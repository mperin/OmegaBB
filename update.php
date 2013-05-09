<?php
/*OmegaBB*/
header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

include('config.php');
include('common.php');
	
$thread_watching=GetParam($_REQUEST,'thread_watching','');
$hitf=GetParam($_REQUEST,'hitf','');
$last_update=GetParam($_REQUEST,'last_update','');
$hipt=GetParam($_REQUEST,'hipt','');
$nrt=GetParam($_REQUEST,'nrt','');
$last_wiki_revision=GetParam($_REQUEST,'last_wiki_revision','');	

$auth_ret = Check_Auth();

if($auth_ret <= 0) {
   echo "0^?".intext("Not signed in");
   return;
}   
	
if ($nrt) {
   update_read_threads($auth_ret,$nrt);
}

if ($last_update == 0) {
   $output = SendUnreadThreads($auth_ret);
} else {
   $output = SendUpdatedThreads($auth_ret,$last_update);
}

$output .= "^*" . GetForumsDeltas($hitf);

//both are set when you're watching a thread and on the last page of that thread
if ($thread_watching && $hipt) {
   $output .= "^*" . GetThreadUpdate($thread_watching,$hipt);
}  else {
   $output .= "^*0^?0^?0^?0^?";
}

$output .= "^*" . SignalsCheck($thread_watching,$last_wiki_revision,$auth_ret);

$output .= "^*" . CheckEventQueue($auth_ret); 

echo "5^*" . $output;

function SignalsCheck($thread_watching,$last_wiki_revision,$user_id) {
    //returns bitfield, first digit = site reload, second digit = forum reload, 3rd digit = thread reload
    $ret_val = 0;
	
	if ($thread_watching && ($last_wiki_revision != "")) {
	   $ret_val += CheckForWikiUpdate($thread_watching,$last_wiki_revision,$user_id);
	}


	
	return $ret_val;
}

function CheckForWikiUpdate($thread_watching,$last_wiki_revision,$user_id) {
    $flag = 0; 
    $wiki_query = "select * from post where reply_num = 1 and thread_id=$thread_watching and author_id != $user_id and revision > $last_wiki_revision";
    $row = perform_query($wiki_query,SELECT);    	  
    if ($row) {
	   $flag = 1;
	}

    return $flag;
}


function CheckEventQueue($user_id) {
    $count = 0;
	$ret_value = "";
	
	if (IsMod($user_id)) {
	   $cur = perform_query("select * from queue", MULTISELECT);
	   while ($row = mysql_fetch_array( $cur )) {  
	       $count++;
		   $ret_value .=  $row["event_id"] . "^?";
	   }
	}
	return $count . "^?" . $ret_value;
}

function update_read_threads($user_id,$nrt) {
    $temp_array = array();
    $pair = array();
    $ret_value = "";
    $temp_array = explode(",",$nrt);

    $row = perform_query("select my_threads from user where user_id='$user_id'", SELECT); 
            
    $updated_my_threads = $row->my_threads;        
    for ($i = 1; $i < count($temp_array); $i++) {    
       $pair = explode(":",$temp_array[$i]);
       if (preg_match('/,' . $pair[0] . ':[0-9]+/',$updated_my_threads)) {
          $updated_my_threads = preg_replace('/,' . 	$pair[0] . ':[0-9]+/',',' . $pair[0] . ':' . $pair[1], $updated_my_threads);       
       } 
    }
    
    $ret_value = perform_query("update user "
    	. "\n set "
    	. "\n  my_threads='" . $updated_my_threads . "'"
        . " where  user_id='$user_id'",UPDATE); 

   return $ret_value;
}

//Called when the user is viewing a thread, appends the thread with any new postings since $hipt.
function GetThreadUpdate($thread_watching,$hipt) {
   global $userid;
   global $settings;

   $count = 0;   
   $high_post_num = 0; 
   $highest_reply_num = 0;
   $ret_value = "";

   $row = perform_query("select forum_id, block_allow_list from thread where thread_id=$thread_watching", SELECT);

   //if forum_id = 12, ensure they're still a member
   if ($row->forum_id == 12){
   		if (!preg_match('/,' . Check_Auth() . ';/',$row->block_allow_list)) {
           return ("0^?Not a member of this PT");
        }
   }

   $first_query = "select * from post where reply_num > 1 and needs_approval = 0 and thread_id=$thread_watching and message_id > $hipt";
   $cur = perform_query($first_query,MULTISELECT);

   while ($row = mysql_fetch_array( $cur )) {  
      if ($settings->edit_time == 0) {
	     $can_edit = 0;
	  } elseif (($row["state"] == 0) && ($userid == $row["author_id"]) && (strtotime($row["tstamp"]) > strtotime("-".$settings->edit_time." minutes"))) {
	     $can_edit = 1;
	  } elseif (($row["state"] == 0) && ($userid == $row["author_id"]) && ($settings->edit_time == -1)) {
	     $can_edit = 1;
	  } else {
	     $can_edit = 0;
	  }

	  $dtime = new DateTime($row->tstamp);
	  $dtime->setTimeZone(new DateTimeZone($settings->time_zone));
	  $timestamp = $dtime->format($settings->datetime_format);  
	  $timestamp=preg_replace("/ /","<br>",$timestamp,1);   
	  
	  $ret_value .= strtoupper($row["author_id"]) . "^?" . $row["author_name"] . "^?" . $row["avatar_id"] . "^?" . $row["message"] . "^?" . $timestamp . "^?" . $can_edit . "^?" . $row["message_id"] . "^?";
	  if ($row["message_id"] > $high_post_num) {$high_post_num = $row["message_id"];}
	  if ($row["reply_num"] > $highest_reply_num) {$highest_reply_num = $row["reply_num"];}
	  $count++;
   }

   return $count . "^?" . $highest_reply_num . "^?" . $high_post_num . "^?" . $ret_value;
}

