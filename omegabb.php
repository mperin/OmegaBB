<?php
/* 
OmegaBB developmental version - build 217  Copyright (c) 2013, Ryan Smiderle.  All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted
provided that the following conditions are met:

    * Redistributions of source code must retain the above product name, version number, 
	copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above product name, version number,  
	copyright notice, this list of conditions and the following disclaimer in the documentation 
	and/or other materials provided with the distribution.
    * Neither the name of OmegaBB nor the names of its contributors may be used to endorse or
	promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR 
IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND 
FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR 
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL 
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT 
OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

include_once('config.php');
include_once('common.php');

function SetForumReloadSignal() {
		$cur = perform_query("select user_id from session where last_activity > DATE_SUB(now(),INTERVAL 6 MINUTE) and session != '0' order by user_id ASC LIMIT $results_per_page OFFSET $sql_offset",MULTISELECT);    	   
		   
		while ($row = mysql_fetch_array( $cur )) {
	    $temp_id = $row["user_id"];   
		}
}





function ApproveEvent($event_id) {
	$row = perform_query("select * from queue where event_id=$event_id",SELECT);
	$row2 = perform_query("select * from thread where thread_id=".$row->thread_id,SELECT);
	$row3 = perform_query("select * from post where message_id=".$row->post_id,SELECT);

	if ($row->type == 'post') {
		perform_query("update post set needs_approval=0 where message_id = ".$row->post_id,UPDATE);

		//if it's the last post in the thread, adjust thread info
		if ($row2->num_posts < $row3->reply_num) {
			perform_query("update thread set num_posts=" . $row3->reply_num. ",last_post_id_num='" . $row3->message_id  . "' where thread_id=" . $row->thread_id, UPDATE);
		}
	} else if ($row->type == 'editpost') {
		perform_query("update post set message='".mysql_real_escape_string($row->data)."', edit_time=now() where message_id='".$row->post_id."'",UPDATE);
	} else if ($row->type == 'editwiki') {
		EditWiki($row->post_id, $row->data, $row->sender, 1);
	} else if ($row->type == 'thread') {
        perform_query("update thread set needs_approval=0 where thread_id =".$row->thread_id,UPDATE);
	}
	perform_query("delete from queue where event_id='$event_id'",DELETE);
			
	return "1^?".intext("Post approved");
}

function DisapproveEvent($event_id) {
	$row = perform_query("select * from queue where event_id=$event_id",SELECT);
	$row2 = perform_query("select username from user where user_id=".$row->sender,SELECT);
	
	LogEvent(1,intext("Rejected posting made by ") . " %%u" . $row->sender . ":" . $row2->username . ";");
	
	perform_query("delete from queue where event_id='$event_id'",DELETE);
	
    if ($row->type == 'post') {
	    perform_query("delete from post where message_id = ".$row->post_id,UPDATE);
		$cur = perform_query("select * from file where post_id=". $row->post_id,MULTISELECT); 
		while ($row2 = mysql_fetch_array( $cur )) {
			if ($row2[file_type] == 1) {
				unlink("files/t_" . $row2[internal_id]);  
				unlink("files/" . $row2[internal_id]);
			} else {
				unlink("files/". $row2[internal_id]);  
			}
		}
		perform_query("update file set is_deleted=2 where post_id=". $row->post_id,UPDATE);    
    } else if ($row->type == 'thread') {
	    perform_query("delete from post where message_id = ".$row->post_id,UPDATE);
		perform_query("delete from thread where thread_id = ".$row->thread_id,UPDATE);
		$cur = perform_query("select * from file where post_id=". $row->post_id,MULTISELECT); 
		while ($row2 = mysql_fetch_array( $cur )) {
			if ($row2[file_type] == 1) {
				unlink("files/t_" . $row2[internal_id]);  
				unlink("files/" . $row2[internal_id]);
			} else {
				unlink("files/". $row2[internal_id]);  
			}
		}
		perform_query("update file set is_deleted=2 where post_id=". $row->post_id,UPDATE);    
	}

    return "1^?".intext("Post disapproved");
}

function ShowEvent($event_id) { 
    global $settings;
    $row = perform_query("select * from queue where event_id=$event_id",SELECT);
	$row2 = perform_query("select * from post where message_id=".$row->post_id,SELECT);
    $row3 = perform_query("select username from user where user_id=".$row->sender,SELECT);
	$row4 = perform_query("select title from thread where thread_id=".$row->thread_id,SELECT);
		
    if ($row->type == 'post') {
	    $return_string = "1^?" . $event_id . "^?" . $row->type . "^?" . $row->sender  . "^?" . $row3->username . "^?" . $row->thread_id . "^?" . $row4->title . "^?" . $row2->message . "^?" . $row2->tstamp;
	} else if ($row->type  == 'editpost' || $row->type  == 'editwiki') {	
	    $return_string = "1^?" . $event_id . "^?" . $row->type . "^?" . $row->sender  . "^?" . $row3->username . "^?" . $row->thread_id . "^?" . $row4->title . "^?" . $row2->message . "^?" . $row2->tstamp . "^?" . $row->data . "^?";
	} else if ($row->type == 'thread') {		
	    $return_string = "1^?" . $event_id . "^?" . $row->type . "^?" . $row->sender  . "^?" . $row3->username . "^?" . $row->thread_id . "^?" . $row4->title . "^?" . $row2->message . "^?" . $row2->tstamp . "^?^?" . $row->forum_id . "^?" . $settings->forum_topic_names[$row->forum_id];
	}

	return $return_string;
}

function add_to_approval_queue($type,$user_id,$thread_id,$post_id,$content,$forum_id,$thread_title) {
    if ($type == "post" ) {
		perform_query("insert queue "
			. "\n set "
			. "\n  type='" . $type . "',"
			. "\n  date=now(),"
			. "\n  sender='" . $user_id . "',"
			. "\n  post_id='" . $post_id . "',"			
			. "\n  forum_id='" . $forum_id . "',"					
			. "\n  thread_id='" . $thread_id . "'",	INSERT);
	} else if ($type == "editpost" ) {
		perform_query("insert queue "
			. "\n set "
			. "\n  type='" . $type . "',"
			. "\n  data = '" . mysql_real_escape_string($content) . "',"
			. "\n  date=now(),"
			. "\n  sender='" . $user_id . "',"
			. "\n  post_id='" . $post_id . "',"			
			. "\n  thread_title='" . $thread_title . "',"			
			. "\n  forum_id='" . $forum_id . "',"						
			. "\n  thread_id='" . $thread_id . "'",	INSERT);
	} else if ($type == "editwiki" ) {
		perform_query("insert queue "
			. "\n set "
			. "\n  type='" . $type . "',"
			. "\n  data = '" . mysql_real_escape_string($content) . "',"
			. "\n  date=now(),"
			. "\n  sender='" . $user_id . "',"
			. "\n  post_id='" . $post_id . "',"			
			. "\n  forum_id='" . $forum_id . "',"						
			. "\n  thread_id='" . $thread_id . "'",	INSERT);
	} else if ($type == "thread" ) {			
			perform_query( "insert queue "
			. "\n set "
			. "\n  type='" . $type . "',"
			. "\n  date=now(),"
			. "\n  sender='" . $user_id . "',"
			. "\n  post_id='" . $post_id . "',"			
			. "\n  forum_id='" . $forum_id . "',"						
			. "\n  thread_id='" . $thread_id . "'",	INSERT);
	}
	return;
}

function UnsetLockdownButton() {
   $row = perform_query("select settings from user where user_id=0",SELECT);
   if ($row->settings & SITEDOWN) {$reload_signal = 1;} else {$reload_signal = 0;}
   
   perform_query("update user set settings=NULL, ban_expire_time=NULL, theme=NULL where user_id=0",UPDATE);
   
   LogEvent(1,intext("Lockdown button unset"));
   
   return "1^?$reload_signal";
}

function SetLockdownButton($user_id,$code,$expire_time,$msg) {
   if ($code == 0) {return "-1^?".intext("Nothing was checked");}
   if ($code > 7) {$reload_signal = 1;} else {$reload_signal = 0;} 

   if (is_numeric($expire_time) &&  $expire_time > 0 && $expire_time < 26) {
      $expire_date = "DATE_ADD(now(),INTERVAL $expire_time HOUR)";
   } else if ($expire_time == "i") {
      $expire_date = "NULL";
   } else {
      return "-1^?".intext("Expire time not set");
   }
   
   perform_query("update user set settings='$code', ban_expire_time=$expire_date, theme='$msg' where user_id=0",UPDATE);
   
   if ($code & SITEDOWN) {
      perform_query("update session set session='0', last_activity=now() where user_id NOT IN (select user_id from user where status = 5)",UPDATE); 
   }
		
   if ($code & NEWUSERCAPTCHA) {
      $state .= intext("Captchas for new users when posting"). "/";
   }   
   if ($code & NONEWACCOUNTS) {
      $state .= intext("New account creation is disabled"). "/";
   }   
   if ($code & MUSTLOGIN) {
      $state .= intext("Must sign in to see forum and articles"). "/";
   }   
   if ($code & FORUMDOWN) {
      $state .= intext("Forum and articles are offline"). "/";
   }   
   if ($code & PTDOWN) {
      $state .= intext("Private threads are offline") . "/";
   }
   if ($code & SITEDOWN) {
      $state .= intext("Site is offline"). "/";
   }
   LogEvent(1,intext("Lockdown button set") . " - " . $state);
   
   return "1^?$reload_signal";
}
 
function GetLockdownButton() {
    global $settings;
	
    //System's settings and ban_expire_time are being used to store the lockdown button state, theme is being used to store the optional system message.
    $row = perform_query("select settings, ban_expire_time, theme from user where user_id=0",SELECT);
	
    if ($row->ban_expire_time) {
		$d1 = time() ;			
		$d2 = strtotime($row->ban_expire_time);	
		if ($d1 > $d2) {
			perform_query("update user "
				. "\n set "
				. "\n settings=NULL,"			
				. "\n theme=NULL,"
				. "\n ban_expire_time=NULL"			
				. " where user_id=0",UPDATE); 		
			LogEvent(2,intext("Lockdown button expired"));	
			return "1^?^?^?";
		}
		$dtime = new DateTime($row->ban_expire_time);
		$dtime->setTimeZone(new DateTimeZone($settings->time_zone));
		$expire_time = $dtime->format($settings->datetime_format);  
	} else {
	   $expire_time = intext("Indefinite");
	}
	
	return "1^?".$row->settings."^?".$expire_time."^?".$row->theme;
}

function SaveSiteSettings() {
    $data_types_array = array("server" => "string",
    "database" => "string",
    "user" => "string",	
    "pass" => "string,",	
    "website_title" => "string",
	"website_blurb" => "string",
    "website_url" => "string",	
    "welcome_thread" => "int,0-x",	
	"default_theme" => "string",
    "language" => "string",	
    "time_zone" => "string",	
	"logo_image" => "string",
    "footer_text" => "string",	
    "banner_space" => "int,x-x",	
	"connect_with_username" => "bool",
    "connect_with_fb" => "bool",	
    "fb_appId" => "string",		
    "fb_secret" => "string",
	"fb_references_needed" => "int,0-x",
    "minimum_status_of_fb_reference" => "int,0-5",		
    "connect_with_linkedin" => "bool",		
    "linkedin_api_key" => "string",		
    "linkedin_secret_key" => "string",
    "linkedin_request_connections" => "bool",			
	"linkedin_references_needed" => "int,0-x",	
    "minimum_status_of_linkedin_reference" => "int,0-5",		
    "show_main" => "bool",	
	"show_news" => "bool",
    "show_extra" => "bool",	
    "enable_articles" => "bool",	
	"enable_forums" => "bool",
    "enable_private_threads" => "bool",	
    "allow_rich_text" => "bool",		
    "image_linking_allowed" => "bool",	
	"youtube_linking_allowed" => "bool",
    "emotes_allowed" => "bool",	
    "word_filter" => "bool",	
	"avatars_allowed" => "bool",
    "animated_avatars" => "bool",	
    "allow_username_change" => "bool",	
    "allow_avatar_change" => "bool",		
	"file_upload_allowed" => "bool",
    "file_upload_in_pt_allowed" => "bool",	
    "allowed_file_types" => "string-array,x",	
	"thumbnail_uploaded_images" => "bool",
    "permalinks_enabled" => "bool",	
	"new_accounts_allowed" => "bool",
    "may_undelete" => "bool",	
    "user_block_list" => "bool",		
    "status_to_start_threads" => "int,0-5",	
    "status_to_create_articles" => "int,0-5",	
    "status_to_upload_file" => "int,0-5",	
    "status_to_embed" => "int,0-5",	
    "status_to_have_block_list" => "int,0-5",	
    "status_to_start_pt" => "int,0-5",	
    "status_to_have_avatar" => "int,0-5",	
    "status_to_see_fb_li_profile" => "int,0-5",	
    "status_to_hard_delete" => "int,3-6",	
    "tab_names" => "string-array,5",	
    "forum_tab_names" => "string-array,6",		
    "forum_topic_names" => "string-array,11",			
    "name_of_status_2" => "string",	
    "avatars_same_size" => "bool",	
	"max_avatar_dimensions" => "int-array,2,0-x",
    "narrow_width" => "int,0-x",			
	"default_avatar" => "string",	
	"system_avatar" => "string",	
    "persistent_logo" => "bool",		
	"profile_text_limit" => "int,0-x",
    "help_button" => "int-array,6,0-1",		
    "update_frequency" => "int,1-x",			
    "thumb_width" => "int,1-x",		
    "thumb_height" => "int,1-x",		
    "max_uploaded_file_size" => "int,0-x",		
    "max_post_length" => "int,1-x",		
    "new_account_captcha" => "bool",		
    "new_user_post_captcha" => "bool",			
    "weak_captcha" => "bool",		
    "captcha_distortion" => "int,1-3",		
    "new_account_limit" => "int,x-x",				
    "flood_time" => "int,1-x",		
    "flood_num_posts" => "int,1-x",				
    "total_forums" => "int,1-11",				
    "forums_per_tab" => "int,1-4",		
    "size_of_thread_title" => "int,1-x",				
    "size_of_article_title" => "int,1-x",			
    "size_of_pt_title" => "int,1-x",	
    "edit_time" => "int,x-x",		
    "max_file_attachments" => "int,0-x",			
    "max_username_length" => "int,1-x",		
    "posts_per_page" => "int,1-x",		
    "img_url_whitelist" => "string-array,x",				
    "img_url_blacklist" => "string-array,x",			
    "user_info_permanentness" => "bool",		
    "auto_close_thread" => "int-array,2,0-x",		
    "prune_watchlist" => "bool",			
    "prune_deleted_threads" => "bool",		
    "prune_deleted_posts" => "bool",		
	"prune_session_table" => "bool",			
	"prune_closed_threads" => "int,x-x",		
	"prune_old_pt" => "int,0-x",
    "datetime_format" => "string",			
    "allow_hotlinking" => "bool",	
	"strip_exif" => "bool",	
	"truncate_name" => "int,0-2",		
	"must_login_to_see_forum" => "bool",
	"must_login_to_see_profile" => "bool",			
	"new_user_avatar" => "int,0-2",	
    "fb_li_welcome_pt" => "bool",
	"first_tab_enabled" => "bool",
	"first_tab_is_div" => "bool",
	"first_tab_indexable" => "bool",
	"second_tab_enabled" => "bool",
	"second_tab_enabled" => "bool",
	"second_tab_is_div" => "bool",
	"second_tab_indexable" => "bool",
	"articles_indexable" => "bool",
	"second_last_tab_enabled" => "bool",
	"second_last_tab_is_div" => "bool",
	"second_last_tab_indexable" => "bool",
	"last_tab_enabled" => "bool",
	"last_tab_is_div" => "bool",
	"last_tab_indexable" => "bool",
	"first_tab_name" => "string",
	"first_tab_location" => "string",
	"second_tab_name" => "string",
	"second_tab_location" => "string",
	"articles_tab_name" => "string",
	"pt_tab_name" => "string",
	"second_last_tab_name" => "string",
	"second_last_tab_location" => "string",
	"last_tab_name" => "string",
	"last_tab_location" => "string",
	"forums_indexable" => "bool",	
	"enable_helpmenu" => "bool",
	"helpmenu_name" => "string",		
	"helpmenu1_enabled" => "bool",
	"helpmenu1_name" => "string",
	"helpmenu1_location" => "string",
	"helpmenu1_is_div" => "bool",
	"helpmenu1_indexable" => "bool",
	"helpmenu2_enabled" => "bool",
	"helpmenu2_name" => "string",
	"helpmenu2_location" => "string",
	"helpmenu2_is_div" => "bool",
	"helpmenu2_indexable" => "bool",
	"helpmenu3_enabled" => "bool",
	"helpmenu3_name" => "string",
	"helpmenu3_location" => "string",
	"helpmenu3_is_div" => "bool",
	"helpmenu3_indexable" => "bool",
	"helpmenu4_enabled" => "bool",
	"helpmenu4_name" => "string",
	"helpmenu4_location" => "string",
	"helpmenu4_is_div" => "bool",
	"helpmenu4_indexable" => "bool",
	"helpmenu5_enabled" => "bool",
	"helpmenu5_name" => "string",
	"helpmenu6_enabled" => "bool",
	"helpmenu6_name" => "string",
	"helpmenu6_location" => "string",
	"helpmenu6_is_div" => "bool",
	"helpmenu6_indexable" => "bool"
	);		

	if (count($_POST) == 0) {return "-1^?".intext("No settings changed");}
	
	foreach ($_POST as $k=>$v) {   
	   $ret = error_check($k,$v,$data_types_array); 
	   if ($ret != 1) {
          return $ret;
       }	   
	}

	$my_file = 'config.php';
	$handle = fopen($my_file, 'r');
	$data = fread($handle,filesize($my_file));
	fclose($handle);

	foreach ($_POST as $k=>$v) {	   
	   if (preg_match('/^string-array,/',$data_types_array[$k])) { 

	      //trims spaces between each element and removes dangling commas
		  $v = trim($v);
		  $v = preg_replace("/,[ ]+/",",",$v);
		  $v = preg_replace("/[ ]+,/",",",$v);
		  $v = preg_replace("/,$/","",$v);	   
	      $v = preg_replace("/^,/","",$v);	 
		
	      //surround each element with quotation marks
	      $tmp_string = preg_replace('/,/','","',$v);
		  $tmp_string = preg_replace('/^(.)/','"$1',$tmp_string);
		  $tmp_string = preg_replace('/(.)$/','$1"',$tmp_string);
		  
	      $data = preg_replace('/->' . $k . '( )*=[^=].*?;/', "->" . $k . ' = array(' . $tmp_string . ');', $data);  
	   } else if (preg_match('/^int-array,/',$data_types_array[$k])) { 
	      $data = preg_replace('/->' . $k . '( )*=[^=].*?;/', "->" .$k . ' = array(' . $v . ');', $data);  
	   } else if (preg_match('/^string/',$data_types_array[$k])) { 
	      $data = preg_replace('/->' . $k . '( )*=[^=].*?;/', "->" .$k . ' = "' . $v . '";', $data);  
	   } else if (preg_match('/^bool/',$data_types_array[$k])) { 
	      $data = preg_replace('/->' . $k . '( )*=[^=].*?;/', "->" .$k . ' = ' . $v . ';', $data);  	  		  
	   } else if (preg_match('/^int/',$data_types_array[$k])) { 
	      $data = preg_replace('/->' . $k . '( )*=[^=].*?;/', "->" .$k . ' = ' . $v . ';', $data);  	 		  
	   } else { //default: treat it as a string
	      $data = preg_replace('/->' . $k . '( )*=[^=].*?;/', "->" .$k . ' = "' . $v . '";', $data);  
	   }
	   $var_list .= $k . " ";
	}

	$my_file = 'config.php';
	$handle = fopen($my_file, 'w') or die(intext('Cannot open file').':  '.$my_file);
	fwrite($handle, $data);	
	fclose($handle);

	LogEvent(1,$var_list ." ".intext("changed by administrator"));
    return "1^?done";
}

function error_check($key,$value,$data_types_array) {
	if (preg_match('/^bool/',$data_types_array[$key])) { 
		if ($value != "true" &&	 $value != "false") { 
			  return "-1^?". $key . " ".intext("must be either true or false");
		}  
	} else if (preg_match('/^int,/',$data_types_array[$key])) { 
		if (!is_numeric($value)) {
		   return "-1^?". $key . " ".intext("must be a numerical value");
		}
		$temp = explode(",",$data_types_array[$key]);
		$temp3 = explode("-",$temp[1]);
		
		if ($temp3[0] != "x" && $temp3[1] != "x") {
		   if ($value < intval($temp3[0]) || $value > intval($temp3[1]) || !is_numeric($value)) {
			  return "-1^?". $key . " ".intext("must be between the values of")." " . $temp3[0] . " ".intext("and")." " . $temp3[1];
		   }
		} else if ($temp3[0] != "x") {
		   if ($value < intval($temp3[0]) || !is_numeric($value)) {
			  return "-1^?". $key . " ".intext("must have a value of at least")." " . $temp3[0];
		   }
		} else if ($temp3[1] != "x") {
		   if ($value > intval($temp3[1]) || !is_numeric($value)) {
			  return "-1^?". $key . " ".intext("must have a value no larger than")." " . $temp3[1];
		   }
		}    
	} else if (preg_match('/^int-array/',$data_types_array[$key])) { 
	    $temp = explode(",",$data_types_array[$key]);
		$temp2 = explode(",",$value);
		$temp3 = explode("-",$temp[2]);
		if (count($temp2) != $temp[1] && $temp[1] != "x") {
		   return "-1^?". $key . " ".intext("must have")." " . $temp[1] . " ".intext("elements");
		}

		foreach ($temp2 as $v) {
		   if (!is_numeric($v)) {
			  return "-1^?". $key . " ".intext("must be numerical values");
		   }		
		   if ($temp3[0] != "x" && $temp3[1] != "x") {
			   if ($v < intval($temp3[0]) || $v > intval($temp3[1]) || !is_numeric($v)) {
				  return "-1^?". $key . " ".intext("must be between the values of")." " . $temp3[0] . " ".intext("and")." " . $temp3[1];
			   }
		   } else if ($temp3[0] != "x") {
			   if ($v < intval($temp3[0]) || !is_numeric($v)) {
				  return "-1^?". $key . " ".intext("must consist of numbers of a value of at least")." " . $temp3[0];
			   }
		   } else if ($temp3[1] != "x") {
			   if ($v > intval($temp3[1]) || !is_numeric($v)) {
				  return "-1^?". $key . " ".intext("must consist of numbers of a value no larger than")." " . $temp3[1];
			   }
           } 		   
		}
	} else if (preg_match('/^string-array/',$data_types_array[$key])) { 
	    $temp = explode(",",$data_types_array[$key]);
		$temp2 = explode(",",$value);
		if (count($temp2) != $temp[1] && $temp[1] != "x") {
		   return "-1^?". $key . " ".intext("must have")." " . $temp[1] . " ".intext("elements");
		}
	} 
	if (preg_match('/\"/',$value)) {return "-1^?".intext("Settings values cannot contain double quotation marks");}

	return 1;
}

function GetSiteSettings() {
    global $settings;

	$count = 0;
	foreach($settings as $key => $value) {
	    if (($key == "fb_appId" || $key == "fb_secret"  || $key == "linkedin_api_key" || $key == "linkedin_secret_key" ) && (!IsAdmin(Check_Auth()))) {continue;}
		if ($key == "server" || $key == "database" || $key == "user" || $key == "pass" || $key == "size_of_thread_title" || $key == "size_of_article_title" || $key == "size_of_pt_title") {continue;}
    	$count++;
	    $return_value = $value; 
		$datatype = gettype($value);

	    if ($datatype == "array") {
		    $innerdatatype = gettype($value[0]);
			if ($innerdatatype == "NULL") {  //if the array is empty, assume it's a string array
			   $datatype = "string-array";			
			} else {
		       $datatype = $innerdatatype . "-array";
			}
		    $return_value = "";
			foreach($value as $v) {
			   $return_value .= "$v,";
			}
	        $return_value = preg_replace('/,$/', '', $return_value);  
		}
		if ($datatype == "boolean") {
		   $return_value = var_export($value,true);
		}
       $ret_val .= "$key^?$datatype^?$return_value^?";
    }
	return $count."^?".$ret_val;
}

function LinkedInLogin ($li_id) {
	global $userid; 
	global $session_id;
	global $sessioncookie;
	
	$first_query = "SELECT * FROM user WHERE linkedin_id='$li_id' AND status > -1"; 

	$row = perform_query($first_query,SELECT);
	if (!$row) {
		return "-1^?".intext("Account disabled");
	}

	if (($info = lockdown_button_check(SITEDOWN)) && $row->status != 5) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      return "-1^?".$sysinfo[1];
	   } else {
	      return "-1^?".intext("Feature disabled");
	   }
	}	

    $row2 = perform_query("select type from ban where fb_li_id = '".$li_id."'",SELECT);
	if ($row2->type == "perm_ban" || $row2->type =="ban" || $row2->type == "wiped") {
		$msg = IsBanned($row->user_id); 
		return "-1^?".$msg;
	}

	$userid = $row->user_id; 
	$user_id = $row->user_id;
	$logins = $row->logins;

	perform_query("delete from session where user_id='" . $row->user_id . "'",DELETE);

	set_session();

	$lifetime = time() + 365*24*60*60;
	setcookie("sessioncookie", $sessioncookie, $lifetime);

	perform_query("insert into session set last_activity=now(), session='$session_id', cookie='$sessioncookie'", INSERT);

	perform_query( "update user "
		. "\n set "
		. "\n last_ip='" . $_SERVER['REMOTE_ADDR'] . "', logins = logins + 1 where user_id='".$row->user_id."'",UPDATE); 

	perform_query("update session "
		. "\n set "
		. "\n  user_id='$user_id',"
		. "\n  sess_start=now(),"
		. "\n  last_activity=now(),"
		. "\n  ip='".$_SERVER['REMOTE_ADDR']."',"
		. "\n  user_agent='".$_SERVER['HTTP_USER_AGENT']
		. "' where  session='$session_id'",UPDATE); 
	
	housecleaning();

	return "1^?Login successful";
}

function LinkedInNewUser ($user_data,$user_connections) {
	global $settings;
	global $userid;
	
	if (!isset($user_data)) {
	   return "-1^?".intext("An error has occurred");
	}	
	
	$p = xml_parser_create();
	xml_parse_into_struct($p, $user_data, $vals, $index);
	xml_parser_free($p);
    $li_id = $vals[$index["ID"][0]]["value"];
    $li_name = $vals[$index["FIRST-NAME"][0]]["value"] . " " . $vals[$index["LAST-NAME"][0]]["value"];			 
    $avatar_url = $vals[$index["PICTURE-URL"][0]]["value"];
	$li_profile_url = $vals[$index["PUBLIC-PROFILE-URL"][0]]["value"]; 
	
    $row = perform_query("select ip_address from ban where ip_address = '".$_SERVER['REMOTE_ADDR']."'",SELECT);
	if ($row) {
		return "-1^?".intext("New accounts not allowed");
	}
	if ($_COOKIE['sessioncookie']) {
		$row = perform_query("select cookie from ban where cookie = '".$_COOKIE['sessioncookie']."'",SELECT);
		if ($row) {
			return "-1^?".intext("New accounts not allowed");
		}
	}
    $row = perform_query("select fb_li_id from ban where fb_li_id = '".$li_id."'",SELECT);
	if ($row) {
		return "-1^?".intext("New accounts not allowed");
	}
	
	if ($info = lockdown_button_check(SITEDOWN+NONEWACCOUNTS)) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
		  return "-1^?".$sysinfo[1];
	   } else {
		  return "-1^?".intext("Feature disabled");
	   }
	}	   

	if ($settings->new_accounts_allowed == 0) {
	  return "-1^?".intext("New accounts not allowed");
	}
   
   	if ($settings->new_account_limit != -1) {
		$count=0;
		$cur2 = perform_query("select last_ip from user where status!=5 and last_ip='" . $_SERVER['REMOTE_ADDR'] . "'", MULTISELECT); 

		while ($row2 = mysql_fetch_array( $cur2 )) {
		   $count++;
		}   

		if ($count >= $settings->new_account_limit) {
		   return "-1^?".intext("You've made the maximum number of accounts");
		}
	}

 	$bad_chars = array(";", "," );
	$li_name = str_replace($bad_chars, '_', $li_name);
	if (is_numeric($li_name) || ($li_name == "") || (strtolower($li_name) == "newusers") || (strtolower($li_name) == "allusers") || (strtolower($li_name) == "system")) {
		$li_name = "Untitled";
	}		
	
	if ($settings->truncate_name == 1) { //Abbreviate last name
	   $first_name = preg_replace('/ .*$/', '', $li_name);
	   preg_match('/ (\S*)$/',$li_name, $matches); 
	   $initial = substr($matches[1], 0, 1);
	   $li_name = $first_name . " " . $initial . ".";
	}
	if ($settings->truncate_name == 2) { //First name only
	   $li_name = preg_replace('/ .*$/', '', $li_name);
	} 

	$nametaken = true;
	$appendnum = 1;		
	while ($nametaken) {
		$row = perform_query("select username from user where username='$li_name'",SELECT);
		if ($row) {
		   $appendnum++;
		   if ($appendnum == 2) {
		      $li_name .= " " . $appendnum;
		   } else {
			  $li_name = preg_replace("/(.* )[0-9]+$/", '${1}'.$appendnum, $li_name);
		   }
		} else {
		   $nametaken = false;
		}
	}

	if ($settings->fb_li_welcome_pt || $settings->linkedin_references_needed) {
	    $li_friends_list = "";
	    for($i = 0; $i < count($user_connections); $i++) {
			$p = xml_parser_create();
			xml_parse_into_struct($p, $user_connections[$i], $vals2, $index2);
			xml_parser_free($p);
			for ($j = 0; $j < count($index2["ID"]); $j++) {
				$li_friends_list .=  "," . $vals2[$index2["ID"][$j]]["value"];
	        }
	    }
		$obb_friends_list = get_obb_friends_list2($li_friends_list);
	}
	
	if ($settings->linkedin_references_needed) {
		$references = 0;
        foreach ($obb_friends_list as $id) {
		    $row3 = perform_query("select username, status, is_banned from user where user_id=$id",SELECT);
			if (($row3->status >= $settings->minimum_status_of_linkedin_reference) && ($row3->is_banned == 0)) {
				$references++;
				$ref_names .= " %%u".$id.":".$row3->username.";";
			}   
		}
        if ($references < $settings->linkedin_references_needed) {
		    return "-1^?".intext("You do not have enough references to become a member of this site.  You must be LinkedIn contacts with at least "). $settings->linkedin_references_needed . intext(" local members");
		}
	}	
	
	if (($settings->welcome_thread == "") || ($settings->welcome_thread == 0)) {$threads = "";} else {$threads = "," . $settings->welcome_thread . ":0";}
	
    $newuserquery = "insert user "
		. "\n set "
		. "\n  username='".mysql_real_escape_string($li_name)."',"
		. "\n  linkedin_id='$li_id',"
		. "\n  linkedin_link='$li_profile_url',"		
		. "\n  my_threads='$threads',"    		
		. "\n  join_date=now(),"
		. "\n  prune_time=now(),"		
		. "\n  settings=0,"		
		. "\n  first_ip='" . $_SERVER['REMOTE_ADDR'] . "'";
   
	$new_user_id = perform_query($newuserquery,INSERT); 
	$userid = $new_user_id;

	if ($settings->avatars_allowed && getimagesize($avatar_url)) {
		$internal_id = md5(mt_rand());   
	
		$q = "insert file "
				. "\n set "		
				. "\n  author_id='" . $new_user_id . "',"
				. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
				. "\n  filename='linkedin_avatar.jpg',"		
				. "\n  mime_type='image/jpeg',"
				. "\n  file_type='1',"   						
				. "\n  avatar_number='1',"   							
				. "\n  internal_id='$internal_id';";   							
		perform_query($q,INSERT);       	
	
		AddAvatar($new_user_id);
		UpdateAvatar(1);
		
		$uploadfile = getcwd() . "/files/avatar_".$new_user_id."_1_".$internal_id;
		$im = thumbnail($avatar_url, $settings->max_avatar_dimensions[0], $settings->max_avatar_dimensions[1], 1);
		if ($im) {
		   imageToFile("image/jpeg", $im, $uploadfile);
		} else {
		   copy($avatar_url, $uploadfile);
		}	   				
	}

	if ((count($obb_friends_list) > 0) && ($settings->enable_private_threads) && ($settings->fb_li_welcome_pt) ) {
	   array_push($obb_friends_list,$new_user_id);
	   PostPrivateThread(0,1,implode(",", $obb_friends_list),intext("Welcome").", ".mysql_real_escape_string($li_name).". ".intext("This is a private thread that includes local members who are contacts with you on LinkedIn").".",intext("Private thread for")." ".mysql_real_escape_string($li_name));
	}
	
	if ($settings->linkedin_references_needed) {
		LogEvent(2,intext("New member").": %%u".$new_user_id.":".mysql_real_escape_string($li_name).";". " ".intext("references").": ".$ref_names);
	} else {
		LogEvent(2,intext("New member").": %%u".$new_user_id.":".mysql_real_escape_string($li_name).";");
	}

	return "1^?new user created";
}

function get_obb_friends_list2($li_friends_list) {
    $return_val = array();
	
    $li_friends_array = explode(",",$li_friends_list);
	
   	foreach ($li_friends_array as $li_id) {
	    if ($li_id == "") {continue;}
		$row = perform_query("select user_id from user where linkedin_id='$li_id'",SELECT); 
	    if ($row) {
		   array_push($return_val,$row->user_id);
		}
	}

    return $return_val;
}

function FacebookLogin ($fb_id,$fb_name,$fb_link) {
	global $userid; 
	global $session_id;
	global $sessioncookie;
	
 	if (!isset($fb_id)) {
	   return "-1^?".intext("An error has occurred");
	}
	
	$first_query = "SELECT * FROM user WHERE facebook_id='$fb_id' AND status > -1"; 

	$row = perform_query($first_query,SELECT);
	if (!$row) {
		return "-1^?".intext("Account disabled");
	}
	
	if (($info = lockdown_button_check(SITEDOWN)) && $row->status != 5) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      return "-1^?".$sysinfo[1];
	   } else {
	      return "-1^?".intext("Feature disabled");
	   }
	}	
	
    $row2 = perform_query("select type from ban where fb_li_id = '".$fb_id."'",SELECT);
	if ($row2->type == "perm_ban" || $row2->type =="ban" || $row2->type == "wiped") {
		$msg = IsBanned($row->user_id); 
		return "-1^?".$msg;
	}

	$userid = $row->user_id; 
	$user_id = $row->user_id;
	$logins = $row->logins;

	perform_query("delete from session where user_id='" . $row->user_id . "'",DELETE);

	set_session();  
	
	$lifetime = time() + 365*24*60*60;
	setcookie( "sessioncookie", $sessioncookie, $lifetime);
		   
	perform_query("insert into session set last_activity=now(), session='$session_id', cookie='$sessioncookie'", INSERT);
 
	perform_query( "update user "
		. "\n set "
		. "\n last_ip='" . $_SERVER['REMOTE_ADDR'] . "', logins = logins + 1 where user_id='".$row->user_id."'",UPDATE); 
					
	perform_query("update session "
		. "\n set "
		. "\n  user_id='$user_id',"
		. "\n  sess_start=now(),"
		. "\n  last_activity=now(),"
		. "\n  ip='".$_SERVER['REMOTE_ADDR']."',"
		. "\n  user_agent='".$_SERVER['HTTP_USER_AGENT']
		. "' where  session='$session_id'",UPDATE); 
	
	housecleaning();
	
	return "1^?Login successful";
}

function FacebookNewUser ($fb_id,$fb_name,$fb_link,$access_token) {
   global $settings;
   global $userid;
      
    $row = perform_query("select ip_address from ban where ip_address = '".$_SERVER['REMOTE_ADDR']."'",SELECT);
	if ($row) {
		return "-1^?".intext("New accounts not allowed");
	}
	if ($_COOKIE['sessioncookie']) {
		$row = perform_query("select cookie from ban where cookie = '".$_COOKIE['sessioncookie']."'",SELECT);
		if ($row) {
			return "-1^?".intext("New accounts not allowed");
		}
	} 
    $row = perform_query("select fb_li_id from ban where fb_li_id = '".$fb_id."'",SELECT);
	if ($row) {
		return "-1^?".intext("New accounts not allowed");
	}
	
	if ($info = lockdown_button_check(SITEDOWN+NONEWACCOUNTS)) {
		$sysinfo = explode("^?",$info);
		if ($sysinfo[1]) {
			return "-1^?".$sysinfo[1];
		} else {
			return "-1^?".intext("Feature disabled");
		}
	}	   

	if ($settings->new_accounts_allowed == 0) {
	  return "-1^?".intext("New accounts not allowed");
	}
   
   	if ($settings->new_account_limit != -1) {
		$count=0;
		$cur2 = perform_query("select last_ip from user where status!=5 and last_ip='" . $_SERVER['REMOTE_ADDR'] . "'", MULTISELECT); 

		while ($row2 = mysql_fetch_array( $cur2 )) {
		   $count++;
		}   

		if ($count >= $settings->new_account_limit) {
		   return "-1^?".intext("You've made the maximum number of accounts");
		}
	}

 	$bad_chars = array(";", "," );
	$fb_name = str_replace($bad_chars, '_', $fb_name);
	if (is_numeric($fb_name) || ($fb_name == "") || (strtolower($fb_name) == "newusers") || (strtolower($fb_name) == "allusers") || (strtolower($fb_name) == "system")) {
		$fb_name = "Untitled";
	}		

	if ($settings->truncate_name == 1) { //Abbreviate last name
	   $first_name = preg_replace('/ .*$/', '', $fb_name);
	   preg_match('/ (\S*)$/',$fb_name, $matches); 
	   $initial = substr($matches[1], 0, 1);
	   $fb_name = $first_name . " " . $initial . ".";
	}
	if ($settings->truncate_name == 2) { //First name only
	   $fb_name = preg_replace('/ .*$/', '', $fb_name);
	} 

	$nametaken = true;
	$appendnum = 1;		
	while ($nametaken) {
		$row = perform_query("select username from user where username='$fb_name'",SELECT);
		if ($row) {
		   $appendnum++;
		   if ($appendnum == 2) {
		      $fb_name .= " " . $appendnum;
		   } else {
			  $fb_name = preg_replace("/(.* )[0-9]+$/", '${1}'.$appendnum, $fb_name);
		   }
		} else {
		   $nametaken = false;
		}
	}	

	if ($settings->fb_li_welcome_pt || $settings->fb_references_needed) {
	    $notdone = true;
		$fburl = "https://graph.facebook.com/$fb_id/friends?access_token=".$access_token;
		$fb_friends_list = "";		
	    while (1) {
			$friends_list = json_decode(file_get_contents($fburl));
			if (count($friends_list->data) == 0) {break;}

			foreach ($friends_list->data as $key=>$value) {
				$fb_friends_list .=  "," . $value->id;
			}	
			$fburl = $friends_list->paging->next;
		}
		$obb_friends_list = get_obb_friends_list($fb_friends_list);
	}

	if ($settings->fb_references_needed) {
		$references = 0;
        foreach ($obb_friends_list as $id) {
		    $row3 = perform_query("select username, status, is_banned from user where user_id=$id",SELECT);
			if (($row3->status >= $settings->minimum_status_of_fb_reference) && ($row3->is_banned == 0)) {
				$references++;
				$ref_names .= " %%u".$id.":".$row3->username.";";
			}   
		}
        if ($references < $settings->fb_references_needed) {
		    return "-1^?".intext("You do not have enough references to become a member of this site.  You must be Facebook friends with at least "). $settings->fb_references_needed . intext(" local members");
		}
	}	
	
	if (($settings->welcome_thread == "") || ($settings->welcome_thread == 0)) {$threads = "";} else {$threads = "," . $settings->welcome_thread . ":0";}
	
    $newuserquery = "insert user "
		. "\n set "
		. "\n  username='".mysql_real_escape_string($fb_name)."',"
		. "\n  facebook_id='$fb_id',"
		. "\n  facebook_link='$fb_link',"		
		. "\n  my_threads='$threads',"    		
		. "\n  join_date=now(),"
		. "\n  prune_time=now(),"		
		. "\n  settings=0,"		
		. "\n  first_ip='" . $_SERVER['REMOTE_ADDR'] . "'";
   
	$new_user_id = perform_query($newuserquery,INSERT); 
	$userid = $new_user_id;
		
	if ($settings->avatars_allowed) {
		$internal_id = md5(mt_rand());   
	
		$q = "insert file "
				. "\n set "		
				. "\n  author_id='" . $new_user_id . "',"
				. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
				. "\n  filename='facebook_avatar.jpg',"		
				. "\n  mime_type='image/jpeg',"
				. "\n  file_type='1',"   						
				. "\n  avatar_number='1',"   							
				. "\n  internal_id='$internal_id';";   							
		perform_query($q,INSERT);       	
	
		AddAvatar($new_user_id);
		UpdateAvatar(1);

		$uploadfile = getcwd() . "/files/avatar_".$new_user_id."_1_".$internal_id;
		$im = thumbnail("http://graph.facebook.com/$fb_id/picture?type=normal", $settings->max_avatar_dimensions[0], $settings->max_avatar_dimensions[1], 1);
		if ($im) {
		   imageToFile("image/jpeg", $im, $uploadfile);
		} else {
		   copy("http://graph.facebook.com/$fb_id/picture?type=normal", $uploadfile);
		}	   		
	}
	
	if ((count($obb_friends_list) > 0) && ($settings->enable_private_threads) && ($settings->fb_li_welcome_pt)) {
	   array_push($obb_friends_list,$new_user_id);
	   PostPrivateThread(0,1,implode(",", $obb_friends_list),intext("Welcome").", ".mysql_real_escape_string($fb_name).". ".intext("This is a private thread that includes local members who are friends with you on Facebook").".",intext("Private thread for")." ".mysql_real_escape_string($fb_name));
	}
	
	if ($settings->fb_references_needed) {
		LogEvent(2,intext("New member").": %%u".$new_user_id.":".mysql_real_escape_string($fb_name).";". " ".intext("references").": ".$ref_names);
	} else {
		LogEvent(2,intext("New member").": %%u".$new_user_id.":".mysql_real_escape_string($fb_name).";");
	}
	
	return "1^?new user created";
}

function get_obb_friends_list($fb_friends_list) {
    $return_val = array();
	
    $fb_friends_array = explode(",",$fb_friends_list);
	
   	foreach ($fb_friends_array as $fb_id) {
	    if ($fb_id == "") {continue;}
		$row = perform_query("select user_id from user where facebook_id='$fb_id'",SELECT); 
	    if ($row) {
		   array_push($return_val,$row->user_id);
		}
	}

    return $return_val;
}

//This function is ran at every login to do periodic housecleaning tasks
function housecleaning() {
    global $settings;

	//File uploading is a two step process, uploading the file then posting.  After a file has been uploaded but before the
	//message is posted, the file exists in the ./files/tmp directory.  This cleans out files that haven't been attached to  
	//a post and are more than 60 minutes old.
	$cur = perform_query("select * from file where post_id < 1 and tstamp < DATE_SUB(now(),INTERVAL 60 MINUTE) and is_deleted = 0 and avatar_number IS NULL",MULTISELECT);

	while ($row = mysql_fetch_array( $cur )) {
	   foreach (glob("files/tmp/from_*_".$row[file_id]) as $filename) {
		  unlink($filename);
	   }
 	   perform_query("update file set is_deleted=2 where file_id='".$row[file_id]."'",UPDATE); 
	}

	//See if any timed bannings need to be lifted
	$cur2 = perform_query("select * from user where ban_expire_time < now()",MULTISELECT);    	   
	while ($row = mysql_fetch_array( $cur2 )) {
		if ($row[user_id] == 0) {
			//lockdown button's time has expired
			perform_query("update user "
				. "\n set "
				. "\n settings=NULL,"			
				. "\n theme=NULL,"
				. "\n ban_expire_time=NULL"			
				. " where user_id=0",UPDATE); 		
			LogEvent(2,intext("Lockdown button expired"));	
		} else {
			$row2 = perform_query("select thread_block_list from user where user_id='0'",SELECT); 
			$old_list = $row2->thread_block_list;
			$new_list = preg_replace('/,' . $row[user_id] . '(,|$)/', '$1', $old_list);  
			perform_query("update user "
				. "\n set "
				. "\n thread_block_list='" . $new_list . "'"
				. " where user_id='0'",UPDATE); 	

			perform_query("update user "
				. "\n set "
				. "\n is_banned=0,"
				. "\n ban_expire_time=NULL"			
				. " where user_id='".$row[user_id]."'",UPDATE); 	
		}			
	}	

	//See if any threads need to be auto-closed
	if (($settings->auto_close_thread[0] != 0) && ($settings->auto_close_thread[1] != 0)) {
		perform_query("update thread set state='1' where "
		. "\n DATE_ADD(creation_time,INTERVAL ".$settings->auto_close_thread[0]." DAY) < now() and "
		. "\n DATE_ADD(tstamp,INTERVAL ".$settings->auto_close_thread[1]." DAY) < now() and "
		. "\n state = 0 and no_auto_close = 0", UPDATE); 
		perform_query("update thread set state='4' where "
		. "\n DATE_ADD(creation_time,INTERVAL ".$settings->auto_close_thread[0]." DAY) < now() and "
		. "\n DATE_ADD(tstamp,INTERVAL ".$settings->auto_close_thread[1]." DAY) < now() and "
		. "\n state = 3 and no_auto_close = 0", UPDATE); 		
	} elseif ($settings->auto_close_thread[0] != 0) {
		perform_query("update thread set state='1' where "
		. "\n DATE_ADD(creation_time,INTERVAL ".$settings->auto_close_thread[0]." DAY) < now() and "
		. "\n state = 0 and no_auto_close = 0", UPDATE); 
		perform_query("update thread set state='4' where "
		. "\n DATE_ADD(creation_time,INTERVAL ".$settings->auto_close_thread[0]." DAY) < now() and "
		. "\n state = 3 and no_auto_close = 0", UPDATE); 		
	} elseif ($settings->auto_close_thread[1] != 0) {
		perform_query("update thread set state='1' where "
		. "\n DATE_ADD(tstamp,INTERVAL ".$settings->auto_close_thread[1]." DAY) < now() and "
		. "\n state = 0 and no_auto_close = 0", UPDATE); 
		perform_query("update thread set state='4' where "
		. "\n DATE_ADD(tstamp,INTERVAL ".$settings->auto_close_thread[1]." DAY) < now() and "
		. "\n state = 3 and no_auto_close = 0", UPDATE); 		
	}
	
    //updating user's information (about every 2 weeks)
	$user_id = Check_Auth();
	$row = perform_query("select * from user where user_id=" . $user_id . " and prune_time < DATE_SUB(now(),INTERVAL 14 DAY)", SELECT);
	
	if ($row) { 
		//Prune closed and deleted threads from the user's thread watchlist.
		if ($settings->prune_watchlist) {
			$temp_my_threads = $row->my_threads;
			$temp_array = explode(",",$row->my_threads);
			for ($i = 1; $i < count($temp_array); $i++){
			   $ta = explode(":",$temp_array[$i]);
			   $my_threads_hash[$ta[0]] = $ta[1];
			}

			$query = "select thread_id from thread where ( (forum_id != 13 and (state = 1 || state = 2 || state = 4) ) or (forum_id = 13 and state = 2) ) and (tstamp < DATE_SUB(now(),INTERVAL 7 DAY)) and (tstamp > '".$row->prune_time."')";
			$cur3 = perform_query($query, MULTISELECT);

			while ($row2 = mysql_fetch_array( $cur3 )) {
				if (isset($my_threads_hash[$row2["thread_id"]])) {
					$temp_my_threads = preg_replace("/,".$row2["thread_id"].":[0-9]+/","",$temp_my_threads);
				}
			}
			mysql_free_result( $cur3 );
			
			perform_query("update user "
				. "\n set "
				. "\n my_threads='" . $temp_my_threads . "'"
				. " where user_id='$user_id'",UPDATE); 	 
		} 
		  
		//update prune time
		perform_query("update user "
				. "\n set "
				. "\n prune_time=DATE_SUB(now(),INTERVAL 7 DAY)"				
				. " where user_id='$user_id'",UPDATE); 	 		
   }
   
   //System prune, ran once a week
   $system_prune = perform_query("select prune_time from user where user_id=0 and prune_time < DATE_SUB(now(),INTERVAL 7 DAY)", SELECT);	
   
   if ($system_prune) {
		//Permanently delete threads that have been set to deleted and have been inactive for at least two weeks.
		if ($settings->prune_deleted_threads) {
			$query = "select thread_id from thread where state = 2 and (tstamp < DATE_SUB(now(),INTERVAL 14 DAY))";
			$cur4 = perform_query($query, MULTISELECT);

			while ($row = mysql_fetch_array( $cur4 )) {
				SystemDeleteThread($row["thread_id"]);
			}
		}
		//Prune closed threads
		if ($settings->prune_closed_threads != -1) {
			$query = "select thread_id from thread where forum_id != 13 and (state = 1 or state = 4) and (tstamp < DATE_SUB(now(),INTERVAL ".$settings->prune_closed_threads." DAY))";
			$cur7 = perform_query($query, MULTISELECT);

			while ($row = mysql_fetch_array( $cur7 )) {
				SystemDeleteThread($row["thread_id"]);
			}
		} 
   
		if ($settings->prune_deleted_posts) {
		   perform_query("delete from post WHERE message_id < 0 and (tstamp < DATE_SUB(now(),INTERVAL 14 DAY))",DELETE); 
		} 	   

		if ($settings->prune_session_table) {
		   perform_query("delete from session WHERE last_activity < DATE_SUB(now(),INTERVAL 6 MONTH)",DELETE); 
		} 	   

		//Delete rows from the file table that have been set to deleted for at least two weeks.
		perform_query("delete from file where (is_deleted = 1 or is_deleted = 2) and (tstamp < DATE_SUB(now(),INTERVAL 14 DAY))",DELETE);

		//update System's prune time
		perform_query("update user set prune_time=now() where user_id=0",UPDATE); 	  
		
		//See if any PTs need to be auto-deleted
		$ptdelquery = "";
		if ($settings->prune_old_pt != 0) {	
			$ptdelquery = "select thread_id from thread where forum_id = 12 and "
			. "\n DATE_ADD(tstamp,INTERVAL ".$settings->prune_old_pt." MONTH) < now()";
		}	
		
		if ($ptdelquery) {
			$cur7 = perform_query($ptdelquery, MULTISELECT);

			while ($row = mysql_fetch_array( $cur7 )) {
				$row3 = perform_query("select block_allow_list from thread where thread_id=".$row["thread_id"]);

				$tmp_string = explode(",",$row3->block_allow_list);
				foreach ($tmp_string as $t) {
					if ($t == "") {continue;}
					$tmp_string2 = explode(";",$t);

					$row2 = perform_query("select my_threads, my_private_threads from user where user_id=".$tmp_string2[0]);

					$new_my_threads = preg_replace('/,' . $row["thread_id"] . '.*(,|$)/', '${1}' , $row2->my_threads);  
					$new_my_private_threads = preg_replace('/,-?' . $row["thread_id"] . '(,|$)/', '${1}' , $row2->my_private_threads);  

					$ret_value = perform_query("update user "
					. "\n set "
					. "\n  my_private_threads='" . $new_my_private_threads . "',"	
					. "\n  my_threads='" . $new_my_threads . "'"
					. " where  user_id='".$tmp_string2[0]."'",UPDATE); 

					SystemDeleteThread($row["thread_id"]);
				}
			}
		}		
		
		LogEvent(2,intext("Weekly system maintenance tasks were ran"));
   }
}

function SystemDeleteThread($thread_id) {
	perform_query("delete from thread WHERE thread_id=". $thread_id, DELETE); 
	perform_query("delete from post WHERE thread_id=". $thread_id, DELETE); 
	$cur5 = perform_query("select * from file where thread_id=". $thread_id, MULTISELECT); 
	while ($row2 = mysql_fetch_array( $cur5 )) {
		if ($row2[file_type] == 1) {
			unlink("files/t_" . $row2[internal_id]);  
			unlink("files/" . $row2[internal_id]);
		} else {
			unlink("files/". $row2[internal_id]);  
		}
	}
	perform_query("update file set is_deleted=2 where thread_id=".$thread_id,UPDATE); 
}

function SaveSettings($autowatch,$hideonlinestatus,$user_id) {
	//settings is a bitfield.  First bit is for $hideonlinestatus, second bit is for $autowatch
	$settings = 0;
		
	if ($hideonlinestatus == "true") {
	   $settings += 1;
	}	
	if ($autowatch == "true") {
	   $settings += 2;
	   //the my_private_threads field in the System account (user id #0) is being used to store user ids of those who have auto-watch set on
	   
	   //if not on list, add
	   $row = perform_query("select my_private_threads from user where user_id=0", SELECT);
	   if (!preg_match("/,$user_id(,|$)/",$row->my_private_threads)) {
	       $new_list = $row->my_private_threads . "," . $user_id;
	       perform_query("update user "
			. "\n set "
			. "\n my_private_threads='" . $new_list . "'"	
			. " where  user_id=0",UPDATE); 
	   }
	} else {
	   //if on list, remove
	   $row = perform_query("select my_private_threads from user where user_id=0", SELECT);   
	   if (preg_match("/,$user_id(,|$)/",$row->my_private_threads)) {
	       $new_list = preg_replace('/,' . $user_id . '(,|$)/','${1}', $row->my_private_threads);
	       perform_query("update user "
			. "\n set "
			. "\n my_private_threads='" . $new_list . "'"	
			. " where user_id=0",UPDATE); 
	   }
	}

	perform_query("update user "
		. "\n set "
		. "\n settings='$settings'"
		. " where user_id='". $user_id ."'",UPDATE); 	

   return "1^?".intext("Settings updated");	
}	

function IsBannedFromThisThread($user_id,$thread_id) {
	global $settings;
	$img_ret_val = array();
	
    $row = perform_query("select status from user where user_id='" . $user_id . "'", SELECT);
	if (!$row) { return 0;}
	$status = $row->status;

    $row = perform_query("select block_allow_list, type, state from thread where thread_id='" . $thread_id . "'", SELECT);
	if (!$row) { return 0;}
	
	if (($row->type == 0) && ($status > -1) && ($status != 5) ) {
	    $block_allow_list = $row->block_allow_list;
	
		if ((preg_match('/,newusers/',$block_allow_list)) && ($status == 0) ) { 
	       return 1;
		}   
		if (preg_match('/,' . $user_id . ';/',$block_allow_list)) { 
		   return 1;
		}
	}

	if (IsBanned($user_id)) {
	   return 1;
	}

	return 0;
}

function RevertWikiPost($post_id,$thread_id,$revision,$user_id) {
    global $settings;

	$row = perform_query("select * from post where message_id='$post_id'",SELECT); 
    $row2 = perform_query("select * from thread where thread_id='" . $row->thread_id . "'", SELECT);
    $row3 = perform_query("select * from user where user_id='" . $user_id . "'", SELECT);  //?
	
	$status = $row3->status;
	
	if (($status > -1) && ($status < 5) && ($row2->author_id != $user_id)) {
	    $block_allow_list = $row2->block_allow_list;

		if ((preg_match('/,newusers/',$block_allow_list)) && ($status == 0) ) { 
		   return "-1^?".intext("New users are not allowed to edit this post");
		}   
		if (preg_match('/,' . $user_id . ';/',$block_allow_list)) { 
		   return "-1^?".intext("You are not allowed to edit this post");
		}
	}

	if ($msg = IsBanned(Check_Auth())) { return "-1^?".intext("You are not allowed to edit this post").". ".$msg;}
	
	if (is_flood()) { 
	   return "-1^?".intext("Flood attack detected, please wait a while before posting");
	}
	if (!IsValidThread($row->thread_id)) {
	   return "-1^?".intext("This is not a valid thread");	
	}

	$can_edit = 0;
	switch ($row->type) {
	  case 1: //author only
		  if ($user_id == $row->author_id) {
			 $can_edit = 1;
		  }
		  break;
	  case 2: //star members and moderators
		  if (GetStatus($user_id) > 1) {
			 $can_edit = 1;
		  }
		  break;
	  case 3: //regular users
		  if (GetStatus($user_id) > 0) {
			 $can_edit = 1;
		  }
		  break;
	  case 4: //all users
		  $can_edit = 1;
		  break;			
	} 
	//author of wiki can always edit
	if ($user_id == $row2->author_id) {
	  $can_edit = 1;
	}
		  
	if (!$can_edit) {
	  return "-1^?".intext("Status not high enough to edit this wiki");	
	}   	   
	if (!$settings->enable_articles) {
	  return "-1^?".intext("Articles are disabled");	
	}

	//old revision you want to make current
	$row4 = perform_query("select message from post where reply_num=-1 and thread_id=$thread_id and revision=$revision", SELECT);	
	
	//current revision 
	$row5 = perform_query("select * from post where reply_num=1 and thread_id=$thread_id",SELECT); 
	
	//change current post to reply_num of -1
	$query = "update post "
		. "\n set "
		. "\n reply_num=-1"
		. " where message_id=".$row5->message_id;
	perform_query($query, UPDATE);
		
	//new post is made using content from $row4, given reply_num = 1, revision++
	$query = "insert post "
		. "\n set "
		. "\n  author_id='" . $row3->user_id . "',"
		. "\n  message='" . mysql_real_escape_string($row4->message) . "',"
		. "\n  thread_id=" . $row->thread_id . ","
		. "\n  tstamp=now(),"
		. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
		. "\n  avatar_id=" . $row3->current_avatar . ","
		. "\n  reply_num=1,"
		. "\n  author_name='" . mysql_real_escape_string($row3->username) . "',"
		. "\n  type=".$row->type.","
		. "\n  revision=".($row5->revision + 1);
	perform_query($query, INSERT);

    PostMsg($user_id, "<i>".intext("Article was reverted to version ").($revision+1)." ".intext("by"). " " . mysql_real_escape_string($row3->username) . " (" . strtoupper($row3->user_id) . ") " . intext("on") . " " . date('F jS\, Y'). "</i>", $row->thread_id, 1);

    return "1^?Revision made";
}

function EditWiki($post_id, $theinput, $user_id, $force=0) {
    global $settings;
	
	$row = perform_query("select * from post where message_id='$post_id'",SELECT); 
    $row2 = perform_query("select * from thread where thread_id='" . $row->thread_id . "'", SELECT);
    $row3 = perform_query("select * from user where user_id='" . $user_id . "'", SELECT);
	
	$status = $row3->status;

	if (($row2->type == 0) && ($status > -1) && ($status < 5) && !($row-type > 0 && $row2->author_id == $user_id)) {
	    $block_allow_list = $row2->block_allow_list;

		if ((preg_match('/,newusers/',$block_allow_list)) && ($status == 0)) { 
		   return "-1^?".intext("New users are not allowed to edit this post");
		}   
		if (preg_match('/,' . $user_id . ';/',$block_allow_list)) { 
		   return "-1^?".intext("You are not allowed to edit this post");
		}
	}

    if ($msg = IsBanned(Check_Auth())) { return "-1^?".intext("You are not allowed to edit this post").". ".$msg;}
	
	if (mb_strlen($theinput) > $settings->max_post_length) {
	   return "-1^?".intext("Post is too long, maximum size is")." ". $settings->max_post_length . " ".intext("characters").".";
	}
	if (is_flood()) { 
	   return "-1^?".intext("Flood attack detected, please wait a while before posting");
	}
	if (($row->state == 1) && ($row2->forum_id != 13) ){
	   return "-1^?".intext("You are not allowed to edit this post");
	}	
	if (!IsValidThread($row->thread_id)) {
	   return "-1^?".intext("This is not a valid thread");	
	}
	
	$can_edit = 0;
	if ($user_id == $row2->author_id) {$can_edit = 1;}
	if ($row->type == 2 && GetStatus($user_id) > 1) {$can_edit = 1;}
	if ($row->type == 3 && GetStatus($user_id) > 0) {$can_edit = 1;}
	if ($row->type == 4) {$can_edit = 1;}

	if (!$can_edit) {
		return "-1^?".intext("Status not high enough to edit this wiki");	
	}   	   
	if (!$settings->enable_articles) {
		return "-1^?".intext("Articles are disabled");	
	}
	
	hyperlinks_check($theinput); 
	rich_text_check($theinput);
	wordfilter_check($theinput);	
	emote_check($theinput);
	image_check4($theinput,$row->thread_id,$row->author_id,$post_id,$row->reply_num);
	youtube_check($theinput);		
	image_links_check($theinput);		
	make_spaces_check($theinput);	

	if ($status == 0 && $settings->post_approval && $force == 0) {
	   add_to_approval_queue('editwiki',$user_id,$row->thread_id,$post_id,$theinput,$row2->forum_id);
       return "-2^?".intext("Your post has been received and is pending approval");
    }
	
	//previous post with reply_num of 1 is given reply_num of 0		
	$query = "update post "
		. "\n set "
		. "\n reply_num=-1"
		. " where message_id='$post_id'";
	perform_query($query, UPDATE);

	//new post is made, and given reply_num = 1, revision++
	$query = "insert post "
		. "\n set "
		. "\n  author_id='" . $row3->user_id . "',"
		. "\n  message='" . $theinput . "',"
		. "\n  thread_id=" . $row->thread_id . ","
		. "\n  tstamp=now(),"
		. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
		. "\n  avatar_id=" . $row3->current_avatar . ","
		. "\n  reply_num=1,"
		. "\n  author_name='" . mysql_real_escape_string($row3->username) . "',"
		. "\n  type=".$row->type.","
		. "\n  revision=".($row->revision + 1);
	perform_query($query, INSERT);

	$num_posts = 0;

	$temp = GetInfo($user_id);
	$user_info = explode("^?",$temp);  
	$user_name = $user_info[1];

	PostMsg($user_id, "<i>".intext("Article was edited by"). " " . mysql_real_escape_string($user_name) . " (" . strtoupper($user_id) . ") " . intext("on") . " " . date('F jS\, Y'). "</i>", $row->thread_id, 1);

	//this needs to be given to the client to add to this article to their mythreads_hash 
	$num_posts = $row2->num_posts + 1;
	
	//$theinput = preg_replace('/\\r/','', $theinput);  //IE is getting "\r"s
	$theinput = stripslashes($theinput);

	return "1^?Post edited^?$theinput^?1^?$num_posts";
}

function EditMsg($post_id, $theinput, $user_id) {
    define('EDIT_GRACE_TIME',3);
    global $settings;
	
	$row = perform_query("select * from post where message_id='$post_id'",SELECT); 
    $row2 = perform_query("select * from thread where thread_id='" . $row->thread_id . "'", SELECT);
    $row3 = perform_query("select * from user where user_id='" . $user_id . "'", SELECT);
	
	$status = $row3->status;

	if (($row2->type == 0) && ($status > -1) && ($status < 5) && !($row-type > 0 && $row2->author_id == $user_id)) {
	    $block_allow_list = $row2->block_allow_list;

		if ((preg_match('/,newusers/',$block_allow_list)) && ($status == 0)) { 
		   return "-1^?".intext("New users are not allowed to edit this post");
		}   
		if (preg_match('/,' . $user_id . ';/',$block_allow_list)) { 
		   return "-1^?".intext("You are not allowed to edit this post");
		}
	}

    if ($msg = IsBanned(Check_Auth())) { return "-1^?".intext("You are not allowed to edit this post").". ".$msg;}
	
	if (mb_strlen($theinput) > $settings->max_post_length) {
	   return "-1^?".intext("Post is too long, maximum size is")." ". $settings->max_post_length . " ".intext("characters").".";
	}
	if (is_flood()) { 
	   return "-1^?".intext("Flood attack detected, please wait a while before posting");
	}
	if (($row->state == 1) && ($row2->forum_id != 13) ){
	   return "-1^?".intext("You are not allowed to edit this post");
	}	
	if ($row->author_id != $user_id) {
	   return "-1^?".intext("You're not the author of this post");
	}
	if ($settings->edit_time == 0) {
	   return "-1^?".intext("Post editing disabled");
	}
	if (($settings->edit_time != -1) && (strtotime($row->tstamp) < strtotime("-".($settings->edit_time + EDIT_GRACE_TIME)." minutes"))) {
	   return "-1^?".intext("The maximum time allowed to edit this post was exceeded");
	}
	if (!IsValidThread($row->thread_id)) {
	   return "-1^?".intext("This is not a valid thread");	
	}

	hyperlinks_check($theinput); 
	rich_text_check($theinput);
	wordfilter_check($theinput);	
	emote_check($theinput);
	image_check3($theinput,$row->thread_id,$row->author_id,$post_id,$row->reply_num);
	file_check3($theinput,$row->thread_id,$row->author_id,$post_id,$row->reply_num);
	youtube_check($theinput);		
	image_links_check($theinput);		
	make_spaces_check($theinput);	

	if ($status == 0 && $settings->post_approval) {
	   add_to_approval_queue('editpost',$user_id,$row->thread_id,$post_id,$theinput,$row2->forum_id);
       return "-2^?".intext("Your post has been received and is pending approval");
    }
	
    perform_query("update post "
		. "\n set "
		. "\n  message='" . $theinput . "',"
		. "\n  edit_time=now()"		
		. " where message_id='$post_id'", UPDATE);

	//$theinput = preg_replace('/\\r/','', $theinput);  //IE is getting "\r"s
	$theinput = stripslashes($theinput);

	return "1^?Post edited^?$theinput";

}

function ForumIndex($page) {
    global $settings;
	if ($settings->must_login_to_see_forum && (Check_Auth() <= 0)) {return;}
	if (lockdown_button_check(MUSTLOGIN) && (Check_Auth() <= 0)) {return;}
	
	if (!$settings->enable_forums) {
	   return;
	}   

    if (lockdown_button_check(FORUMDOWN+SITEDOWN)) {
	   return;
	}   

    $row = perform_query("SELECT * FROM thread ORDER BY thread_id LIMIT 1",SELECT);   
   
    $lowest = $row->thread_id - 1 + ($page * 200);
    $highest = $row->thread_id - 1 + (($page+1) * 200);
	
	$forum_lower_range = 11 - $settings->total_forums;
    $my_query = "select * from thread where thread_id > $lowest and thread_id < $highest+1 and state != 2 and forum_id != 12 and forum_id != 13 and forum_id > $forum_lower_range order by thread_id limit 200";

	$cur = perform_query($my_query,MULTISELECT);    	   
	   
	while ($row = mysql_fetch_array( $cur )) {
	   $page_num = 0;
	   for ($i = 0; $i < $row["num_posts"]; $i += $settings->posts_per_page) {
	      if ($page_num > 0) { 
		     $extra = "&page=" . ($page_num+1); 
			 $extra2 = " -- Page ". ($page_num+1);
		  } else {
		     $extra = ""; 
		     $extra2 = "";
		  }
		  $stuff .=  "<br>\n<a href='thread.php?id=" . $row["thread_id"] . $extra ."'>" . $row['title'] . $extra2 . "</a> ";  
		  $page_num++;
	   }
	}

    $row = perform_query("SELECT * FROM thread ORDER BY thread_id DESC LIMIT 1",SELECT);   
   
    if ($row->thread_id > $highest) {
	   $stuff .= "<br><br>\n<a href='fullindex.php?page=" . ($page + 1) . "'> Next Page </a>"; 
    }
	return $stuff;
}

function ArticlesIndex($page) {
    global $settings;
	if ($settings->must_login_to_see_forum && (Check_Auth() <= 0)) {return;}
	if (lockdown_button_check(MUSTLOGIN) && (Check_Auth() <= 0)) {return;}
		
	if (!$settings->enable_articles) {
	   return;
	}   
	if (lockdown_button_check(FORUMDOWN+SITEDOWN)) {
	   return;
	}   
	
    $row = perform_query("SELECT * FROM thread where forum_id = 13 ORDER BY thread_id LIMIT 1",SELECT);   
   
    $lowest = $row->thread_id - 1 + ($page * 200);
    $highest = $row->thread_id - 1 + (($page+1) * 200);

    $my_query = "select * from thread where thread_id > $lowest and thread_id < $highest+1 and state != 2 and forum_id = 13 order by thread_id limit 200";

	$cur = perform_query($my_query,MULTISELECT);    	   
	   
	while ($row = mysql_fetch_array( $cur )) {
	   $page_num = 0;
	   for ($i = 0; $i < $row["num_posts"]; $i += $settings->posts_per_page) {
	      if ($page_num > 0) { 
		     $extra = "&page=" . ($page_num+1); 
			 $extra2 = " -- Page ". ($page_num+1);
		  } else {
		     $extra = ""; 
		     $extra2 = "";
		  }
		  $stuff .=  "<br>\n<a href='thread.php?id=" . $row["thread_id"] . $extra ."'>" . $row['title'] . $extra2 . "</a> ";  
		  $page_num++;
	   }
	}

    $row = perform_query("SELECT * FROM thread ORDER BY thread_id DESC LIMIT 1",SELECT);   
   
    if ($row->thread_id > $highest) {
	   $stuff .= "<br><br>\n<a href='fullindex.php?page=" . ($page + 1) . "'> Next Page </a>"; 
    }
	return $stuff;
}

function GetForumNames($current_forum_id,$total_forums) {
	global $settings;
	$start = 12 - $total_forums;

	$count = 0;
	for ($i = $start; $i < 12; $i++) {
	    if ($i == $current_forum_id) {continue;}
		$forum_list .= $i. "^?"  .$settings->forum_topic_names[$i - 1]. "^?";
		$count++;
	}

	return "1^?" . $count . "^?" . $forum_list;
}

function GetSiteInfo() {
	$obb_ver = file_get_contents("./version.txt");
	if (preg_match('/^OmegaBB developmental version/',$obb_ver)) {
		$obb_ver = preg_replace('/^OmegaBB developmental version - /','',$obb_ver);
	} else if (preg_match('/^OmegaBB [0-9\.]+/',$obb_ver)) {
		$obb_ver = preg_replace('/^OmegaBB ([0-9\.]+).*$/','$1',$obb_ver);
	} else {
		$obb_ver = intext("unknown");
	}

	$php_ver = phpversion();
	$sql_ver = mysql_get_server_info();

	return "1^?$obb_ver^?$php_ver^?$sql_ver";
}

function CheckForUpdates() {
	$current_version = file_get_contents("http://www.omegabb.com/current_version.txt");
	if (!$current_version) {
		return "-1^?".intext("Failed to contact server");
	} 		
		  
	$tmp = $current_version;
	$tmp2 = $current_version;
	$current_version_number = preg_replace('/^OmegaBB ([0-9\.]+).*$/','$1',$tmp);
	$current_build_number = preg_replace('/^.*build ([0-9]+).*$/','$1',$tmp2);

	$obb_ver = file_get_contents("./version.txt");
	if (preg_match('/^OmegaBB developmental version/',$obb_ver)) {
		$obb_ver = preg_replace('/^OmegaBB developmental version - build /','',$obb_ver);
		$is_dev_version = true;
	} else if (preg_match('/^OmegaBB [0-9\.]+/',$obb_ver)) {
		$obb_ver = preg_replace('/^OmegaBB ([0-9\.]+).*$/','$1',$obb_ver);
		$is_dev_version = false;
	} else {
		return "-1^?".intext("Unable to determine your current version.  version.txt is missing.");
	}

	if ($is_dev_version) {
		if ($current_build_number < $obb_ver) {
			return "1^?".intext("Your developmental version is more recent than the offical version.");
		} else if ($current_build_number == $obb_ver) {
			return "1^?".intext("Your version is up to date.");
		} else {
			return "1^?".intext("A newer version is available.  Go to www.omegabb.com to download it.");
		}
	} else {
		$current_version_tuple = explode(".",$current_version_number);
		$local_version_tuple = explode(".",$obb_ver);

		if ($current_version_tuple[0] > $local_version_tuple[0]) {
			return "1^?".intext("A newer version is available.  Go to www.omegabb.com to download it.");
		}
		if ($current_version_tuple[1] > $local_version_tuple[1]) {
			return "1^?".intext("A newer version is available.  Go to www.omegabb.com to download it.");
		}

		if (sizeof($current_version_tuple) == sizeof($local_version_tuple)) {
			if (sizeof($current_version_tuple) == 3) {
				if ($current_version_tuple[2] > $local_version_tuple[2]) {
					return "1^?".intext("A newer version is available.  Go to www.omegabb.com to download it.");
				} else {
					return "1^?".intext("Your version is up to date.");
				}
			} else {
				return "1^?".intext("Your version is up to date.");
			}
		} else {
			return "1^?".intext("A newer version is available.  Go to www.omegabb.com to download it.");
		}
	}
}

function GetSystemLog($page) {
    global $settings;
    $results_per_page = 50;	
	$sql_offset = $page * $results_per_page;
	$count = 0;
	$cur = perform_query("select * from log order by log_id DESC LIMIT $results_per_page OFFSET $sql_offset",MULTISELECT);

	while ($row = mysql_fetch_array( $cur )) {
	    $count++;
		
		$dtime = new DateTime($row["tstamp"]);
		$dtime->setTimeZone(new DateTimeZone($settings->time_zone));
		$timestamp = $dtime->format($settings->datetime_format);  
		
		$row3 = perform_query("select username from user where user_id=".$row["user_id"],SELECT);
		
		$log_list .= $row["user_id"] . ":" . $row3->username  . "^?" . $row["text"] . "^?" . $timestamp . "^?";
	}
	
    $row2 = perform_query("SELECT count( * ) as total_record FROM log",SELECT);
	$total_pages = ceil($row2->total_record / $results_per_page);
	if ($total_pages == 0) {$total_pages = 1;}
	
	return "1^?" . $page . "^?" . $total_pages . "^?" . $count . "^?" . $log_list;
}

function GetFileLog($page) {
    global $settings;
    $results_per_page = 50;	
	$sql_offset = $page * $results_per_page;
	$count = 0;
	$cur = perform_query("select * from file order by file_id DESC LIMIT $results_per_page OFFSET $sql_offset",MULTISELECT);

	while ($row = mysql_fetch_array( $cur )) {
	    $count++;
		
		if ($row["avatar_number"]) {
		   $location = intext("Avatar");
		   if (file_exists("files/avatar_".$row["author_id"]."_".$row["avatar_number"]."_".$row["internal_id"])) {
		      $status = intext("found");
			  $url = "file.php?uid=".$row["author_id"]."&avatar_number=".$row["avatar_number"];
		   } else {
		      $status = intext("file not found");
			  $url = "none";
		   }
		} else if (($row["post_id"] == -1) || ($row["post_id"] == 0) ){
		   $location = intext("in tmp directory");
		   if (file_exists("files/tmp/from_".$row["author_id"]."_".$row["file_id"])) {
		      $status = intext("found");
		      $url = "file.php?file_id=".$row["file_id"];
		   } else {
		      $status = intext("file not found");
			  $url = "none";
		   }		
        } else { 
			if ($row["forum_id"] == 12) {
			   $location = intext("Private thread");
			} else {
			   $row4 = perform_query("select title from thread where thread_id=".$row["thread_id"],SELECT);
			   if ($row4) {
			      $location = "%%t".$row["thread_id"].":".$row4->title.";";
			   } else {
			      $location = "(".intext("deleted thread").")";
			   }
			}
			if (file_exists("files/".$row["internal_id"])) {
				$status = intext("found");
				if ((preg_match('/^image\//',$row["mime_type"]))) {
				   $url = "file.php?id=".$row["external_id"]."&d=inline";
				} else {
				   $url = "file.php?id=".$row["external_id"];
				}
			} else {
				$status = intext("file not found");
				$url = "none";
			}
		}
		if ($row["is_deleted"] == 1) {$url = "none"; $status = intext("deleted");}
        if ($row["is_deleted"] == 2) {$url = "none"; $status = intext("auto-deleted");}

		$dtime = new DateTime($row["tstamp"]);
		$dtime->setTimeZone(new DateTimeZone($settings->time_zone));
		$timestamp = $dtime->format($settings->datetime_format);  
		
		$row3 = perform_query("select username from user where user_id=".$row["author_id"],SELECT);
		if ($row3) {$username = $row3->username;} else {$username = $row["author_id"];}
		
		$log_list .= $row["file_id"] . "^?" . $timestamp . "^?" . $row["author_id"] . ":" . $username . "^?" . $row["filename"] . "^?" .  $row["mime_type"] . "^?" . $location . "^?" . $url . "^?" . $status . "^?";
	}
	
    $row2 = perform_query("SELECT count( * ) as total_record FROM file",SELECT);
	$total_pages = ceil($row2->total_record / $results_per_page);
	
	return "1^?" . $page . "^?" . $total_pages . "^?" . $count . "^?" . $log_list;
}

function GetUserList($page, $filter){
	$count = 0;
    $do_sql = 1;
	$results_per_page = 30;	
	$sql_offset = $page * $results_per_page;
	
    if ($filter == "all") {	
	   $my_query = "select user_id, username from user where status != -1 order by user_id ASC LIMIT $results_per_page OFFSET $sql_offset";
	   $my_query2 = "SELECT count( * ) as total_record FROM user where status != -1";
	}
    if ($filter == "regular_users") {	
	   $my_query = "select user_id, username from user where status = 1 order by user_id ASC LIMIT $results_per_page OFFSET $sql_offset";
	   $my_query2 = "SELECT count( * ) as total_record FROM user where status = 1";	   
	}
    if ($filter == "new_users") {	
	   $my_query = "select user_id, username from user where status = 0 and user_id != 0 order by user_id ASC LIMIT $results_per_page OFFSET $sql_offset";
	   $my_query2 = "SELECT count( * ) as total_record FROM user where status = 0 and user_id != 0";	   
	}	
    if ($filter == "editors") {	 //status of 2, by default called star members
	   $my_query = "select user_id, username from user where status = 2 order by user_id ASC LIMIT $results_per_page OFFSET $sql_offset";
	   $my_query2 = "SELECT count( * ) as total_record FROM user where status > 2";	   
	}	
    if ($filter == "moderators") {	
	   $my_query = "select user_id, username from user where status > 2 order by user_id ASC LIMIT $results_per_page OFFSET $sql_offset";
	   $my_query2 = "SELECT count( * ) as total_record FROM user where status > 2";	   
	}
    if ($filter == "banned") {
		$do_sql = 0;   
		$count = 0;
	   
		$row = perform_query("SELECT count( * ) as total_record FROM ban WHERE 1",SELECT);  
		$total_pages = ceil($row->total_record / $results_per_page);
		
		$cur = perform_query("select * from ban order by ban_id ASC LIMIT $results_per_page OFFSET $sql_offset",MULTISELECT);    	   

		while ($row = mysql_fetch_array( $cur )) {
			if ($row["type"] == "wiped") {
				if ($row["ip_address"]) {
					$username = $row["ip_address"];

					if (!IsAdmin(Check_Auth())) {
						$username = preg_replace('/[0-9]+\.[0-9]+$/','?.?', $username);
					} 
				}
				if ($row["fb_li_id"]) {
					$fb_li_id = $row["fb_li_id"];

					if (!IsAdmin(Check_Auth())) {
						$fb_li_id = preg_replace('/[0-9]{1,5}$/','?', $fb_li_id);
					}
					$username .= " / social media id: " . $fb_li_id;

			    }
			    $user_list .= $row["ban_id"] . "^?-1^?" . $username . "^?";		
			    $count++;
			} else {	
				$row3 = perform_query("select username from user where user_id = '".$row["user_id"]."'",SELECT);
				$user_id = $row["user_id"];
				$username = $row3->username;
				$user_list .= $row["ban_id"] . "^?" . $user_id . "^?" . $username . "^?";		
				$count++;
			}
	    }
	}
	if ($filter == "online") {	
	    $do_sql = 0;
		
		$row = perform_query("SELECT count( * ) as total_record FROM session where last_activity > DATE_SUB(now(),INTERVAL 6 MINUTE) and session != '0'",SELECT);
		$total_pages = ceil($row->total_record / $results_per_page);
		
		$cur = perform_query("select user_id from session where last_activity > DATE_SUB(now(),INTERVAL 6 MINUTE) and session != '0' order by user_id ASC LIMIT $results_per_page OFFSET $sql_offset",MULTISELECT);    	   
		   
		while ($row = mysql_fetch_array( $cur )) {
		   $temp_id = $row["user_id"];
			
		   $row = perform_query("select settings, username from user where user_id = '$temp_id'",SELECT);   
		   
		   if (($row->settings & 1) && (!IsAdmin(Check_Auth()))) { continue;}
		   
		   $user_list .=  "0^?" . $temp_id . "^?" . $row->username . "^?";
		   $count++;
		}	
	}
	
	if ($do_sql) {
		$cur = perform_query($my_query,MULTISELECT);    	   
		   
		while ($row = mysql_fetch_array( $cur )) {
		   $user_list .=  "0^?" . $row["user_id"] . "^?" . $row["username"] . "^?";
		   $count++;
		}
		
		$row = perform_query($my_query2,SELECT);   
		$total_pages = ceil($row->total_record / $results_per_page);
    }
	return "1^?" . $page . "^?" . $total_pages . "^?" . $count . "^?" . $user_list;
}   

function ThreadModOptions($thread_id) {
   global $settings;
   
   $row = perform_query("select * from thread where thread_id='" . $thread_id . "'", SELECT);
   if (!$row) { return "-1^?".intext("Thread has been deleted");}
   $state = $row->state;
   $forum_id = $row->forum_id;
   $author_id = $row->author_id;
   
   $auto_close_option = 0;  
   if (($settings->auto_close_thread[0] != 0) || ($settings->auto_close_thread[1] != 0)){
      if (($state == 0) || ($state == 3)) {
         if ($row->no_auto_close == 0) {
		    $auto_close_option = 1;  //keep thread from auto-closing
		 } else {
		    $auto_close_option = 2;  //allow thread to auto-close
		 }
	  }
   }
   
   return "1^?" . $state . "^?" . $forum_id . "^?" . $thread_id . "^?" . $author_id . "^?" . $auto_close_option;
}

function SaveBlockList($user_id,$blocked_user_list,$blocknewusers,$ptblocked_user_list,$ptblocknewusers,$ptblockallusers) {
	//thread block list
	$member_list = explode(",",$blocked_user_list);  
	foreach ($member_list as $mem) {
	    $mem = trim($mem);
	    if ($mem == "") {continue;}
		if ($mem == "newusers") {continue;}
		
		if (is_numeric($mem)) {
           $row = perform_query("select * from user where user_id='". $mem . "'",SELECT); 
        } else {
	       $row = perform_query("select * from user where username='$mem'",SELECT); 
		}
		if (!$row) {continue;}
        
	    $new_user_list .= "," . $row->user_id;  
	}

    if ($blocknewusers == "true") { $extra .= ",newusers";}
 
	$ret_value = perform_query("update user "
    	. "\n set "
		. "\n thread_block_list='" . $extra . $new_user_list . "'"
        . " where user_id='". $user_id ."'",UPDATE); 	

	//private thread block list
	$member_list = explode(",",$ptblocked_user_list);  
	foreach ($member_list as $mem) {
	    $mem = trim($mem);
	    if ($mem == "") {continue;}
		if ($mem == "newusers") {continue;}
		if ($mem == "allusers") {continue;}
		
		if (is_numeric($mem)) {
           $row2 = perform_query("select * from user where user_id='". $mem . "'",SELECT); 
        } else {
	       $row2 = perform_query("select * from user where username='$mem'",SELECT); 
		}
		if (!$row2) {continue;}
        
	    $ptnew_user_list .= "," . $row2->user_id;  
	}

    if ($ptblocknewusers == "true") { $ptextra .= ",newusers";}
    if ($ptblockallusers == "true") { $ptextra .= ",allusers";}
 
	$ret_value = perform_query("update user "
    	. "\n set "
		. "\n pt_block_list='" . $ptextra . $ptnew_user_list . "'"
        . " where user_id='". $user_id ."'",UPDATE); 			
		
		
   return "1^?".intext("Block list updated");
}	

function GetSettingsInfo($user_id) {
    $row = perform_query("select settings, pt_block_list, thread_block_list from user where user_id='". $user_id . "'",SELECT); 
   
    $new_user_list = "";
   
    //change into usernames
   	$member_list = explode(",",$row->thread_block_list);  
	foreach ($member_list as $mem) {
	    $mem = trim($mem);
	    if ($mem == "") {continue;}
		if ($mem == "newusers") {$new_user_list .= ",newusers"; continue;}
		
		if (is_numeric($mem)) {
           $row2 = perform_query("select * from user where user_id='". $mem . "'",SELECT); 
        } else {
	       $row2 = perform_query("select * from user where username='$mem'",SELECT); 
		}
		if (!$row2) {continue;}
		  
	    $new_user_list .= "," . $row2->username;  
    }
	
	$pt_block_list = explode(",",$row->pt_block_list);  
	foreach ($pt_block_list as $mem) {
	    $mem = trim($mem);
	    if ($mem == "") {continue;}
		if ($mem == "newusers") {$ptblock_user_list .= ",newusers"; continue;}
		if ($mem == "allusers") {$ptblock_user_list .= ",allusers"; continue;}
		
		if (is_numeric($mem)) {
           $row3 = perform_query("select * from user where user_id='". $mem . "'",SELECT); 
        } else {
	       $row3 = perform_query("select * from user where username='$mem'",SELECT); 
		}
		if (!$row3) {continue;}
		  
	    $ptblock_user_list .= "," . $row3->username;  
    }

   return "1^?" . trim($new_user_list) ."^?". trim($ptblock_user_list) . "^?" . $row->settings;
}

function UserIdModOptions($user_id,$thread_id,$page,$post_position) {
   global $settings;
   
   $post_position++;

   $row = perform_query("select avatar_id, message_id, state from post where reply_num='". (($page * $settings->posts_per_page) + $post_position) . "' and thread_id ='" .$thread_id."'", SELECT);
   $message_id = $row->message_id;
   $is_deleted = $row->state;
   $avatar_id = $row->avatar_id;
   
   $row = perform_query("select is_banned, status from user where user_id='" . $user_id . "'", SELECT);
   if ($row) {
      $is_wiped = 0;
      $status = $row->status;
      $is_banned = $row->is_banned; 
   } else {
      $is_wiped = 1;
   }
   
   $row = perform_query("select type, block_allow_list from thread where thread_id='" . $thread_id . "'", SELECT);
   $block_allow_list = $row->block_allow_list;
   $type = $row->type;
   
   if ($type == 0) { //it's a public thread, so being on the block_allow_list means you've been banned from this thread
	   $is_thread_banned = 0;
	   if ((preg_match('/,' . $user_id . ';/',$block_allow_list))) { 
		   $is_thread_banned = 1;
	   }
   } else if ($type > 0) { //it's a private thread, so being on the block_allow_list means you're a member of this thread
	   $is_thread_banned = 1;
	   if ((preg_match('/,' . $user_id . ';/',$block_allow_list))) { 
		   $is_thread_banned = 0;
	   }  
   }
   
   return "1^?" . $is_deleted . "^?" . $is_thread_banned . "^?" . $is_banned . "^?" . $user_id . "^?" . $thread_id . "^?" . $page . "^?" . $post_position . "^?" . $message_id . "^?" . $status . "^?" . $type . "^?" . $is_wiped . "^?" . $avatar_id . "^?";
}

function WikiModOptions($user_id,$thread_id,$page,$post_position) {
   global $settings;
   
   $post_position++;

   $row = perform_query("select message_id, state from post where reply_num='". (($page * $settings->posts_per_page) + $post_position) . "' and thread_id ='" .$thread_id."'", SELECT);
   $message_id = $row->message_id;
   $is_deleted = $row->state;
   
   $row = perform_query("select status from user where user_id='" . $user_id . "'", SELECT);
   $status = $row->status;
   
   $row = perform_query("select type, block_allow_list from thread where thread_id='" . $thread_id . "'", SELECT);
   $block_allow_list = $row->block_allow_list;
   $type = $row->type;
   
   if ($type == 0) { //it's a public thread, so being on the block_allow_list means you've been banned from this thread
	   $is_thread_banned = 0;
	   if ((preg_match('/,' . $user_id . ';/',$block_allow_list))) { 
		   $is_thread_banned = 1;
	   }
   } else if ($type > 0) { //it's a private thread, so being on the block_allow_list means you're a member of this thread
	   $is_thread_banned = 1;
	   if ((preg_match('/,' . $user_id . ';/',$block_allow_list))) { 
		   $is_thread_banned = 0;
	   }  
   }

   if (is_banned($user_id)) {
	   $is_global_banned = 1;
   }
   
   return "1^?" . $is_deleted . "^?" . $is_thread_banned . "^?" . $is_global_banned . "^?" . $user_id . "^?" . $thread_id . "^?" . $page . "^?" . $post_position . "^?" . $message_id . "^?" . $status;
}

function JoinPT($thread_id,$user_id) {
    //ensure user has this thread as a negative in their my_private_threads list	
    $row = perform_query("select * from user where user_id='". $user_id . "'",SELECT); 
    if (!(preg_match('/,-' . $thread_id . '(,|$)/',$row->my_private_threads))) { 
	   return "-1^?".intext("Cannot join thread");
	}
	$username = $row->username;
	$user_id = $row->user_id;
	
	//set my_threads to thread_id:0, remove negative from my_private_threads, add user to threads block_allow_list
	
	$new_my_threads = $row->my_threads . "," . $thread_id . ":0";
	$new_my_private_threads = preg_replace('/,-' . $thread_id . '(,|$)/', ',' . $thread_id . '${1}' , $row->my_private_threads);  
	
	$ret_value = perform_query("update user "
    	. "\n set "
		. "\n  my_private_threads='" . $new_my_private_threads . "',"	
    	. "\n  my_threads='" . $new_my_threads . "'"
        . " where  user_id='$user_id'",UPDATE); 
		
	$row = perform_query("select block_allow_list from thread where thread_id='". $thread_id . "'",SELECT); 	
	
	$new_block_allow_list = $row->block_allow_list . "," . $user_id . ";" . $username;
		
	$ret_value = perform_query("update thread "
    	. "\n set "
		. "\n block_allow_list='" . mysql_real_escape_string($new_block_allow_list) . "'"	
        . " where thread_id='$thread_id'",UPDATE); 
		
	return "1^?".intext("Joined thread")." $thread_id";
}

function LeavePT($thread_id,$user_id) {
    //the user's entry is removed from the thread's block_allow_list.
    $row = perform_query("select * from thread where thread_id='". $thread_id . "'",SELECT); 
	$new_block_allow_list = preg_replace('/,' . $user_id . ';[^$,]+/', '' , $row->block_allow_list);  //todo: this looks wrong

	$ret_value = perform_query("update thread "
    	. "\n set "
		. "\n  block_allow_list='" . mysql_real_escape_string($new_block_allow_list) . "'"	
        . " where thread_id='$thread_id'",UPDATE); 

    //deleted from my_threads, set to a negative number in my_private_threads.  

    $row = perform_query("select * from user where user_id='". $user_id . "'",SELECT); 

	$new_my_threads = preg_replace('/,' . $thread_id . ':[0-9]+/', '' , $row->my_threads);
	$new_my_private_threads = preg_replace('/,(' . $thread_id . ')(,|$)/', ',-${1}${2}', $row->my_private_threads);  
	   
    $ret_value = perform_query("update user "
    	. "\n set "
		. "\n  my_private_threads='" . $new_my_private_threads . "',"	
    	. "\n  my_threads='" . $new_my_threads . "'"
        . " where  user_id='$user_id'",UPDATE); 

    return "1^?Left thread";
}

function SetProfileText($user_id,$text) {
    global $settings;
	
	if ($msg = IsBanned(Check_Auth())) { return "-1^?".intext("Unable to update profile.")." ".$msg;}
	
	hyperlinks_check($text);
	make_spaces_check($text);
    wordfilter_check($text);
	emote_check($text);	
	rich_text_check($text);
		
	$len = mb_strlen($text);

    $ret_value = perform_query("update user "
    	. "\n set "
    	. "\n  profile_text='" . $text . "'"
        . " where  user_id='$user_id'",UPDATE); 

	if ($len > $settings->profile_text_limit) {
	   return "1^?".intext("Profile Updated (exceeded maximum length").", " . ($len - $settings->profile_text_limit) . " ".intext("characters were cut").")^?$ret_value"; 
	} else {
       return "1^?".intext("Profile Updated")."^?$ret_value";  
	}
}

function Invite($user_id,$members,$thread_id){
    $status = GetStatus(Check_Auth());

    //ensure the user is allowed to invite members
	$security = 0;
	$row = perform_query("select * from thread where thread_id='$thread_id'",SELECT); 
	if ($row->type == "2") {
	   $security = 1;
	}
	if (($row->type == "3") && ($row->author_id == $user_id)) {
	   $security = 1;
	}
	if ($security == 0) {
	   return "-1^?invalid request";
	}

	$member_list = explode(",",$members);  
	foreach ($member_list as $mem) {
	    $mem = trim($mem);
	    if ($mem == "") {continue;}
		if (is_numeric($mem)) {
           $row = perform_query("select * from user where user_id='". $mem . "'",SELECT); 
        } else {
	       $row = perform_query("select * from user where username='$mem'",SELECT); 
		}
		if (!$row) {continue;}
		if ($row->user_id == 0) {continue;}
		
	    //if thread is already on their list, skip user
	    if (preg_match('/,(-)?' . $thread_id . '(,|$)/',$row->my_private_threads)) { continue; }

		//see if the invited user has banned this person from sending them private threads
		$banned_list = pt_block_list($row->user_id);

		if ((preg_match('/,newusers(,|$)/',$banned_list)) && ($status == 0) ) { 
		   return "-1^?" . $row->username . intext(" has blocked new users from sending them private threads");
		}   
		if ((preg_match('/,allusers(,|$)/',$banned_list)) && ($status < 5) ) { 
		   return "-1^?" . $row->username . intext(" has blocked all users from sending them private threads");
		}   		
		if ((preg_match('/,' . $user_id . ';/',$banned_list)) && ($status < 5)) { 
		   return "-1^?" . $row->username . intext(" has blocked you from sending them private threads");
		}

	    $new_user_list .= "," . $row->user_id . ";" . mysql_real_escape_string($row->username);
		$readable_user_list .= ";" . $row->username;

		//add thread to users my_threads and my_private_threads
	    $cur = perform_query("update user set my_threads='" .  $row->my_threads . "," . $thread_id . ":0' where user_id=" . $row->user_id, UPDATE); 
		$cur = perform_query("update user set my_private_threads='" . $row->my_private_threads . "," . $thread_id . "' where user_id=" . $row->user_id, UPDATE); 
	}
	
	//add users to thread's block_allow_list
	$row = perform_query("select * from thread where thread_id='$thread_id'",SELECT); 
    $cur = perform_query("update thread set block_allow_list='" .  mysql_real_escape_string($row->block_allow_list) . $new_user_list .  "' where thread_id=" . $thread_id, UPDATE); 

	if ($readable_user_list != "") {
	   fix_punctuation($readable_user_list);
	   $row = perform_query("select username from user where user_id='". $user_id . "'",SELECT);
	   PostMsg(0, mysql_real_escape_string($row->username) . " " . intext("has invited") ." " .mysql_real_escape_string($readable_user_list) ." " . intext("to the thread"), $thread_id);
	}
	
	return "1^?invite finished";
}

function fix_punctuation(&$instring) {
	$list = explode(";",$instring);  
	$instring = "";
	for ($i = 0; $i < count($list); $i++) {
	   if ($list[$i] == "") {continue;} 
	   if ($i == count($list) - 1) {
	      $instring .= $list[$i];
	   } elseif ($i == count($list) - 2) {
	      $instring .= $list[$i] . " " . intext("and") . " ";
	   } else {
	      $instring .= $list[$i] . ", ";
	   }
	}
}

function PostPrivateThread($user_id, $pt_type, $members, $content_of_thread, $thread_title) {
	global $settings;
    $img_ret_val = array();
	$forum_id = 12;
	$i = 0;
	
	if ($thread_title == "") {$thread_title = "Untitled";}

	$status = 0;

    $row = perform_query("select status from user where user_id='" . $user_id . "'", SELECT);
	$status = $row->status;

	if ($msg = IsBanned(Check_Auth())) { return "-1^?".intext("You are not permitted to post a new thread").". ".$msg;}

	if (mb_strlen($content_of_thread) > $settings->max_post_length) {
	   return "-1^?".intext("Post is too long, maximum size is")." ". $settings->max_post_length . " ".intext("characters").".";
	}	
	if (mb_strlen(trim($content_of_thread)) == 0) {
	   return "-1^?".intext("Your post is empty");
	}		
		
	if (is_flood()) {
	   return "-1^?".intext("Flood attack detected, please wait a while before posting");
	}		
	
	if (mb_strlen($thread_title) > $settings->size_of_pt_title) { 
	   return "-1^?".intext("Thread title is too long");
	}
	
	if (($settings->status_to_start_pt > $status) && ($status != -1)){
	   return "-1^?".intext("Your status is not high enough to start private threads");	
	}
	if (!IsValidForum($forum_id)) {
	   return "-1^?".intext("This is not a valid forum.");
	}		

	$member_list = explode(",",$members);  
	$user_id_list = "";
	$block_allow_list = ""; 
	foreach ($member_list as $mem) {
	    $mem = trim($mem);
	    if ($mem == "") {continue;}
		if (is_numeric($mem)) {
           $row = perform_query("select * from user where user_id='". $mem . "'",SELECT); 
        } else {
	       $row = perform_query("select * from user where username='$mem'",SELECT); 
		}
		if (!$row) {continue;}
		if ($row->user_id == $user_id) {continue;}
		if ($row->user_id == 0) {continue;}
	    if (preg_match('/,' . $row->user_id . '(,|$)/',$user_id_list)) { continue; }  //avoid duplicates
				
		//see if the invited user has banned this person from sending them private threads
		$banned_list = pt_block_list($row->user_id);

		if ((preg_match('/,newusers(,|$)/',$banned_list)) && ($status == 0) ) { 
		   return "-1^?" . $row->username . intext(" has blocked new users from sending them private threads");
		}   
		if ((preg_match('/,allusers(,|$)/',$banned_list)) && ($status < 5) && ($status != -1) ) { 
		   return "-1^?" . $row->username . intext(" has blocked all users from sending them private threads");
		}
		//user has blocked all PTs but this is a System-created PT, skip user and continue
		if ((preg_match('/,allusers(,|$)/',$banned_list)) && ($status == -1) ) { 
		   continue; 
		}
		if ((preg_match('/,' . $user_id . ';/',$banned_list)) && ($status < 5)) { 
		   return "-1^?" . $row->username . intext(" has blocked you from sending them private threads");
		}
		
	    $block_allow_list .= "," . $row->user_id . ";" . $row->username;
		$user_id_list .= "," . $row->user_id;

		$members_my_threads[$i] = $row->my_threads;
		$members_my_private_threads[$i] = $row->my_private_threads;
		$i++;
	}

	if ($i == 0) {
	   return "-1^?".intext("You did not specify any users");
	}

	//special case for PT welcome thread, if all local members have blocked PTs then fail silently 
	if (($i == 1) && ($status == -1)) {
	   return;
	}
	
	$row = perform_query("SELECT * FROM user WHERE user_id=" . $user_id,SELECT);
    $avatar_id=$row->current_avatar; 
    $username=$row->username;
	$my_threads=$row->my_threads;		       
	$user_id_private_threads = $row->my_private_threads;
	
	if ($user_id) {
	   $extra = "," . $user_id . ";" . mysql_real_escape_string($username);
	} else { 
	   $extra = "";
	}

	$first_query = "insert thread "
    		. "\n set "
    		. "\n  author_id='" . $user_id . "',"
    		. "\n  title='" . $thread_title . "',"
    		. "\n  block_allow_list='" . mysql_real_escape_string($block_allow_list) . $extra . "',"	
    		. "\n  tstamp=now(),"
    		. "\n  creation_time=now(),"			
			. "\n  type=" . $pt_type . ","
    		. "\n  num_posts=1,"
    		. "\n  forum_id=12";
    
	$thread_id = perform_query($first_query,INSERT);
	$thread_id = rtrim($thread_id);
	
	$my_threads=$row->my_threads . "," . $thread_id . ":1";	
	
	hyperlinks_check($content_of_thread);
	rich_text_check($content_of_thread);
	emote_check($content_of_thread);
    $img_ret_val = image_check2($content_of_thread,$forum_id,$user_id);  	
	$file_ret_val = file_check2($content_of_thread,$forum_id,$user_id);
	youtube_check($content_of_thread);			
	image_links_check($content_of_thread);		
	make_spaces_check($content_of_thread);
	wordfilter_check($content_of_thread);

    $post_query = "insert post "
    	. "\n set "
    	. "\n  author_id='" . $user_id . "',"
    	. "\n  message='" . $content_of_thread . "',"
    	. "\n  thread_id=" . $thread_id . ","
    	. "\n  tstamp=now(),"
    	. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "'," 		
    	. "\n  avatar_id=" . $avatar_id . ","    		
    	. "\n  reply_num=" . 1 . ","
    	. "\n  author_name='" . mysql_real_escape_string($username) . "'";   		
    	
	$post_id = perform_query($post_query,INSERT);
    
	foreach ($img_ret_val as $i) {
	    perform_query("update file set post_id=" . $post_id . ", thread_id=" .$thread_id." where file_id=" . $i,UPDATE);
    }
	foreach ($file_ret_val as $i) {
	    perform_query("update file set post_id=" . $post_id . ", thread_id=" .$thread_id." where file_id=" . $i,UPDATE);
    }	
    perform_query("update thread set last_post_id_num='" . $post_id . "' where thread_id=" . $thread_id,UPDATE);

	if ($user_id) {
	   perform_query("update user set my_threads='" . $my_threads . "' where user_id=" . $user_id,UPDATE);
	   perform_query("update user set my_private_threads='" . $user_id_private_threads . "," . $thread_id . "' where user_id=" . $user_id, UPDATE); 
    }

	$i = 0;
	$user_id_list_array = explode(",",$user_id_list);  
	foreach ($user_id_list_array as $mem) {
	    $mem = trim($mem);
	    if ($mem == "" || $mem == $user_id) {continue;}
	    $cur = perform_query("update user set my_threads='" . $members_my_threads[$i] . "," . $thread_id . ":0' where user_id=" . $mem, UPDATE); 
		$cur = perform_query("update user set my_private_threads='" . $members_my_private_threads[$i] . "," . $thread_id . "' where user_id=" . $mem, UPDATE); 
		$i++;
	}

	perform_query("UPDATE user SET num_posts=num_posts+1 WHERE user_id=" . $user_id,UPDATE); 
		
	return "1^?" . $thread_id;
}

function UnwatchThread($thread_id) {
    global $userid;
	
	if (!IsValidThread($thread_id)) {return "-1^?".intext("Invalid thread");}
	
    $row = perform_query("select my_threads from user where user_id='$userid'",SELECT); 
             
    $updated_my_threads = preg_replace('/,' . 	$thread_id . ':[0-9]+/','', $row->my_threads);      

    $ret_value = perform_query("update user "
    	. "\n set "
    	. "\n  my_threads='" . $updated_my_threads . "'"
        . " where  user_id='$userid'",UPDATE); 

    return "1^?$ret_value";  
}

function WatchThread($thread_id,$total_posts) {
    global $userid;
	
	if (!IsValidThread($thread_id)) {return "-1^?".intext("Invalid thread");}
	$row2 = perform_query("select forum_id, block_allow_list from thread where thread_id='$thread_id'",SELECT); 
	if (($row2->forum_id == 12) && (!preg_match('/,' . $userid . ';/',$row2->block_allow_list))) { 
		return "-1^?".intext("Invalid thread");
	}
		
    $row = perform_query("select my_threads from user where user_id='$userid'",SELECT); 
             
    $updated_my_threads = $row->my_threads . "," . $thread_id . ":" . $total_posts;      

    $ret_value = perform_query("update user "
    	. "\n set "
    	. "\n  my_threads='" . $updated_my_threads . "'"
        . " where  user_id='$userid'",UPDATE); 

    return "1^?$ret_value";  
}

function NewUsername($newusername, $user_id) {
   global $settings;
   
   if ($settings->allow_username_change == 0) {
      return "-1^?".intext("Feature disabled");
   }

   if ($msg = IsBanned(Check_Auth())) { return "-1^?".intext("Unable to change username.")." ".$msg;}
	
   $newusername = trim($newusername);
   
   $temp = strtolower($newusername);
   if (mb_strlen($newusername) > $settings->max_username_length) {
      return "-1^?".intext("Name is too long");
   }
   if (($temp == "newusers") || ($temp == "allusers") || ($temp == "system")) {
        return "-1^?".intext("Name is reserved");
   }		
   if ($newusername == "") {
        return "-1^?".intext("Can't have blank name");
   }		
   if (is_numeric($newusername)) {
       return "-1^?".intext("User name can't be a number");
   }
   if (strpos($newusername, ";")) {
       return "-1^?".intext("Semicolons are not allowed in user name");
   }
   if (strpos($newusername, ",")) {
       return "-1^?".intext("Commas are not allowed in user name");
   }
   
   $row = perform_query("select * from user where username='$newusername'", SELECT);
   if ($row) { 
      return "-1^?".intext("User name already taken");
   }
   
   $row = perform_query("select * from user where user_id='$user_id'", SELECT);
   $forum_array = explode(",",$row->my_private_threads);
   
   foreach ($forum_array as $f) {
      if ($f == "") {continue;}
	  if (preg_match('/-[0-9]+/',$f)) { continue; }
	  $row2 = perform_query("select block_allow_list from thread where thread_id='$f'", SELECT);
	  if ($row2) {
		  $new_block_allow_list = preg_replace('/,' . $user_id . ';[^$,]+/', ',' . $user_id . ";" . $newusername, mysql_real_escape_string($row2->block_allow_list));
		  $ret_value = perform_query("update thread "
			 . "\n set "
			 . "\n  block_allow_list='" . $new_block_allow_list . "'"	
			 . " where thread_id='$f'",UPDATE);    
	  }
   }
		   
   perform_query("update user set username='" . $newusername . "' where user_id=" . $user_id,UPDATE);
          
   if ($settings->user_info_permanentness == 0) {
      perform_query("update post set author_name='$newusername' where author_id=$user_id",UPDATE);	  
   }		  
		  
   return "1^?".intext("Name changed")."^?".$ret_val."^?".stripslashes($newusername);	
}

function NewPassword($user_id, $oldpass, $newpass0, $newpass1) {
    $row = perform_query("select * from user where user_id='$user_id'", SELECT);
	if ($row->facebook_id || $row->linkedin_id) {
		return "-1^?".intext("You connect with Facebook or LinkedIn therefore do not need a password");
	}

   $row = perform_query("select * from user where user_id='$user_id' and password='".hash('sha256', $oldpass)."'", SELECT);
  
   if ($row == "") { 
      return "-1^?".intext("Wrong password");
   }
   if ($newpass0 != $newpass1) {
      return "-1^?".intext("Passwords don't match");
   }
   
   perform_query("update user set password='" . hash('sha256', $newpass0) . "' where user_id=" . $user_id, UPDATE);

   return "1^?".intext("Password changed");
}

function UpdateAvatar($avatar_number) {
    global $userid;
    global $settings;
   
    if ($msg = IsBanned(Check_Auth())) {return "-1^?".intext("Avatar cannot be updated.")." ".$msg;}
   
    if ($settings->user_info_permanentness == 0) {
	   $cur = perform_query("select * from file where author_id=$userid and avatar_number > 0 and is_deleted=0",MULTISELECT); 
	   while ($row = mysql_fetch_array( $cur )) {
	      if ($row["avatar_number"] == $avatar_number) {continue;}
		  perform_query("update file set is_deleted=2 where file_id=".$row["file_id"],UPDATE); 
		  unlink("files/avatar_".$row["author_id"]."_".$row["avatar_number"]."_".$row["internal_id"]);
	   }
	   perform_query("update post set avatar_id=$avatar_number where author_id=$userid",UPDATE);	  
	}

	perform_query("update user set current_avatar='" . $avatar_number . "' where user_id=" . $userid,UPDATE);

	return "1^?".intext("Avatar updated");	
}       
    
function SaveTheme($user_id,$theme) {
	perform_query("update user set theme='" . $theme . "' where user_id=" . $user_id,UPDATE);
	return "1^?".intext("Theme Saved");	
}    
    
function user_block_list($user_id) {
    $row = perform_query("select thread_block_list from user where user_id='". $user_id . "'",SELECT); 
	if ($row->thread_block_list == "") {return "";}
	
	$member_list = explode(",",$row->thread_block_list);  
	foreach ($member_list as $mem) {
	    $mem = trim($mem);
	    if ($mem == "") {continue;}
		if ($mem == "newusers") {$block_list .= ",newusers"; continue;}
		
        $row = perform_query("select last_ip from user where user_id='". $mem . "'",SELECT); 

		if (!$row) {continue;}
        if ($row->last_ip == "") {$row->last_ip = "null";}
		
	    $block_list .= "," . $mem . ";" . $row->last_ip;  
	}
	
	return $block_list;
}

function pt_block_list($user_id) {
    $row = perform_query("select pt_block_list from user where user_id='". $user_id . "'",SELECT); 
	if ($row->pt_block_list == "") {return "";}
	
	$block_list = "";
	$member_list = explode(",",$row->pt_block_list);  
	foreach ($member_list as $mem) {
	    $mem = trim($mem);
	    if ($mem == "") {continue;}
		if ($mem == "newusers") {$block_list .= ",newusers"; continue;}
		if ($mem == "allusers") {$block_list .= ",allusers"; continue;}
		if (preg_match("/^0;/",$mem)) {$block_list .= "," . $mem; continue;}		
				
        $row = perform_query("select last_ip from user where user_id='". $mem . "'",SELECT); 

		if (!$row) {continue;}
        if ($row->last_ip == "") {$row->last_ip = "null";}
		
	    $block_list .= "," . $mem . ";" . $row->last_ip;  
	}
	
	return $block_list;
}

function PostThreadToQueue($user_id, $forum_id, $content_of_thread, $thread_title, $wiki_type=0, $comment_type=0) {
	global $settings;
    $img_ret_val = array();
	
    if ($thread_title == "") {$thread_title = "Untitled";}
   
    $row = perform_query("select status from user where user_id='" . $user_id . "'", SELECT);
	$status = $row->status;
	
	if ($msg = IsBanned(Check_Auth())) { return "-1^?".intext("You are not permitted to post a new thread").". ".$msg;}

	if (mb_strlen($content_of_thread) > $settings->max_post_length) {
	   return "-1^?".intext("Post is too long, maximum size is")." ". $settings->max_post_length . " ".intext("characters").".";
	}	
	if (mb_strlen(trim($content_of_thread)) == 0) {
	   return "-1^?".intext("Your post is empty");
	}	

	if (is_flood()) {
	   return "-1^?".intext("Flood attack detected, please wait a while before posting");
	}	
	
	if ($forum_id == 13) {
		if (mb_strlen($thread_title) > $settings->size_of_article_title) { 
		   return "-1^?".intext("Article title is too long");
		}	
	} else {
		if (mb_strlen($thread_title) > $settings->size_of_thread_title) { 
		   return "-1^?".intext("Thread title is too long");
		}
	}

	$user_status = GetStatus($user_id);
	
	if ($forum_id == 13) {	
		if ($settings->status_to_create_articles > $user_status) {
	       return "-1^?".intext("User status not high enough to post articles");
		}  
	} else {
	    if ($settings->status_to_start_threads > $user_status) {
	       return "-1^?".intext("User status not high enough to post threads");
	    }
	}
	
	if (!IsValidForum($forum_id)) {
	   return "-1^?".intext("This is not a valid forum.");
	}
	
    $block_allow_list = user_block_list($user_id);
   
    wordfilter_check($thread_title);
   
	$ret_value = "";
	$first_query = "insert thread "
    		. "\n set "
    		. "\n  author_id='" . $user_id . "',"
    		. "\n  title='" . $thread_title . "',"
    		. "\n  block_allow_list='" . $block_allow_list . "',"			
    		. "\n  tstamp=now(),"
    		. "\n  creation_time=now(),"			
    		. "\n  num_posts=1,"
    		. "\n  needs_approval=1,"			
			. "\n  state = ".$comment_type.","
    		. "\n  forum_id=" . $forum_id;
	$thread_id = perform_query($first_query,INSERT); 
	$thread_id = rtrim($thread_id);
	$ret_value .= "^?" . $thread_id;
	
	$row = perform_query("SELECT * FROM user WHERE user_id=" . $user_id, SELECT);	
    $avatar_id=$row->current_avatar; 
    $username=$row->username;
    $my_threads=$row->my_threads . "," . $thread_id . ":1";	
	
	hyperlinks_check($content_of_thread);
	rich_text_check($content_of_thread);
	wordfilter_check($content_of_thread);		
	emote_check($content_of_thread);
	youtube_check($content_of_thread);			
    $img_ret_val = image_check2($content_of_thread,$forum_id,$user_id);
	$file_ret_val = file_check2($content_of_thread,$forum_id,$user_id);	
	image_links_check($content_of_thread);		
	make_spaces_check($content_of_thread);
	
    $post_query = "insert post "
    	. "\n set "
    	. "\n  author_id='" . $user_id . "',"
    	. "\n  message='" . $content_of_thread . "',"
    	. "\n  thread_id=" . $thread_id . ","
    	. "\n  tstamp=now(),"
    	. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "'," 		
    	. "\n  avatar_id=" . $avatar_id . ","    		
    	. "\n  reply_num=" . 1 . ","
		. "\n  type = ".$wiki_type.","					
    	. "\n  author_name='" . mysql_real_escape_string($username) . "'";   		
	$post_id = perform_query($post_query,INSERT);
	
	foreach ($img_ret_val as $i) {
	    perform_query("update file set post_id=" . $post_id . ", thread_id=" .$thread_id." where file_id=" . $i,UPDATE);
    }
	foreach ($file_ret_val as $i) {
	    perform_query("update file set post_id=" . $post_id . ", thread_id=" .$thread_id." where file_id=" . $i,UPDATE);
    }
	
    perform_query("update thread set last_post_id_num='" . $post_id . "' where thread_id=" . $thread_id,UPDATE);
	perform_query("update user set my_threads='" . $my_threads . "' where user_id=" . $user_id,UPDATE);
	perform_query("UPDATE user SET num_posts=num_posts+1 WHERE user_id=" . $user_id,UPDATE); 
	
	//the my_private_threads column in the System account is being used to store the user ids of users who want to
	//automatically watch all new threads.  
	$row = perform_query("select my_private_threads from user where user_id=0", SELECT);
    $user_list = explode(",",$row->my_private_threads);  
	foreach ($user_list as $user) {
	   if ($user == "") {continue;}
	   if ($user == $user_id) {continue;}
	   
	   $row2 = perform_query("select my_threads from user where user_id=$user", SELECT);
       $my_threads=$row2->my_threads . "," . $thread_id . ":0";		
       perform_query("update user set my_threads='".$my_threads."' WHERE user_id=$user",UPDATE); 
	}
	
	return "1^?" . $thread_id;
}


function PostThread($user_id, $forum_id, $content_of_thread, $thread_title, $wiki_type=0, $comment_type=0) {
	global $settings;
    $img_ret_val = array();
	
    if ($thread_title == "") {$thread_title = "Untitled";}
   
    $row = perform_query("select status from user where user_id='" . $user_id . "'", SELECT);
	$status = $row->status;
	
	if ($msg = IsBanned(Check_Auth())) { return "-1^?".intext("You are not permitted to post a new thread").". ".$msg;}

	if (mb_strlen($content_of_thread) > $settings->max_post_length) {
	   return "-1^?".intext("Post is too long, maximum size is")." ". $settings->max_post_length . " ".intext("characters").".";
	}	
	if (mb_strlen(trim($content_of_thread)) == 0) {
	   return "-1^?".intext("Your post is empty");
	}	

	if (is_flood()) {
	   return "-1^?".intext("Flood attack detected, please wait a while before posting");
	}	
	
	if ($forum_id == 13) {
		if (mb_strlen($thread_title) > $settings->size_of_article_title) { 
		   return "-1^?".intext("Article title is too long");
		}	
	} else {
		if (mb_strlen($thread_title) > $settings->size_of_thread_title) { 
		   return "-1^?".intext("Thread title is too long");
		}
	}

	$user_status = GetStatus($user_id);
	
	if ($forum_id == 13) {	
		if ($settings->status_to_create_articles > $user_status) {
	       return "-1^?".intext("User status not high enough to post articles");
		}  
	} else {
	    if ($settings->status_to_start_threads > $user_status) {
	       return "-1^?".intext("User status not high enough to post threads");
	    }
	}
	
	if (!IsValidForum($forum_id)) {
	   return "-1^?".intext("This is not a valid forum.");
	}
	
    $block_allow_list = user_block_list($user_id);
   
    wordfilter_check($thread_title);
   
    if ($user_status == 0 && $settings->post_approval) {$extra = "\n needs_approval = 1,";} else {$extra = "";}
   
	$ret_value = "";
	$first_query = "insert thread "
    		. "\n set "
    		. "\n  author_id='" . $user_id . "',"
    		. "\n  title='" . $thread_title . "',"
    		. "\n  block_allow_list='" . $block_allow_list . "',"			
    		. "\n  tstamp=now(),"
			. $extra
    		. "\n  creation_time=now(),"			
    		. "\n  num_posts=1,"
			. "\n  state = ".$comment_type.","
    		. "\n  forum_id=" . $forum_id;
	$thread_id = perform_query($first_query,INSERT); 
	$thread_id = rtrim($thread_id);
	$ret_value .= "^?" . $thread_id;
	
	$row = perform_query("SELECT * FROM user WHERE user_id=" . $user_id, SELECT);	
    $avatar_id=$row->current_avatar; 
    $username=$row->username;
    $my_threads=$row->my_threads . "," . $thread_id . ":1";	
	
	hyperlinks_check($content_of_thread);
	rich_text_check($content_of_thread);
	wordfilter_check($content_of_thread);		
	emote_check($content_of_thread);
	youtube_check($content_of_thread);			
    $img_ret_val = image_check2($content_of_thread,$forum_id,$user_id);
	$file_ret_val = file_check2($content_of_thread,$forum_id,$user_id);	
	image_links_check($content_of_thread);		
	make_spaces_check($content_of_thread);
	
    $post_query = "insert post "
    	. "\n set "
    	. "\n  author_id='" . $user_id . "',"
    	. "\n  message='" . $content_of_thread . "',"
    	. "\n  thread_id=" . $thread_id . ","
    	. "\n  tstamp=now(),"
    	. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "'," 		
    	. "\n  avatar_id=" . $avatar_id . ","    		
    	. "\n  reply_num=" . 1 . ","
		. "\n  type = ".$wiki_type.","					
    	. "\n  author_name='" . mysql_real_escape_string($username) . "'";   		
	$post_id = perform_query($post_query,INSERT);
		
	foreach ($img_ret_val as $i) {
	    perform_query("update file set post_id=" . $post_id . ", thread_id=" .$thread_id." where file_id=" . $i,UPDATE);
    }
	foreach ($file_ret_val as $i) {
	    perform_query("update file set post_id=" . $post_id . ", thread_id=" .$thread_id." where file_id=" . $i,UPDATE);
    }
	
    perform_query("update thread set last_post_id_num='" . $post_id . "' where thread_id=" . $thread_id,UPDATE);
	perform_query("update user set my_threads='" . $my_threads . "' where user_id=" . $user_id,UPDATE);
	perform_query("UPDATE user SET num_posts=num_posts+1 WHERE user_id=" . $user_id,UPDATE); 
	
	//the my_private_threads column in the System account is being used to store the user ids of users who want to
	//automatically watch all new threads.  
	$row = perform_query("select my_private_threads from user where user_id=0", SELECT);
    $user_list = explode(",",$row->my_private_threads);  
	foreach ($user_list as $user) {
	   if ($user == "") {continue;}
	   if ($user == $user_id) {continue;}
	   
	   $row2 = perform_query("select my_threads from user where user_id=$user", SELECT);
       $my_threads=$row2->my_threads . "," . $thread_id . ":0";		
       perform_query("update user set my_threads='".$my_threads."' WHERE user_id=$user",UPDATE); 
	}
	
	if (($user_status == 0) && $settings->post_approval && ($force == 0)) {
	   add_to_approval_queue('thread',$user_id,$thread_id,$post_id,'',$forum_id,$thread_title);
       return "-2^?".intext("Your post has been received and is pending approval");
	}
	
	return "1^?" . $thread_id;
}

function PostMsg($user_id, $theinput, $thread_id, $force=0) {
	global $settings;
	$img_ret_val = array();
	
    $row = perform_query("select status from user where user_id='" . $user_id . "'", SELECT);
	$status = $row->status;

    $row = perform_query("select * from thread where thread_id='" . $thread_id . "'", SELECT);
	
	//when $force equals 1 it's a post caused by the internal system, so normal restrictions do not apply
	if ((($row->state == 1) || ($row->state == 4)) && ($force == 0)) {
	   return "-1^?".intext("Thread has been closed");
	}
	
	if (($row->type == 0) && ($status > -1) && ($status != 5) && ($force == 0)) {
	    $block_allow_list = $row->block_allow_list;
	
		if ((preg_match('/,newusers/',$block_allow_list)) && ($status == 0) && ($user_id != $row->author_id)) { 
		   return "-1^?".intext("New users are not allowed to post in this thread");
		}   
		if (preg_match('/,' . $user_id . ';/',$block_allow_list)) { 
		   return "-1^?".intext("You are not allowed to post in this thread");
		}
	}
	
	if (($msg = IsBanned(Check_Auth())) && ($force == 0)) { return "-1^?".intext("You are not allowed to post in this thread").". ".$msg;}

	if (mb_strlen($theinput) > $settings->max_post_length) {
	   return "-1^?".intext("Post is too long, maximum size is")." ". $settings->max_post_length . " ".intext("characters").".";
	}
	if (mb_strlen(trim($theinput)) == 0) {
	   return "-1^?".intext("Your post is empty");
	}	
	if (is_flood() && ($force == 0)) {
	   return "-1^?".intext("Flood attack detected, please wait a while before posting");
	}
	if (!IsValidThread($thread_id,1)) {
	   return "-1^?".intext("This is not a valid thread");	
	}
	
	$num_posts = $row->num_posts;
	$num_posts++;

	$row2 = perform_query("SELECT * FROM user WHERE user_id='$user_id'",SELECT);
	$userid=$row2->user_id; 
	$username=$row2->username; 
	$avatar_id=$row2->current_avatar; 
	
	if (!($status == 0 && $settings->post_approval && $force == 0)) {
		$my_threads=$row2->my_threads; 

		if (preg_match('/,' . $thread_id . ':[0-9]+/',$my_threads) != 0) {
		  $my_threads = preg_replace('/,' . 	$thread_id . ':[0-9]+/',',' . $thread_id . ':' . $num_posts, $my_threads);         
		} else {
		  $my_threads .= ',' . $thread_id . ':' . $num_posts;
		}
		
		perform_query("update user set my_threads='" . $my_threads . "' where user_id=" . $user_id,UPDATE);
	}
	
	hyperlinks_check($theinput); 
	rich_text_check($theinput);
	wordfilter_check($theinput);	
	emote_check($theinput);
	$img_ret_val = image_check($theinput,$thread_id,$user_id);
	$file_ret_val = file_check($theinput,$thread_id,$user_id);
	youtube_check($theinput);		
    image_links_check($theinput);	
	make_spaces_check($theinput);

	if ($status == 0 && $settings->post_approval && $force == 0) {
	   $extra = "\n needs_approval = 1,";
	} else {
       $extra = "";
    }
	
	$post_query = "insert post "
    		. "\n set "
    		. "\n  author_id='" . $userid . "',"
    		. "\n  message='" . $theinput . "',"
    		. "\n  thread_id=" . $thread_id . ","
    		. "\n  tstamp=now(),"
    		. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
    		. "\n  avatar_id=" . $avatar_id . ","
    		. "\n  reply_num=" . $num_posts . ","
			. $extra
    		. "\n  author_name='" . mysql_real_escape_string($username) . "'";
			
	$post_id = perform_query($post_query, INSERT);	
	
	if (!($status == 0 && $settings->post_approval && $force == 0)) {
       perform_query("update thread set num_posts=" . $num_posts . ",last_post_id_num='" . $post_id . "' where thread_id=" . $thread_id, UPDATE);
    }
	
	foreach ($img_ret_val as $i) {
	    perform_query("update file set post_id=" . $post_id . " where file_id=" . $i . " and post_id=0", UPDATE);
    }
	foreach ($file_ret_val as $i) {
	    perform_query("update file set post_id=" . $post_id . " where file_id=" . $i . " and post_id=0", UPDATE);
    }
	
	if ($status == 0 && $settings->post_approval && $force == 0) {
	   add_to_approval_queue('post',$userid,$thread_id,$post_id,'',$row->forum_id);
       return "-2^?".intext("Your post has been received and is pending approval");
    } else {
	   perform_query("UPDATE user SET num_posts=num_posts+1 WHERE user_id=" . $userid, UPDATE); 
	   return $thread_id . "^?" . $num_posts . "^?" . $post_id;
	}
}
 
function is_flood() {
   global $settings;
     
   $my_query = "SELECT count( * ) as recent_posts FROM post where ip_address = '" .$_SERVER['REMOTE_ADDR'] . "' AND tstamp > DATE_SUB(now(),INTERVAL " . $settings->flood_time . " SECOND)";
   $row = perform_query($my_query,SELECT);    	   
   if ($row->recent_posts + 1 > $settings->flood_num_posts ) {
      return 1;
   } else {
      return 0;
   }
} 

function hyperlinks_check(&$text) {
   $text = preg_replace('#(https?://)([^\s\[<]+)#', '<a target="_blank" href="\1\2">\1\2</a>', $text);
}

function make_spaces_check(&$text) {
   while (preg_match("/  /", $text)) {
      $text = preg_replace('#  #', '&nbsp;&nbsp;', $text);
   }
}

function rich_text_check(&$theinput) {
    define('QUOTE_NEST_LIMIT',5); //maximum depth quotes can go
    global $settings;
   
	if ($settings->allow_rich_text) {
	   $theinput = preg_replace("#(&lt;|\[)(b|i|u|B|I|U)(&gt;|\])#",'<${2}>',$theinput); 
	   $theinput = preg_replace("#(&lt;|\[)/(b|i|u|B|I|U)(&gt;|\])#",'</${2}>',$theinput); 
	}
	   
	//change <q> into quote divs
	$theinput = preg_replace("#\[QUOTE\]#",'<div class="quote">',$theinput,QUOTE_NEST_LIMIT); 
	$theinput = preg_replace("#\[/QUOTE\]#",'</div>',$theinput,QUOTE_NEST_LIMIT); 
} 

function wordfilter_check(&$theinput) {
   global $settings;
   global $word_filter;

   if ($settings->word_filter) {
      include('word_filter.php');
      
      foreach ($word_filter as $key=>$val) {
         $theinput = preg_replace("/$key/i",$val,$theinput); 
      }
   }
}
 
function emote_check(&$theinput) {
   global $settings;
  
   if ($settings->emotes_allowed == 1) {
      if (preg_match_all('/:[\S]+?:/',$theinput, $matches)) {
	     
		 foreach($matches[0] as $i){
		    $j = preg_replace('/:/','',$i);		    
		
		    if (file_exists("emotes/" . $j . ".png")) {
			   $theinput = preg_replace("#([^\"]|^)$i#",'${1}<img title="'.$i.'" alt="'.$i.'" src="emotes/' . $j . '.png">',$theinput,1); 
		    }
		    if (file_exists("emotes/" . $j . ".jpg")) {
			   $theinput = preg_replace("#([^\"]|^)$i#",'${1}<img title="'.$i.'" alt="'.$i.'" src="emotes/' . $j . '.jpg">',$theinput,1); 
		    }
		    if (file_exists("emotes/" . $j . ".jpeg")) {
			   $theinput = preg_replace("#([^\"]|^)$i#",'${1}<img title="'.$i.'" alt="'.$i.'" src="emotes/' . $j . '.jpeg">',$theinput,1); 
		    }		   
		    if (file_exists("emotes/" . $j . ".gif")) {
			  $theinput = preg_replace("#([^\"]|^)$i#",'${1}<img title="'.$i.'" alt="'.$i.'" src="emotes/' . $j . '.gif">',$theinput,1); 
		    }		   
		 }
      }
   }
}

function youtube_check(&$theinput) {
   global $settings;

   if ((GetStatus(Check_Auth())) < $settings->status_to_embed) {
      return;
   }

   if ($settings->youtube_linking_allowed == 1) {
		while (preg_match('/\[((YOUTUBE)|(youtube))\].*?(<a href=".*">http:\/\/.*v=)?(<a href=".*">http:\/\/.*\/)?([A-Za-z0-9\-_]+)(&[^\]]*)?(#t=[^\]]*)?(<\/a>)?[ ]*\[\/((YOUTUBE)|(youtube))\]/',$theinput)) {
			//url contains a timecode, #t=...
			if (preg_match('/#t=[0-9smh]+/',$theinput, $matches)) {
				preg_match('/[0-9]+(s)/',$matches[0], $t);
				$seconds = substr($t[0], 0, -1);
				preg_match('/[0-9]+m/',$matches[0], $t);
				$minutes = substr($t[0], 0, -1);	
				preg_match('/[0-9]+h/',$matches[0], $t);
				$hours = substr($t[0], 0, -1);
				$start_time = $seconds + ($minutes * 60) + ($hours * 3600);

				//start time consists only of numbers, treat them as seconds
				if (!(preg_match('/[smh]+/',$matches[0]))) {
					preg_match('/[0-9]+/',$matches[0], $t);
					$start_time = $t[0];
				}

				$theinput = preg_replace('/\[((YOUTUBE)|(youtube))\].*?(<a href=".*">http:\/\/.*v=)?(<a href=".*">http:\/\/.*\/)?([A-Za-z0-9\-_]+)(&[^\]]*)?(#t=[^\]]*)?(<\/a>)?[ ]*\[\/((YOUTUBE)|(youtube))\]/',
				'<iframe class="youtube-player" type="text/html" width="425" height="355" src="http://www.youtube.com/embed/${6}?start='.$start_time.'" frameborder="0"></iframe>', $theinput, 1);	
			} else {
				$theinput = preg_replace('/\[((YOUTUBE)|(youtube))\].*?(<a href=".*">http:\/\/.*v=)?(<a href=".*">http:\/\/.*\/)?([A-Za-z0-9\-_]+)(&[^\]]*)?(<\/a>)?[ ]*\[\/((YOUTUBE)|(youtube))\]/',
				'<iframe class="youtube-player" type="text/html" width="425" height="355" src="http://www.youtube.com/embed/${6}" frameborder="0"></iframe>', $theinput, 1);	
			}
		}
   }
   $theinput = preg_replace('/\[(\/)?(youtube|YOUTUBE)\]/', '', $theinput);	
   return 1;
}
 
function image_links_check(&$theinput) {
   global $settings;
   
   if ($settings->image_linking_allowed == 0) {
      return;
   }
   if ((GetStatus(Check_Auth())) < $settings->status_to_embed) {
      return;
   }

   $num = preg_match_all('/\[((IMG)|(img))\]\s*(<a.*?>)?(https?:\/\/)?([^<>\[\] ]+)(<\/a>)?\s*\[\/((IMG)|(img))\]/', $theinput, $tagmatches);

   for ($i=0; $i<$num; $i++) {
	   preg_match('#(https?://)([-a-zA-Z0-9@:;*%()_+.,~\#?&//=]+)#', $tagmatches[0][$i], $urlmatches);
	   
	   $allowed = true;
	   if ($settings->img_url_whitelist) {
	       $allowed = false;
           foreach ($settings->img_url_whitelist as $cur) {
		      if (preg_match('#(https?://)([-a-zA-Z0-9@:;*%()_+.,]+)?'.$cur.'/([-a-zA-Z0-9@:;*%()_+.,~\#?&//=]+)#', $urlmatches[0])) {
			     $allowed = true;
			  }
		   }
	   }
	   if ($settings->img_url_blacklist) {
           foreach ($settings->img_url_blacklist as $cur) {
		      if (preg_match('#(https?://)([-a-zA-Z0-9@:;*%()_+.,]+)?'.$cur.'/([-a-zA-Z0-9@:;*%()_+.,~\#?&//=]+)#', $urlmatches[0])) {
			     $allowed = false;
			  }
		   }
	   }	   
	   
	   if ($allowed) {
		   $url = $urlmatches[0];
		   $thumb_size = remote_image_thumbnail($url,$settings->thumb_width,$settings->thumb_height);
		   if ($thumb_size[0] != 0) {
			  $random_number = rand(1000,10000);
			  $theinput = str_replace($tagmatches[0][$i], '<div class="inline_img" id="'.$random_number.'_eimg"> <a href="javascript:enlarge_offsite_image('.$random_number.')"> <img width='.$thumb_size[0].' height='.$thumb_size[1].' id="'.$thumb_size[0].','.$thumb_size[1].'" alt="'.$url.'" title="'.$url.'" src="'.$url.'"></a></div>', $theinput);
		   } else {
			  $theinput = str_replace($tagmatches[0][$i], '<img alt="'.$url.'" title="'.$url.'" src="'.$url.'">', $theinput);
		   }
	   }
   }

   //remove tags that didn't do anything
   $theinput = preg_replace('/\[(\/)?(img|IMG)\]/', '', $theinput);	

   return;
}

//called when making a post in an existing thread
function file_check(&$theinput,$thread_id,$user_id){
   global $settings;
   $ret_val = array();
   $q = "select * from file where is_deleted=0 and file_type=0 and author_id='$user_id'". " and thread_id='$thread_id' and post_id=0 ORDER BY `file_id` DESC";
   $cur = perform_query($q,MULTISELECT); 
   
   if (!mysql_num_rows($cur)) { 
	  return $ret_val;
   }
   
   while ($row = mysql_fetch_array( $cur )) {
      $internal_id = md5(mt_rand());   
	  $external_id = md5(mt_rand());   
	  array_push($ret_val,$row["file_id"]);
	  rename('files/tmp/from_' . $user_id . '_' . $row["file_id"],'files/' . $internal_id);
	  chmod('files/' . $internal_id, 0644);
	  perform_query("update file set external_id='$external_id', internal_id='$internal_id' where file_id= ".$row["file_id"],UPDATE);
	  $theinput .= '&lt;&lt;&lt;FILE:' . $row["filename"] . '|' . $external_id . '&gt;&gt;&gt;';
   }

   return $ret_val;
}

//called when making the first post of a thread
function file_check2(&$theinput,$forum_id,$user_id){
   global $settings;
   $ret_val = array();
   $q = "select * from file where is_deleted=0 and file_type=0 and author_id='$user_id'". " and thread_id=-1 and post_id=-1 ORDER BY `file_id` DESC";   
   $cur = perform_query($q,MULTISELECT); 
   
   if (!mysql_num_rows($cur)) { 
	  return $ret_val;
   }
           	   
   while ($row = mysql_fetch_array( $cur )) {
      $internal_id = md5(mt_rand());   
	  $external_id = md5(mt_rand());      
	  array_push($ret_val,$row["file_id"]);
	  rename('files/tmp/from_' . $user_id . '_' . $row["file_id"], 'files/' . $internal_id);	  
	  chmod('files/' . $internal_id, 0644);
	  perform_query("update file set external_id='$external_id', internal_id='$internal_id' where file_id= ".$row["file_id"],UPDATE);
	  $theinput .= '&lt;&lt;&lt;FILE:' . $row["filename"] . '|' . $external_id . '&gt;&gt;&gt;';	  
   }

   return $ret_val;
}

//called when editing a post
function file_check3(&$theinput,$thread_id,$user_id,$post_id,$reply_num){
   $ret_val = array();
     
   if ($reply_num == 1) {
      $q = "select * from file where file_type=0 and author_id='$user_id' and post_id=$post_id ORDER BY `file_id`";   
   } else {   
      $q = "select * from file where file_type=0 and author_id='$user_id' and thread_id='$thread_id' and post_id=$post_id ORDER BY `file_id`";
   }
	  
   $cur = perform_query($q,MULTISELECT); 
   
    if (!mysql_num_rows($cur)) { 
	   return $ret_val;
	}
	
    while ($row = mysql_fetch_array( $cur )) {
	   if (!preg_match('/&lt;&lt;&lt;FILE:' . preg_quote($row["filename"]) . '\|' . $row["external_id"] . '&gt;&gt;&gt;/',$theinput)) {  
	      //file was edited out of post, delete it
		  unlink('files/' . $row["internal_id"]);
		  perform_query("update file set is_deleted=2 where file_id= ".$row["file_id"],UPDATE);
	   } 
   }	
}

//called when making a post in an existing thread
function image_check(&$theinput,$thread_id,$user_id){
   global $settings;
   $ret_val = array();
   $q = "select * from file where is_deleted=0 and file_type=1 and author_id='$user_id'". " and thread_id='$thread_id' and post_id=0 ORDER BY `file_id` DESC";
   $cur = perform_query($q,MULTISELECT); 
              	   
    while ($row = mysql_fetch_array( $cur )) {
	   if (preg_match('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/',$theinput)) {  
   	        $random_number = rand(1000,10000);	   
			$internal_id = md5(mt_rand());   
			$external_id = md5(mt_rand());   
			array_push($ret_val,$row["file_id"]);

            if (file_exists('files/tmp/from_' . $user_id . '_t_' . $row["file_id"])) {
			   rename('files/tmp/from_' . $user_id . '_t_' . $row["file_id"],'files/t_' . $internal_id);
			   rename('files/tmp/from_' . $user_id . '_' . $row["file_id"],'files/' . $internal_id);
			   chmod('files/t_' . $row["file_id"], 0644);
			   chmod('files/' . $row["file_id"], 0644);
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . "|" .$random_number.'_img"> <a href=javascript:enlarge_image("' . $row["filename"] .'","' . $external_id .'",' . $random_number .')> <img src="file.php?id='.$external_id.'&t=small&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></a></div>',$theinput,1);
               perform_query("update file set external_id='$external_id', internal_id='$internal_id' where file_id= ".$row["file_id"],UPDATE);	
			} else {
 			   rename('files/tmp/from_' . $user_id . '_' . $row["file_id"],'files/' . $internal_id);
			   chmod('files/' . $row["file_id"], 0644);
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . '"> <img src="file.php?id='.$external_id.'&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></div>',$theinput,1);	
               perform_query("update file set external_id='$external_id', internal_id='$internal_id' where file_id= ".$row["file_id"],UPDATE);			   
            }
       } else {
		    unlink("files/tmp/from_".$user_id."_".$row["file_id"]);  
		    unlink("files/tmp/from_".$user_id."_t_".$row["file_id"]);  
		    perform_query("update file set is_deleted=2 where file_id=".$row["file_id"],UPDATE); 
	   }
   }
   
   //In order to allow an uploaded image to appear in a quotation
   $q = "select * from file where file_type=1 and thread_id='$thread_id' and post_id!=0 ORDER BY `file_id`";
   $cur = perform_query($q,MULTISELECT); 
   
    while ($row = mysql_fetch_array( $cur )) {
	   if (preg_match('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/',$theinput)) {  
	        $random_number = rand(1000,10000);	   
            if (file_exists('files/t_' . $row["internal_id"])) {
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . "|" .$random_number.'_img"> <a href=javascript:enlarge_image("' . $row["filename"] .'","' . $row["external_id"] .'",' . $random_number .')> <img src="file.php?id='.$row["external_id"].'&t=small&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></a></div>',$theinput,1);
            } else {
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . '"> <img src="file.php?id='.$row["external_id"].'&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></div>',$theinput,1);	
            }
       }
   }   
   return $ret_val;
}

//called when making the first post of a thread
function image_check2(&$theinput,$forum_id,$user_id){
   global $settings;
   $ret_val = array();
   $q = "select * from file where is_deleted=0 and file_type=1 and author_id='$user_id'". " and thread_id=-1 and post_id=-1 ORDER BY `file_id` DESC";   

   $cur = perform_query($q,MULTISELECT); 

    if (!mysql_num_rows($cur)) { 
	   return $ret_val;
	}

    while ($row = mysql_fetch_array( $cur )) {
	   if (preg_match('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/',$theinput)) {  
   	        $random_number = rand(1000,10000);	   
		    $internal_id = md5(mt_rand());   
			$external_id = md5(mt_rand());   
			array_push($ret_val,$row["file_id"]);

            if (file_exists('files/tmp/from_' . $user_id . '_t_' . $row["file_id"])) {
			   rename('files/tmp/from_' . $user_id . '_t_' . $row["file_id"],'files/t_' . $internal_id);
			   rename('files/tmp/from_' . $user_id . '_' . $row["file_id"],'files/' . $internal_id);
			   chmod('files/t_' . $row["file_id"], 0644);
			   chmod('files/' . $row["file_id"], 0644);
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . "|" .$random_number.'_img"> <a href=javascript:enlarge_image("' . $row["filename"] .'","' . $external_id .'",' . $random_number .')> <img src="file.php?id='.$external_id.'&t=small&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></a></div>',$theinput,1);
               perform_query("update file set external_id='$external_id', internal_id='$internal_id' where file_id= ".$row["file_id"],UPDATE);	
			} else {
 			   rename('files/tmp/from_' . $user_id . '_' . $row["file_id"],'files/' . $internal_id);
			   chmod('files/' . $row["file_id"], 0644);
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . '"><img src="file.php?id='.$external_id.'&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></div>',$theinput,1);	
               perform_query("update file set external_id='$external_id', internal_id='$internal_id' where file_id= ".$row["file_id"],UPDATE);			   
            }
       } else {
		    unlink("files/tmp/from_".$user_id."_".$row["file_id"]);  
		    unlink("files/tmp/from_".$user_id."_t_".$row["file_id"]);  
		    perform_query("update file set is_deleted=2 where file_id=".$row["file_id"],UPDATE); 
	   }
   }   

   return $ret_val;
}

//called when editing a post
function image_check3(&$theinput,$thread_id,$user_id,$post_id,$reply_num){  
   if ($reply_num == 1) {
      $q = "select * from file where file_type=1 and author_id='$user_id' and post_id=$post_id ORDER BY `file_id`";   
   } else {   
      $q = "select * from file where file_type=1 and author_id='$user_id' and thread_id='$thread_id' and post_id=$post_id ORDER BY `file_id`";
   }
	  
   $cur = perform_query($q,MULTISELECT); 
   
    while ($row = mysql_fetch_array( $cur )) {
	   if (preg_match('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/',$theinput)) {  
	        $random_number = rand(1000,10000);	   
            if (file_exists('files/t_' . $row["internal_id"])) {
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . "|" .$random_number.'_img"> <a href=javascript:enlarge_image("' . $row["filename"] .'","' . $row["external_id"] .'",' . $random_number .')> <img src="file.php?id='.$row["external_id"].'&t=small&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></a></div>',$theinput,1);
            } else {
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . '"><img src="file.php?id='. $row["external_id"].'&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></div>',$theinput,1);	
            }
       }
	   else {
	      //image was edited out of post, delete it
          if (file_exists('files/t_' . $row["internal_id"])) {
		  	   unlink('files/t_' . $row["internal_id"]); 
			   unlink('files/' . $row["internal_id"]);		   
	      } else {
		       unlink('files/' . $row["internal_id"]);
		  }
		  perform_query("update file set is_deleted=2 where file_id= ".$row["file_id"],UPDATE);
	   }
   }
   
   //In order to allow an uploaded image to appear in a quotation
   $q = "select * from file where file_type=1 and thread_id='$thread_id' and post_id!=0 ORDER BY `file_id`";
   $cur = perform_query($q,MULTISELECT); 
   
    while ($row = mysql_fetch_array( $cur )) {
	   if (preg_match('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/',$theinput)) {  
	        $random_number = rand(1000,10000);	   
            if (file_exists('files/t_' . $row["internal_id"])) {
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . "|" .$random_number.'_img"> <a href=javascript:enlarge_image("' . $row["filename"] .'","' . $row["external_id"] .'",' . $random_number .')> <img src="file.php?id='.$row["external_id"].'&t=small&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></a></div>',$theinput,1);
            } else {
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . '"><img src="file.php?id='. $row["external_id"].'&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></div>',$theinput,1);	
            }
       }
   }     
}

//called when editing a wiki article
function image_check4(&$theinput,$thread_id,$user_id,$post_id,$reply_num){  
   $q = "select * from file where file_type=1 and thread_id='$thread_id' ORDER BY `file_id`";
	  
   $cur = perform_query($q,MULTISELECT); 
   
    if (!mysql_num_rows($cur)) { 
	   return $ret_val;
	}
           	   
    while ($row = mysql_fetch_array( $cur )) {
	   if (preg_match('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/',$theinput)) {  
	        $random_number = rand(1000,10000);	   
            if (file_exists('files/t_' . $row["internal_id"])) {
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . "|" .$random_number.'_img"> <a href=javascript:enlarge_image("' . $row["filename"] .'","' . $row["external_id"] .'",' . $random_number .')> <img src="file.php?id='.$row["external_id"].'&t=small&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></a></div>',$theinput,1);
            } else {
               $theinput = preg_replace('/&lt;&lt;&lt;image:' . preg_quote($row["filename"]) . '&gt;&gt;&gt;/','<div class="inline_img" id="' . $row["filename"] . '"><img src="file.php?id='. $row["external_id"].'&d=inline" title="'.$row["filename"].'" alt="'.$row["filename"].'"></div>',$theinput,1);	
            }
       }
   }
}

function GetForumInfo($forum_id) {	
    global $settings;
    if (!IsValidForum($forum_id)) {return "-1^?".intext("Invalid forum");}

	return($forum_id);
}

function AddAvatar($user_id){
	$row = perform_query("SELECT * FROM user WHERE user_id='$user_id'",SELECT);
	$new_total = $row->total_avatars + 1;
	perform_query("update user set total_avatars=" .$new_total. " where user_id='$user_id'", UPDATE); 

	return $new_total;
}
	
function GetInfo($userinfo) {				   	
	if (is_numeric($userinfo)){	
		$first_query = "SELECT * "
			. "\nFROM user "
			. "\nWHERE user_id='$userinfo'";
	} else {
		$first_query = "SELECT * "
			. "\nFROM user "
			. "\nWHERE username='$userinfo'";
	}
	$row = perform_query($first_query,SELECT);
	
	$username = $row->username;
	$user_id = $row->user_id;
	$status = $row->status;
	$user_id_hex = $row->user_id;
	$theme = $row->theme;
	$current_avatar = $row->current_avatar;
	$total_avatars = $row->total_avatars;
	$my_threads = $row->my_threads;
	
	return "1^?$username^?$user_id^?" .strtoupper($user_id_hex) . "^? ^?$status^?$theme^?$current_avatar^?$total_avatars^?$my_threads";
}    

function GetState($user_id) {
	$row = perform_query("SELECT state FROM user WHERE user_id='$user_id'",SELECT);
	return $row->state;
}

function NewUser($newuser,$newpassword) {
	global $settings;
	global $userid;

    $row = perform_query("select ip_address from ban where ip_address = '".$_SERVER['REMOTE_ADDR']."'",SELECT);
	if ($row) {
		return "-1^?".intext("New accounts not allowed");
	}
	
	if ($_COOKIE['sessioncookie']) {
		$row = perform_query("select cookie from ban where cookie = '".$_COOKIE['sessioncookie']."'",SELECT);
		if ($row) {
			return "-1^?".intext("New accounts not allowed");
		}
	} 

	if ($settings->new_accounts_allowed == 0) {
		return "-1^?".intext("New accounts not allowed");
	}

	if ($settings->connect_with_username == 0){            
		return "-1^?".intext("Feature disabled");
	}
   
	if ($info = lockdown_button_check(SITEDOWN+NONEWACCOUNTS)) {
		$sysinfo = explode("^?",$info);
		if ($sysinfo[1]) {
			return "-1^?".$sysinfo[1];
		} else {
			return "-1^?".intext("Feature disabled");
		}
	}	

   $newuser = trim($newuser);
   
   $temp = strtolower($newuser);
   if (mb_strlen($newuser) > $settings->max_username_length) {
      return "-1^?".intext("Name is too long");
   }
   if (($temp == "newusers") || ($temp == "system")) {
       return "-1^?".intext("Name is reserved");
   }	   
   if ($newuser == "") {
	   return "-1^?".intext("Can't have blank name");
   }		
   if (strpos($newuser, ";")) {
	  return "-1^?".intext("Semicolons are not allowed in user name");
   }
   if (strpos($newuser, ",")) {
	  return "-1^?".intext("Commas are not allowed in user name");
   }   
   if (is_numeric($newuser)) {
	  return "-1^?".intext("User name can't be a number");
   }
   
   $row = perform_query("select * from user where username='$newuser'", SELECT);
   if ($row) { 
	   return "-1^?".intext("User name already taken");
   }

	if ($settings->new_account_limit != -1) {
		$count=0;
		$cur2 = perform_query("select last_ip from user where status!=5 and last_ip='" . $_SERVER['REMOTE_ADDR'] . "'", MULTISELECT); 

		while ($row2 = mysql_fetch_array( $cur2 )) {
			$count++;
		}   
		if ($count >= $settings->new_account_limit) {
			return "-1^?".intext("You've made the maximum number of accounts");
		}
	}
   
   if (($settings->welcome_thread == "") || ($settings->welcome_thread == 0)) {$threads = "";} else {$threads = "," . $settings->welcome_thread . ":0";}
   
   $hashed_password = hash('sha256', $newpassword);
      
   $newuserquery = "insert user "
		. "\n set "
		. "\n  username='$newuser',"
		. "\n  password='$hashed_password',"
		. "\n  my_threads='$threads',"    		
		. "\n  join_date=now(),"
		. "\n  prune_time=now(),"		
		. "\n  settings=0,"		
		. "\n  first_ip='" . $_SERVER['REMOTE_ADDR'] . "'";
   
	$id = perform_query($newuserquery,INSERT); 
    $userid = $id;
	
	if (($settings->new_user_avatar == 2) && getimagesize($settings->website_url."/identicon.php?size=80&hash=123456")) {
		$internal_id = md5(mt_rand());   
	
		$q = "insert file "
				. "\n set "		
				. "\n  author_id='" . $id . "',"
				. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
				. "\n  filename='identicon.png',"		
				. "\n  mime_type='image/png',"
				. "\n  file_type='1',"   						
				. "\n  avatar_number='1',"   							
				. "\n  internal_id='$internal_id';";   							
		perform_query($q,INSERT);       	
	
		AddAvatar($id);
		UpdateAvatar(1);

		copy($settings->website_url."/identicon.php?size=".$settings->max_avatar_dimensions[0]."&hash=". md5(mt_rand()), getcwd() . "/files/avatar_".$id."_1_".$internal_id);
	}	
	
	LogEvent(2,intext("New member").": %%u".$id.":".$newuser.";");
	
	return "1^?new user created";
}

function AutoLogin() {
	$sessioncookie = $_COOKIE['sessioncookie'];
	$sess=md5( $sessioncookie . $_SERVER['REMOTE_ADDR'] );
	
	if ($sess == "0") {return "-1";}
		
	$row = perform_query("select * from session where session='$sess'",SELECT);
	
	if ($row) {	
		perform_query("update session set last_activity=now() where session='$sess'",UPDATE);
		perform_query("update user set last_ip='" . $_SERVER['REMOTE_ADDR'] . "', logins = logins + 1 where user_id='".$row->user_id."'",UPDATE); 

		housecleaning();
		
		return "1^?Auto-login worked^?" . $row->user_id;
	} else { 
		return "-1^?";
	}
}

function Login($user,$pass,$rem){
	global $settings;
	global $userid; 
	global $session_id;
	global $sessioncookie;
	   
	if (!$user || !$pass) {            
	   return "-1^?".intext("User or password not given");
	}
	if ($settings->connect_with_username == 0){            
	   return "-1^?".intext("Feature disabled");
	}
	
	if ($settings->connect_with_fb) {
		if (is_numeric($user)){
		$user_id = $user;
		
		$first_query = "SELECT * "
			. "\nFROM user "
			. "\nWHERE user_id='$user_id'"; 
	   } else {
		$first_query = "SELECT * "
			. "\nFROM user "
			. "\nWHERE username='$user'"; 
	    }
	    $row = perform_query($first_query,SELECT);
		
		if ($row->facebook_id) {
		    return "-1^?".intext("You must connect using the Facebook button");
	    }
		if ($row->linkedin_id) {
		    return "-1^?".intext("You must connect using the LinkedIn button");
	    }		
	}
	
	$hashed_password = hash('sha256', $pass);
	
	if (!$hashed_password) {
	   return "-1^?".intext("Bad user name or password");
	}
	if (is_numeric($user)){
		$user_id = $user;
		
		$first_query = "SELECT * "
			. "\nFROM user "
			. "\nWHERE user_id='$user_id' AND password='$hashed_password' AND status > -1"; 
	} else {
		$first_query = "SELECT * "
			. "\nFROM user "
			. "\nWHERE username='$user' AND password='$hashed_password' AND status > -1"; 
	}
	$row = perform_query($first_query,SELECT);
	if (!$row) {
		return "-1^?".intext("Bad user name or password");
	}

	if (($info = lockdown_button_check(SITEDOWN)) && $row->status != 5) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      return "-1^?".$sysinfo[1];
	   } else {
	      return "-1^?".intext("Feature disabled");
	   }
	}	

	if ($row->is_banned == 2) {
		$msg = IsBanned($row->user_id); 
		if ($msg) {return "-1^?".$msg;}
	}

	$userid=$row->user_id; 
	$user_id = $row->user_id;
	$logins = $row->logins;
		
	if ($rem == "true") {
	   $lifetime = time() + 365*24*60*60;
	} else {
	   $lifetime = 0; 
	}
			
	perform_query("delete from session where user_id='" . $row->user_id . "'",DELETE);
		
	set_session(); 
				   	
	setcookie("sessioncookie", $sessioncookie, $lifetime); 
	
    perform_query("insert into session set last_activity=now(), session='$session_id', cookie='$sessioncookie'", INSERT);
 
	perform_query( "update user "
		. "\n set "
		. "\n last_ip='" . $_SERVER['REMOTE_ADDR'] . "', logins = logins + 1 where user_id='".$row->user_id."'",UPDATE); 
					
	perform_query("update session "
		. "\n set "
		. "\n  user_id='$user_id',"
		. "\n  sess_start=now(),"
		. "\n  last_activity=now(),"
		. "\n  ip='".$_SERVER['REMOTE_ADDR']."',"
		. "\n  user_agent='".$_SERVER['HTTP_USER_AGENT']
		. "' where  session='$session_id'",UPDATE); 
	
	housecleaning();
	
	return "1^?Login successful";
}

function Logout(){       
	global $session_id;

	perform_query("update session set session='0', last_activity=now() where session='" . $session_id . "'",UPDATE); 
		
	return "1";
}

//this sets two global variables: sessioncookie is a number string given to the user as a cookie, 
//and session_id is a hash containing sessioncookie and the user's ip address, stored in the server database
function set_session() {
	global $session_id;  
	global $sessioncookie;

	$randnum = md5(mt_rand());
	$sessioncookie = $randnum;
	$session_id = md5( $randnum . $_SERVER['REMOTE_ADDR'] );
} 

function remote_image_thumbnail($url, $max_width, $max_height) {
   $info = getimagesize($url);
	
   $width = isset($info['width']) ? $info['width'] : $info[0];
   $height = isset($info['height']) ? $info['height'] : $info[1];
     
   $wRatio = $max_width / $width;
   $hRatio = $max_height / $height;	  
	  
   if ( ($width <= $max_width) && ($height <= $max_height) ) {
      $return_array[0] = 0;
      $return_array[1] = 0;
   } elseif ( ($wRatio * $height) < $max_height ) { //?
      // Image is horizontal
      $tHeight = ceil($wRatio * $height);
      $tWidth = $max_width;
	  
	  $return_array[0]=$tWidth;
	  $return_array[1]=$tHeight;	  
   } else {
      // Image is vertical
      $tWidth = ceil($hRatio * $width);
      $tHeight = $max_height;
	  
	  $return_array[0]=$tWidth;
	  $return_array[1]=$tHeight;
   }	  
   return $return_array;	  
}

function thumbnail($inputFileName, $max_width, $max_height, $is_avatar) {
   global $settings;
   
   $info = getimagesize($inputFileName);
   $type = isset($info['type']) ? $info['type'] : $info[2];

   if ( !(imagetypes() & $type) ) {
      return 0;
   }

   $width = isset($info['width']) ? $info['width'] : $info[0];
   $height = isset($info['height']) ? $info['height'] : $info[1];

   $wRatio = $max_width / $width;
   $hRatio = $max_height / $height;

   $sourceImage = imagecreatefromstring(file_get_contents($inputFileName));

   if ( ($width <= $max_width) && ($height <= $max_height) ) {
	  if ($settings->animated_avatars == 0 && $is_avatar) {
	     //Force a resize to get rid of animated gifs
	     $tHeight = $height;
         $tWidth = $width;
	  } else {
         return 0;
	  }
   } elseif ( ($wRatio * $height) < $max_height ) { //?
      // Image is horizontal
      $tHeight = ceil($wRatio * $height);
      $tWidth = $max_width;
   } else {
      // Image is vertical
      $tWidth = ceil($hRatio * $width);
      $tHeight = $max_height;
   }

   $thumb = imagecreatetruecolor($tWidth, $tHeight);

   if ( $sourceImage === false ) {
      // Could not load image
      return 0;
   }

   // Copy resampled makes a smooth thumbnail
   imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $tWidth, $tHeight, $width, $height);
   imagedestroy($sourceImage);

   return $thumb;
}

function imageToFile($mime_type, $im, $fileName, $quality = 90) {	
	if ((preg_match('#image/gif#',$mime_type)))  {  
	   imagegif($im, $fileName);
	} else if ((preg_match('#image/jpeg#',$mime_type)))  {  
	   imagejpeg($im, $fileName, $quality);
	} else if ((preg_match('#image/png#',$mime_type)))  {  
	   imagepng($im, $fileName);
	} else if ((preg_match('#image/x-ms-bmp#',$mime_type)))  {  
	   imagewbmp($im, $fileName);
	} else {
	   return false;
	}
	return true;
}

function mime_check($filename,$datafile) {
    global $settings;

    $mime_types = array("323" => "text/h323",
    "acx" => "application/internet-property-stream",
    "ai" => "application/postscript",
    "aif" => "audio/x-aiff",
    "aifc" => "audio/x-aiff",
    "aiff" => "audio/x-aiff",
    "asf" => "video/x-ms-asf",
    "asr" => "video/x-ms-asf",
    "asx" => "video/x-ms-asf",
    "au" => "audio/basic",
    "avi" => "video/x-msvideo",
    "axs" => "application/olescript",
    "bas" => "text/plain",
    "bcpio" => "application/x-bcpio",
    "bin" => "application/octet-stream",
    "bmp" => "image/x-ms-bmp",
    "c" => "text/plain",
    "cat" => "application/vnd.ms-pkiseccat",
    "cdf" => "application/x-cdf",
    "cer" => "application/x-x509-ca-cert",
    "class" => "application/octet-stream",
    "clp" => "application/x-msclip",
    "cmx" => "image/x-cmx",
    "cod" => "image/cis-cod",
    "cpio" => "application/x-cpio",
    "crd" => "application/x-mscardfile",
    "crl" => "application/pkix-crl",
    "crt" => "application/x-x509-ca-cert",
    "csh" => "application/x-csh",
    "css" => "text/css",
    "dcr" => "application/x-director",
    "der" => "application/x-x509-ca-cert",
    "dir" => "application/x-director",
    "dll" => "application/x-msdownload",
    "dms" => "application/octet-stream",
    "doc" => "application/msword",
    "dot" => "application/msword",
    "dvi" => "application/x-dvi",
    "dxr" => "application/x-director",
    "eps" => "application/postscript",
    "etx" => "text/x-setext",
    "evy" => "application/envoy",
    "exe" => "application/octet-stream",
    "fif" => "application/fractals",
    "flr" => "x-world/x-vrml",
    "gif" => "image/gif",
    "gtar" => "application/x-gtar",
    "gz" => "application/x-gzip",
    "h" => "text/plain",
    "hdf" => "application/x-hdf",
    "hlp" => "application/winhlp",
    "hqx" => "application/mac-binhex40",
    "hta" => "application/hta",
    "htc" => "text/x-component",
    "htm" => "text/html",
    "html" => "text/html",
    "htt" => "text/webviewhtml",
    "ico" => "image/x-icon",
    "ief" => "image/ief",
    "iii" => "application/x-iphone",
    "ins" => "application/x-internet-signup",
    "isp" => "application/x-internet-signup",
    "jfif" => "image/pipeg",
    "jpe" => "image/jpeg",
    "jpeg" => "image/jpeg",
    "jpg" => "image/jpeg",
    "js" => "application/x-javascript",
    "latex" => "application/x-latex",
    "lha" => "application/octet-stream",
    "lsf" => "video/x-la-asf",
    "lsx" => "video/x-la-asf",
    "lzh" => "application/octet-stream",
    "m13" => "application/x-msmediaview",
    "m14" => "application/x-msmediaview",
    "m3u" => "audio/x-mpegurl",
    "man" => "application/x-troff-man",
    "mdb" => "application/x-msaccess",
    "me" => "application/x-troff-me",
    "mht" => "message/rfc822",
    "mhtml" => "message/rfc822",
    "mid" => "audio/mid",
    "mny" => "application/x-msmoney",
    "mov" => "video/quicktime",
    "movie" => "video/x-sgi-movie",
    "mp2" => "video/mpeg",
    "mp3" => "audio/mpeg",
    "mpa" => "video/mpeg",
    "mpe" => "video/mpeg",
    "mpeg" => "video/mpeg",
    "mpg" => "video/mpeg",
    "mpp" => "application/vnd.ms-project",
    "mpv2" => "video/mpeg",
    "ms" => "application/x-troff-ms",
    "mvb" => "application/x-msmediaview",
    "nws" => "message/rfc822",
    "oda" => "application/oda",
    "p10" => "application/pkcs10",
    "p12" => "application/x-pkcs12",
    "p7b" => "application/x-pkcs7-certificates",
    "p7c" => "application/x-pkcs7-mime",
    "p7m" => "application/x-pkcs7-mime",
    "p7r" => "application/x-pkcs7-certreqresp",
    "p7s" => "application/x-pkcs7-signature",
    "pbm" => "image/x-portable-bitmap",
    "pdf" => "application/pdf",
    "pfx" => "application/x-pkcs12",
    "pgm" => "image/x-portable-graymap",
    "pko" => "application/ynd.ms-pkipko",
    "pma" => "application/x-perfmon",
    "pmc" => "application/x-perfmon",
    "pml" => "application/x-perfmon",
    "pmr" => "application/x-perfmon",
    "pmw" => "application/x-perfmon",
	"png" => "image/png",
    "pnm" => "image/x-portable-anymap",
    "pot" => "application/vnd.ms-powerpoint",
    "ppm" => "image/x-portable-pixmap",
    "pps" => "application/vnd.ms-powerpoint",
    "ppt" => "application/vnd.ms-powerpoint",
    "prf" => "application/pics-rules",
    "ps" => "application/postscript",
    "pub" => "application/x-mspublisher",
    "qt" => "video/quicktime",
    "ra" => "audio/x-pn-realaudio",
    "ram" => "audio/x-pn-realaudio",
    "ras" => "image/x-cmu-raster",
    "rgb" => "image/x-rgb",
    "rmi" => "audio/mid",
    "roff" => "application/x-troff",
    "rtf" => "application/rtf",
    "rtx" => "text/richtext",
    "scd" => "application/x-msschedule",
    "sct" => "text/scriptlet",
    "setpay" => "application/set-payment-initiation",
    "setreg" => "application/set-registration-initiation",
    "sh" => "application/x-sh",
    "shar" => "application/x-shar",
    "sit" => "application/x-stuffit",
    "snd" => "audio/basic",
    "spc" => "application/x-pkcs7-certificates",
    "spl" => "application/futuresplash",
    "src" => "application/x-wais-source",
    "sst" => "application/vnd.ms-pkicertstore",
    "stl" => "application/vnd.ms-pkistl",
    "stm" => "text/html",
    "svg" => "image/svg+xml",
    "sv4cpio" => "application/x-sv4cpio",
    "sv4crc" => "application/x-sv4crc",
    "t" => "application/x-troff",
    "tar" => "application/x-tar",
    "tcl" => "application/x-tcl",
    "tex" => "application/x-tex",
    "texi" => "application/x-texinfo",
    "texinfo" => "application/x-texinfo",
    "tgz" => "application/x-compressed",
    "tif" => "image/tiff",
    "tiff" => "image/tiff",
    "tr" => "application/x-troff",
    "trm" => "application/x-msterminal",
    "tsv" => "text/tab-separated-values",
    "txt" => "text/plain",
    "uls" => "text/iuls",
    "ustar" => "application/x-ustar",
    "vcf" => "text/x-vcard",
    "vrml" => "x-world/x-vrml",
    "wav" => "audio/x-wav",
    "wcm" => "application/vnd.ms-works",
    "wdb" => "application/vnd.ms-works",
    "wks" => "application/vnd.ms-works",
    "wmf" => "application/x-msmetafile",
    "wps" => "application/vnd.ms-works",
    "wri" => "application/x-mswrite",
    "wrl" => "x-world/x-vrml",
    "wrz" => "x-world/x-vrml",
    "xaf" => "x-world/x-vrml",
    "xbm" => "image/x-xbitmap",
    "xla" => "application/vnd.ms-excel",
    "xlc" => "application/vnd.ms-excel",
    "xlm" => "application/vnd.ms-excel",
    "xls" => "application/vnd.ms-excel",
    "xlt" => "application/vnd.ms-excel",
    "xlw" => "application/vnd.ms-excel",
    "xof" => "x-world/x-vrml",
    "xpm" => "image/x-xpixmap",
    "xwd" => "image/x-xwindowdump",
    "z" => "application/x-compress",
    "zip" => "application/zip");		
		
 	$p = pathinfo($filename);
	$ext = strtolower($p['extension']);
	
	if (in_array($ext,$settings->allowed_file_types)) {
	    //try the best 
	    if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $datafile);
            finfo_close($finfo);
			if (array_key_exists($ext, $mime_types)) {
			   $ext_mime_type = $mime_types[$ext];
			   if ($mimetype != $ext_mime_type) {
			      $mimetype = -1;
			   }
			}
		//try the second best	
        } else if(function_exists('mime_content_type')) {
		    $mimetype = mime_content_type($datafile);
			if (array_key_exists($ext, $mime_types)) {
			   $ext_mime_type = $mime_types[$ext];
			   if ($mimetype != $ext_mime_type) {
			      $mimetype = -1;
			   }
			}
		//rely on the file extension  
		} else if (array_key_exists($ext, $mime_types)) {
			$mimetype = $mime_types[$ext];
		} else {
		    $mimetype = 'application/octet-stream';
		}

		//if it's an image, validate it with getimagesize() 
		if (strpos($mimetype, 'image') !== false) {
			$imageinfo = getimagesize($datafile);
			if (!$imageinfo) {
                $mimetype = -1;
			}
			if($imageinfo['mime'] != $mimetype) {
                $mimetype = -1;
			}
        }		
	} else {
        $mimetype = -1;
    }	  
	return $mimetype;
}
?>