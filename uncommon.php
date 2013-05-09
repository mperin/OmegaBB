<?php
/*OmegaBB*/

//a collection of miscellaneous functions moved into their own source file to reduce memory usage
 
function GetThreadPage($thread_id,$offset,$bot=0) {
	global $userid;
	global $settings;

	if (lockdown_button_check(MUSTLOGIN) && (Check_Auth() <= 0)) {return "-1^?".intext("You must sign in to see the forums");}
	if ($settings->must_login_to_see_forum && (Check_Auth() <= 0)) { return "-1^?".intext("You must sign in to see the forums");}

	$thread_id = chop($thread_id);
	$ret_value = "";
	$total = 0;
	$count = 0;
	$thread_array = array();
	$extra = 0; //this stores the number of revisions an article has, and who made the last revision, if the thread is an article.  
	
	$row = perform_query("SELECT * FROM thread WHERE thread_id = $thread_id",SELECT);
	
	$forum_id = $row->forum_id; 
	$author_id = $row->author_id; 
	$total_posts = $row->num_posts;
	$thread_title = $row->title; 
	$highest_post = $row->last_post_id_num;
	$block_allow_list = $row->block_allow_list;
	$type = $row->type;
	$state = $row->state;
	
	if ($state == 2) {
		return ($thread_id . "^? ^? ^? ^? ^? ^?$total_posts^?".$thread_title."^? ^? ^?2"); //thread has been deleted
	}
	if (!IsValidThread($thread_id)) {
		return ($thread_id . "^? ^? ^? ^? ^? ^?9999^?".$thread_title."^? ^? ^?5"); 
	}
	
	if ($type == 0) { $block_allow_list = "";}
	
	//if type is not 0, it's a private thread, CheckAuth and confirm they're on the block_allow_list
	//if they fail to be on the list, then send a system message
	if ($type != 0) { 		
	   $allowed = 0;
	   $user_id = Check_Auth();
	   if (($author_id == $user_id) && ($type == 3) ) {
	      $type = 4;
	   }
	   if (preg_match('/,' . $user_id . ';[^$,]+/', $block_allow_list)) { 
		  $allowed = 1; 
	   }
	   if ($allowed == 0) {
		  $row = perform_query("select my_private_threads from user where user_id='". $user_id . "'",SELECT); 
		  if (preg_match('/,-' . $thread_id . '($|,)/', $row->my_private_threads)) { 
			 return ($thread_id . "^? ^?-2^? ^? ^? ^?$total_posts^?".$thread_title."^? ^? ^? "); //not a member but may re-join
		  } else {
			 return ($thread_id . "^? ^?-1^? ^? ^? ^?$total_posts"); //not a member and may not join
		  }
	   }
	}
	
	if ( ($offset == 999) || ($offset == "last") ) {
	   $offset = -1;
	   for ($counter = $total_posts;$counter > 0;$counter -= $settings->posts_per_page) {
		  $offset++;
	   }
	   $total_pages = $offset + 1;
	} else {
	   $total_pages = 0;
	   for ($counter = $total_posts;$counter > 0;$counter -= $settings->posts_per_page) {
		  $total_pages++;
	   }           	
	}
   
   $top_limit = ($offset * $settings->posts_per_page) + $settings->posts_per_page;
   $bottom_limit = ($offset * $settings->posts_per_page);
   $first_query = "SELECT * FROM post WHERE thread_id = $thread_id AND needs_approval = 0 AND reply_num > $bottom_limit AND reply_num <= $top_limit ORDER BY reply_num DESC ";
	   
   $cur = perform_query($first_query, MULTISELECT);
   
   if ($bot) {
      $d = "<br>\n";
   } else {
      $d = "^?";
   }
   
   Check_Auth();  //here to cause userid to get set
   
   $post_count = 0;
   $last_reply_num = 0;
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

	   //if it's a wiki type, check to see if this user has edit access
	   if (($forum_id == 13) && ($row["reply_num"] == 1) && ($row["type"] > 0)) {
		   $user_status = GetStatus($userid);
		   
		   $can_edit = 0;
	       switch ($row["type"]) {
			  case 1: //author only
                  if (($userid) && ($userid == $row["author_id"])) {
				     $can_edit = 1;
			      }
				  break;
			  case 2: //star members and moderators
                  if (($userid) && ($user_status > 1)) {
				     $can_edit = 1;
			      }
				  break;
			  case 3: //regular users
                  if (($userid) && ($user_status > 0)) {
				     $can_edit = 1;
			      }
				  break;
			  case 4: //all users
                  if ($userid) {
				     $can_edit = 1;
			      }
				  break;			
		   }
		   
		   //author of wiki may always edit
		   if (($userid) && ($userid == $author_id)) {
				$can_edit = 1;
		   }

           //see how many revision this wiki has and who the latest author is
		   $op_query = "select * from post where thread_id = $thread_id and revision = 0 and ((reply_num = 1) or (reply_num = -1))";
		   $row2 = perform_query($op_query,SELECT);    	  
			  
           //total revisions, last edit user id, OP user id of revision 0, wiki type
		   $extra = $row["type"] . "," . $row["revision"] . "," . $row["author_id"] . ";" . $row["author_name"] . "," . $row2->author_id . ";" . $row2->author_name ;
	   }

	   $dtime = new DateTime($row["tstamp"]);
	   $dtime->setTimeZone(new DateTimeZone($settings->time_zone));	   
	   $timestamp = $dtime->format($settings->datetime_format);  
	   $timestamp=preg_replace("/ /","<br>",$timestamp,1);   
	   
	   if ($bot) { 
	       $timestamp=preg_replace("/<br>.*/","",$timestamp,1);
           $thread_array[$count++] = $d . "<hr>" . $d . $timestamp . " -- " . $row["message"] . $d . "by " . $row["author_name"];
	   } else {
	       $thread_array[$count++] = $d . strtoupper($row["author_id"]) . $d . $row["author_name"] . $d . $row["avatar_id"] . $d . $row["message"] . $d . $timestamp . $d . $can_edit . $d . $row["message_id"];
	   }

	   $post_count++;
	   if ($last_reply_num == 0){
		  $last_reply_num = $row["reply_num"];
	   }
   }
   mysql_free_result( $cur );
	 
   if ($bot) { 
      $ret_value .= $d;
   } else {	   
      $ret_value .= $thread_id . $d . $forum_id . $d . $type . $d . $block_allow_list . $d . $highest_post . $d . $post_count . $d . $total_posts . $d . $thread_title . $d . $total_pages . $d . $offset . $d . $state . $d . $extra;
   }

   for ($i = $count; $i > 0; $i--) {
	  $ret_value .= $thread_array[$i-1]; 
   }
   
   if ($bot) {
      $ret_value .= "<br><hr>";
	  if ($offset > 0) {
	     $ret_value .= "<br><a href='thread.php?id=" . $thread_id . "&page=" . ($offset) . "'>Previous Page</a>";
	  }
      if  ($offset < ($total_pages - 1)) {
         $ret_value .= "<br><a href='thread.php?id=" . $thread_id . "&page=" . ($offset + 2) . "'>Next Page</a>";
      }
      $ret_value .= "<br>\n<a href='fullindex.php'>Index</a><br>";
   }

   return ($ret_value);
} 	
	