//called only when the user first logs in, it goes through all of the user's watched threads and
//gives how many posts they contain
function SendUnreadThreads($user_id) {
   define('CHUNK_SIZE',20);  
   $temp_array = array();
   $thread_content_array = array();
   $ta = array();
   $total = 0;
   $ret_value = "";
   $highest_post_id = 0;
   $num_post_array = array();
   $my_threads_hash = array();
   $thread_id_array = array();
   $x = 0;
	
   $row = perform_query("select my_threads from user where user_id=" . $user_id, SELECT);
   $temp_array = explode(",",$row->my_threads);

	for ($i = 1; $i < count($temp_array); $i++){
	   $ta = explode(":",$temp_array[$i]);
	   $my_threads_hash[$ta[0]] = $ta[1];
	   $thread_id_array[$x++] = $ta[0];
	}
	sort($thread_id_array);

	for ($offset = 0; count($thread_id_array) > $offset; $offset+=CHUNK_SIZE) {	
	    $sql_section = ""; 
		for ($i = $offset; ($offset + CHUNK_SIZE) > $i; $i++) {
		   if (isset($thread_id_array[$i])) {
			  $sql_section .= "thread_id=$thread_id_array[$i] ";
			  if ((($i + 1) < ($offset + CHUNK_SIZE)) && (isset($thread_id_array[$i+1]))) {
				 $sql_section .= "or ";
			  }
		   }
		}	
		  
		$query = "select * from thread where $sql_section order by thread_id asc";
		$cur = perform_query($query, MULTISELECT);

		while ($row = mysql_fetch_array( $cur )) {
		   if ($row["num_posts"] > $my_threads_hash[$row["thread_id"]]) {
			  $ret_value .= $row["thread_id"] . "^?" . $row["title"] . "^?" . $row["num_posts"] . "^?" . $row["last_post_id_num"] . "^?" ;
  			  $total++;
		   }
		}
		mysql_free_result( $cur );
	}
   
   $row2 = perform_query("select MAX(last_post_id_num) as max_post from `thread`",SELECT);
   $highest_post_id = $row2->max_post;

   return ($highest_post_id . "^?" . $total ."^?" . $ret_value);
}

//using $last_update, this goes through all recently updated threads (that the user is also watching) sending their post count
function SendUpdatedThreads($user_id,$last_update) {
   $ret_value = "";
   $total = 0;
   $thread_array = array();
   $thread_content_array = array();
   $thread_num_posts_array = array();
   $count = 0;
   $real_count = 0;
   $highest_post_id = 0;

   $first_query = "SELECT * FROM thread WHERE last_post_id_num > $last_update ORDER BY last_post_id_num";
   $cur = perform_query($first_query,MULTISELECT);    	   

   while ($row = mysql_fetch_array( $cur )) {
	  $thread_array[$count++] = $row["thread_id"];
	  $thread_content_array[$total] = $row["thread_id"] . "^?" . $row["title"] . "^?" . $row["num_posts"] . "^?" . $row["last_post_id_num"] . "^?" ;
	  $thread_num_posts_array[$total] = $row["num_posts"];
	  $total++;
	  if ($row["last_post_id_num"] > $highest_post_id) {$highest_post_id = $row["last_post_id_num"];}
   }
   mysql_free_result( $cur );
 
   if ($total > 0) {
	  $temp_array = array();
	  $ta = array();
	  $row = perform_query("select my_threads from user where user_id=" . $user_id, SELECT);
	  $temp_array = explode(",",$row->my_threads);
	  for ($i = 1; $i < count($temp_array); $i++){
		 $ta = explode(":",$temp_array[$i]);
		 
		 for ($j = 0; $j < count($thread_array); $j++) {
			if (($ta[0] == $thread_array[$j]) && ($ta[1] < $thread_num_posts_array[$j])) { 
			   $ret_value .= $thread_content_array[$j];
			   $real_count++;
			}
		 }
	  }
   }

   return ($highest_post_id . "^?" . $real_count ."^?" . $ret_value); 
}

//using $hitf it displays any new threads in the forum
function GetForumsDeltas($hitf) {
   global $settings;
   $new_hitf = 0;
   $count = 0;
   $user_id = Check_Auth();	   
   $row_info = "";
   $extra = "";
   
	if (!$settings->enable_forums) {
	   $extra .= "and forum_id > 11 ";
	} else {
	   $extra .= "and forum_id > " . (11 - $settings->total_forums) . " ";
	}			
	if (!$settings->enable_private_threads) {
	   $extra .= "and forum_id != 12 ";
	}
	if (!$settings->enable_articles) {
	   $extra .= "and forum_id != 13 ";
	}	
   
   $cur = perform_query("select * from thread where needs_approval = 0 and thread_id > $hitf $extra ORDER BY thread_id",MULTISELECT);    	   

   while ($row = mysql_fetch_array( $cur )) {
	  if ($row["thread_id"] > $new_hitf) { $new_hitf = $row["thread_id"]; }
	  if ($row["forum_id"] == 12) {
		 if (preg_match('/,' . $user_id . ';/', $row["block_allow_list"])) {
			//its on the list, so send
		 } else {
			continue;  
		 }
      }
	  if ($row["state"] == 2) {
         continue; //thread is marked as deleted
      }		 
	  
	  $row_info .=  $row["forum_id"] . "^?" . $row["thread_id"] . "^?" . $row["title"] . "^?" . $row["state"] . "^?";
	  $count++;
   }
   
   return ($new_hitf . "^?" . $count . "^?" . $row_info);
}

?>