function GetThreadTitle($thread_id) {
    global $settings;
	
	if (lockdown_button_check(MUSTLOGIN) && (Check_Auth() <= 0)) {return;}
	if ($settings->must_login_to_see_forum && (Check_Auth() <= 0)) {return;}
	
	if ($info = lockdown_button_check(FORUMDOWN+SITEDOWN)) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      return "-1^?".$sysinfo[1];
	   } else {
	      return "-1^?".intext("Feature disabled");
	   }
	}
	
	$row = perform_query("select title from thread where thread_id='$thread_id'",SELECT); 
    return $row->title;
}	

function GetForums() {
    global $settings; 
	
	if (lockdown_button_check(MUSTLOGIN) && (Check_Auth() <= 0)) {return "-1^?".intext("You must sign in to see the forums");}
	if ($settings->must_login_to_see_forum && (Check_Auth() <= 0)) {return "-1^?".intext("You must sign in to see the forums");}
	if (!$settings->enable_forums && !$settings->enable_articles) {return "-1^?".intext("Feature disabled");}	
	
	if ($info = lockdown_button_check(FORUMDOWN+SITEDOWN)) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      return "-1^?".$sysinfo[1];
	   } else {
	      return "-1^?".intext("Feature disabled");
	   }
	}

	$ret_value = "";
	
	$forum_array = array(1,2,3,4,5,6,7,8,9,10,11,12,13);
	
	for ($i = 0; $i < sizeof($forum_array); $i++) {
		$thread_count[$i] = 0;
				   
		if ($forum_array[$i] == 13) {
		   $forum_title[$i] = $settings->articles_topic_name;	

        } else {		
		   $forum_title[$i] = $settings->forum_topic_names[$i];
		}
		
		if (!IsValidForum($forum_array[$i])) {
		   $thread_count[$i] = 0;
		   continue;
		}
				   
		if ($forum_array[$i] == 12) {
		   $thread_count[$i] = 0;
		   continue;
		}

		$row_info = "";

		$cur = perform_query("SELECT * FROM thread WHERE forum_id = $forum_array[$i] and (state = 3 || state = 4) ORDER BY thread_id DESC LIMIT 11",MULTISELECT);
		while ($row = mysql_fetch_array( $cur )) {
		   $row_info .=  $row["thread_id"] . "^?" . $row["title"] . "^?" . $row["state"] . "^?";
		   $thread_count[$i]++;
		}		

		$cur = perform_query("SELECT * FROM thread WHERE needs_approval = 0 and forum_id = $forum_array[$i] and state != 2 and state != 3 and state != 4 ORDER BY thread_id DESC LIMIT 11",MULTISELECT);	   
		while ($row = mysql_fetch_array( $cur )) {
		   $row_info .=  $row["thread_id"] . "^?" . $row["title"] . "^?" . $row["state"] . "^?";
		   $thread_count[$i]++;
		   if ($thread_count[$i] > 10) {break;}
		}

		$ret_value .=  $row_info;		
	}
	
	$header_string = sizeof($forum_array) .'^?'; 
	for ($i = 0; $i < sizeof($forum_array); $i++) {
	   $header_string .= $forum_array[$i] . '^?' . $forum_title[$i] .'^?'. $thread_count[$i] .'^?';
	}
	
	return ($header_string . $ret_value);
}

function GetPage($page, $forum_id) {
    global $settings;
	if ($settings->must_login_to_see_forum && (Check_Auth() <= 0)) {return "-1^?".intext("You must sign in to see the forums");}
	if (lockdown_button_check(MUSTLOGIN) && (Check_Auth() <= 0)) {return "-1^?".intext("You must sign in to see the forums");}
	if (!IsValidForum($forum_id)) {return "-1^?".intext("Invalid forum");}
	
	if ($forum_id == 12) {
	   $user_id = Check_Auth();
	   $row = perform_query("select my_private_threads from user where user_id='$user_id'", SELECT);
	   if (!$row) { 
		   $thread_count[11] = 0;
	   } else {
		   $row_info = "";		   
		   $private_threads_array = explode(",",$row->my_private_threads);
		   $start = (count($private_threads_array) - 1) - ($page * 10);
		   for ($j = $start; $j > $start - 11; $j--) {
			  if ($j < 0) {break;}
			  if ($private_threads_array[$j] < 0) { $private_threads_array[$j] *= -1;}  //make it positive if it's negative
			  $row2 = perform_query("select * from thread where thread_id='" . $private_threads_array[$j] . "'", SELECT);
			  $row_info .=  $row2->forum_id . "^?" . $row2->thread_id . "^?" . $row2->title . "^?" . $row2->state . "^?";
			  $count++;
		   }
		   $ret_value .=  $row_info;
	   }
	} else {		   
	    $row = perform_query("SELECT count( * ) as total_stickies FROM thread where forum_id = $forum_id and (state = 3 || state = 4)");
	
		$sql_offset = ($page * 10) - $row->total_stickies;
		$cur = perform_query("select * from thread where needs_approval = 0 and forum_id = $forum_id and state != 2 and state != 3 and state != 4 ORDER BY thread_id DESC LIMIT 11 OFFSET $sql_offset",MULTISELECT);    	   
		   
		while ($row = mysql_fetch_array( $cur )) {
		   $row_info .=  $row["forum_id"] . "^?" . $row["thread_id"] . "^?" . $row["title"] . "^?" . $row["state"] . "^?";
		   $count++;
		}
	}
	return ($forum_id  . "^?" . $page . "^?" . $count . "^?" . $row_info);
}

function GetPT($user_id) {
    global $settings;
	$thread_count = 0;
	
	if (!$settings->enable_private_threads) {
		return "-1^?".intext("Feature disabled");
	}		

	if ($info = lockdown_button_check(PTDOWN+SITEDOWN)) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      return "-1^?".$sysinfo[1];
	   } else {
	      return "-1^?".intext("Feature disabled");
	   }
	}
	
	$forum_title = $settings->pt_topic_name;
	
	$row = perform_query("select my_private_threads from user where user_id='$user_id'", SELECT);
	if (!$row) { 
	   $thread_count = 0;
	} else {
	   $row_info = "";		   
	   $private_threads_array = explode(",",$row->my_private_threads);
	   for ($j = count($private_threads_array) - 1; $j > count($private_threads_array) - 12; $j--) {
		  if ($j < 0) {break;}
		  if ($private_threads_array[$j] < 0) { $private_threads_array[$j] *= -1;}  //make it positive if it's negative
		  $row2 = perform_query("select * from thread where state != 2 and thread_id='" . $private_threads_array[$j] . "'", SELECT);
		  if ($row2) { 
		     $row_info .=  $row2->thread_id . "^?" . $row2->title . "^?" . $row2->state . "^?";
		     $thread_count++;
		  }
	   }
	}
	return "1^?12^?$forum_title^?$thread_count^?$row_info";
}			   

function GetProfile($user_id) {	
    global $settings;	

	if (lockdown_button_check(MUSTLOGIN) && (Check_Auth() <= 0)) {return "-1^?".intext("You must sign in to see the forums");}
	if ($settings->must_login_to_see_forum && (Check_Auth() <= 0)) {return "-1^?".intext("You must sign in to see the forums");}
    if ($settings->must_login_to_see_profile && (Check_Auth() <= 0)) {return "-1^?".intext("Must be signed in to see profiles");}

	if ($info = lockdown_button_check(SITEDOWN)) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      return "-1^?".$sysinfo[1];
	   } else {
	      return "-1^?".intext("Feature disabled");
	   }
	}	
	$row = perform_query("select * from user where user_id='". $user_id . "'",SELECT); 
	if (!$row) {return "-1^?".intext("User not found");}
		
	$username = $row->username;
	$user_id = $row->user_id;
	$num_posts = $row->num_posts;
	$profile_text = $row->profile_text;
	$current_avatar = $row->current_avatar;
	$user_id_hex = $row->user_id;	
	$num_posts = $row->num_posts;
	$last_ip = $row->last_ip;	
	$status = $row->status;
    $xsettings = $row->settings;  
	$fb_link = $row->facebook_link;
	$li_link = $row->linkedin_link;
	$li_id = $row->linkedin_id;
	$is_banned = 0;
	
	$dtime = new DateTime($row->join_date);
	$dtime->setTimeZone(new DateTimeZone($settings->time_zone));
	preg_match('/[^ ]*/',$settings->datetime_format,$matches);
	$join_date = $dtime->format($matches[0]);  

	$row = perform_query("select * from ban where user_id=".$user_id,SELECT);
	if ($row) {
		if ($row->type == "perm_ban") {$is_banned = 2;}
		if ($row->type == "perm_mute") {$is_banned = 1;}
		if ($row->type == "ban") {$is_banned = 2;}
		if ($row->type == "mute") {$is_banned = 1;}
		
		if ($row->type == "ban" || $row->type == "mute") {
			$dtime = new DateTime($row->expires);
			$dtime->setTimeZone(new DateTimeZone($settings->time_zone));
			$ban_expire_time = $dtime->format($settings->datetime_format);  
		}
    }
	
	if (!IsAdmin(Check_Auth())) {
	   $last_ip = "";
    }
	
	$hide_online_status = $xsettings & 1;
	if ((!$hide_online_status)  || (IsAdmin(Check_Auth()))) {
	    $row2 = perform_query("select last_activity, session from session where user_id='". $user_id . "'",SELECT);
        if (!$row2) {
		    $last_online = intext("over six months ago");
		} else {
			$d1 = time() ;
			$d2 = strtotime($row2->last_activity);
			$d2 += 300;
			
			if (($d1 > $d2) || ($row2->session == "0")) {
				$dtime = new DateTime($row2->last_activity);
				$dtime->setTimeZone(new DateTimeZone($settings->time_zone));
				$last_online = $dtime->format($settings->datetime_format);  
			} else {
				$last_online = intext("now");
			}		
		}
	} else {
	   $last_online = intext("private");
	}
	
	if (($fb_link) && (GetStatus(Check_Auth()) < $settings->status_to_see_fb_li_profile)) {
	   $fb_link = "1";
	}
	if (($li_link) && (GetStatus(Check_Auth()) < $settings->status_to_see_fb_li_profile)) {
	   $li_link = "1";
	}
	if (($li_id) && (!$li_link)) {$li_link = "1";} 
	
	$row3 = perform_query("select internal_id from file where author_id='". $user_id . "' and avatar_number=" . $current_avatar, SELECT); 
    if (($current_avatar > 0) && !file_exists("files/avatar_" . $user_id . "_" . $current_avatar . "_" . $row3->internal_id)) {$current_avatar = -1;}
	
	return "1^?$username^?$user_id^?" .strtoupper($user_id_hex) . "^?$num_posts^?$profile_text^?$current_avatar^?$join_date^?$num_posts^?$last_ip^?$status^?$is_banned^?$ban_expire_time^?$last_online^?$fb_link^?$li_link^?";	
}    

function UserQuery($q) {
    $ret_value = "";
    $length = strlen($q);

    $cur = perform_query("SELECT * FROM user WHERE SUBSTRING(username, 1, $length) = \"$q\"",MULTISELECT); 
	while ($row = mysql_fetch_array( $cur )) {
	   if ($row["username"] == "System") {continue;}	   
	   $ret_value .= $row["username"] . ";" . $row["user_id"] . "\n"; 
    }

    return $ret_value;
}

function ShowRevision($thread_id,$revision,$total_revisions) {
   global $settings;
   
   if (lockdown_button_check(MUSTLOGIN) && (Check_Auth() <= 0)) {return "-1^?".intext("You must sign in to see the forums");}
   if ($settings->must_login_to_see_forum && (Check_Auth() <= 0)) {return "-1^?".intext("You must sign in to see the forums");}
   if ($info = lockdown_button_check(FORUMDOWN+SITEDOWN)) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      return "-1^?".$sysinfo[1];
	   } else {
	      return "-1^?".intext("Feature disabled");
	   }
	}
	
   if ($revision == $total_revisions) {
      $row = perform_query("select * from post where thread_id=$thread_id and reply_num=1", SELECT);
   } else {
      $row = perform_query("select * from post where thread_id=$thread_id and reply_num=-1 and revision=$revision", SELECT);   
   }

   $dtime = new DateTime($row->tstamp);
   $dtime->setTimeZone(new DateTimeZone($settings->time_zone));
   $timestamp = $dtime->format($settings->datetime_format);  
   $timestamp=preg_replace("/ /","<br>",$timestamp,1);   

   $output = $row->message . "^?" . $row->author_id . "^?" . $row->author_name . "^?" . $timestamp ."^?" .$row->message_id;
	   
   return "1^?$output";
}

?>