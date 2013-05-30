<?php
/*OmegaBB 0.9.3*/

//moderation functions

header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

include('config.php');
include('common.php');

$action=GetParam($_REQUEST,'action','');
$message_id=GetParam($_REQUEST,'message_id','');
$user_id=GetParam($_REQUEST,'user_id','');	
$page=GetParam($_REQUEST,'page','');
$post_position=GetParam($_REQUEST,'post_position','');
$thread_id=GetParam($_REQUEST,'thread_id','');
$ip_address=GetParam($_REQUEST,'ip_address','');
$forum_id=GetParam($_REQUEST,'forum_id','');
$revision=GetParam($_REQUEST,'revision','');
$new_name=GetParam($_REQUEST,'new_name','');
$time=GetParam($_REQUEST,'time','');
$dupe_check=GetParam($_REQUEST,'dupe_check','');
$wipe_type=GetParam($_REQUEST,'wipe_type','');
$data=GetParam($_REQUEST,'data','');
$file_id=GetParam($_REQUEST,'file_id','');

$auth_ret = Check_Auth();

if($auth_ret <= 0) {
	echo "-1^?".intext("Not signed in");
	return;
}   
if (!IsMod($auth_ret)) {
	echo "-1^?".intext("user not a moderator");
	return;
}   

if ($msg = IsBanned($auth_ret)) {
	echo "-1^?".$msg;
	return;
}  

if ($user_id != "") {
	$status = GetStatus($user_id);		
	if (($status > 3) && !IsAdmin($auth_ret))	{
	   echo "-1^?".intext("Cannot moderate this user");
	   return;
	}
	if ($user_id == 0) {
	   echo "-1^?".intext("Cannot moderate System account");
	   return;
	}
}

if ($action == "sticky_thread") {
   if (!IsAdmin($auth_ret)) {
	  echo "-1^?user not an admin: $auth_ret";
	  return;
   }   
   $return_stuff = StickyThread($thread_id);
}	
if ($action == "unsticky_thread") {
   if (!IsAdmin($auth_ret)) {
	  echo "-1^?user not an admin: $auth_ret";
	  return;
   }   
   $return_stuff = UnStickyThread($thread_id);
}		

if ($action == "move_thread") {
   $return_stuff = MoveThread($thread_id,$forum_id);
}    		

if ($action == "delete_current_avatar") {
	if (IsMod($user_id) && !IsAdmin($auth_ret)) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
   $return_stuff = DeleteCurrentAvatar($user_id);
}    		

if ($action == "make_mod") {
   if (!IsAdmin($auth_ret)) {
	  echo "-1^?".intext("You must be an administrator to do this");
	  return;
   }   
   $return_stuff = GiveModStatus($user_id);
}

if ($action == "revoke_mod") {
   if (!IsAdmin($auth_ret)) {
	  echo "-1^?".intext("You must be an administrator to do this");
	  return;
   }   
   $return_stuff = RevokeModStatus($user_id);
}

if ($action == "make_editor") {
   if (!IsAdmin($auth_ret)) {
	  echo "-1^?".intext("You must be an administrator to do this");
	  return;
   }   	
   $return_stuff = GiveEditorStatus($user_id);
}

if ($action == "revoke_editor") {
   if (!IsAdmin($auth_ret)) {
	  echo "-1^?".intext("You must be an administrator to do this");
	  return;
   }   
   $return_stuff = RevokeEditorStatus($user_id);
}	

if ($action == "undelete_thread") {
	if ($settings->may_undelete == 0){  
	   echo "-1^?".intext("Feature disabled");
	   return;
	}
   $return_stuff = UnDeleteThread($thread_id);
}    		

if ($action == "delete_thread") {
   if (GetStatus(Check_Auth()) >= $settings->status_to_hard_delete) {
	  $return_stuff = HardDeleteThread($thread_id);
   } else {
	  $return_stuff = DeleteThread($thread_id);
   }
}    		   

if ($action == "open_thread") {
   $return_stuff = OpenThread($thread_id);
}    	

if ($action == "close_thread") {
   $return_stuff = CloseThread($thread_id);
}    		

if ($action == "prevent_auto_close") {
   $return_stuff = PreventAutoClose($thread_id);
}    	

if ($action == "allow_auto_close") {
   $return_stuff = AllowAutoClose($thread_id);
}    		

if ($action == "delete_wiki_post") {
   if (GetStatus(Check_Auth()) >= $settings->status_to_hard_delete) {
	  $return_stuff = HardDeleteWikiPost($thread_id,$revision);
   } else {
	  $return_stuff = DeleteWikiPost($thread_id,$revision);
   }
}    				

if ($action == "delete_post") {
   if (GetStatus(Check_Auth()) >= $settings->status_to_hard_delete) {
	  $return_stuff = HardDeletePost($message_id);
   } else {
	  $return_stuff = DeletePost($message_id);
   }
}    		

if ($action == "undelete_post") {
   if ($settings->may_undelete == 0){  
	   echo "-1^?".intext("Feature disabled");
	   return;
	}
   $return_stuff = UnDeletePost($message_id);
}    		

if ($action == "raise_status") {
   $return_stuff = RaiseStatus($user_id);
}    	

if ($action == "lower_status") {
   $return_stuff = LowerStatus($user_id);
}    		

if ($action == "deletefile") {
   $return_stuff = DeleteFile($file_id);
}    		

if ($action == "kick_from_pt") { 
	if (IsMod($user_id) && !IsAdmin($auth_ret)) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}				
   $return_stuff = KickFromPT($user_id,$thread_id);
}    		

if ($action == "thread_ban") {
	if (IsMod($user_id) && !IsAdmin($auth_ret)) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}				
   $return_stuff = ThreadBan($user_id,$thread_id);
}    	

if ($action == "thread_unban") {
	if (IsMod($user_id) && !IsAdmin($auth_ret)) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}	
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}				
   $return_stuff = ThreadUnban($user_id,$thread_id);
}    	

if ($action == "wipe") {
	if (!IsAdmin($auth_ret)) {
	   echo "-1^?".intext("You must be an administrator to do this");
	   return;
	}   
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}				
   $return_stuff = WipeAccount($user_id,$wipe_type,$dupe_check);
}    		

if ($action == "ban") {
	if (IsMod($user_id) && !IsAdmin($auth_ret)) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}		
   $return_stuff = Ban($time,$user_id,$thread_id,$dupe_check);
}    	

if ($action == "unban") { 
	if (IsMod($user_id) && !IsAdmin($auth_ret)) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}				
   $return_stuff = Unban($user_id);
}    	

if ($action == "mute") {
	if (IsMod($user_id) && !IsAdmin($auth_ret)) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}		
   $return_stuff = Mute($time,$user_id,$thread_id,$dupe_check);
}    	

if ($action == "unmute") { 
	if (IsMod($user_id) && !IsAdmin($auth_ret)) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}				
   $return_stuff = UnMute($user_id);
}    		

if ($action == "delete_avatar") {
	if (IsMod($user_id) && !IsAdmin($auth_ret) ) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}				
   $return_stuff = DeleteAvatar($user_id,$message_id);
}    	

if ($action == "unbanwiped") {
	if (!IsAdmin($auth_ret)) {
	   echo "-1^?".intext("You must be an administrator to do this");
	   return;
	}   
   $return_stuff = UnbanWiped($data);
}    		

if ($action == "change_username") {
	if (IsMod($user_id) && !IsAdmin($auth_ret) ) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}				
   $return_stuff = ChangeUsername($new_name,$user_id,$message_id);
}    		

if ($action == "change_username2") {
	if (IsMod($user_id) && !IsAdmin($auth_ret) ) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
	if (IsAdmin($user_id)) {
	   echo "-1^?".intext("Cannot perform this action on an administrator.");
	   return;
	}				
   $return_stuff = ChangeUsername2($new_name,$user_id);
}

$temp_array = explode("^?",$return_stuff);
if (intval($temp_array[0]) > 0) {
   LogEvent(1,$temp_array[1]);
}

echo $return_stuff;
return;

function UnDeletePost($message_id) {
    $row = perform_query("select * from post where message_id =". ($message_id * -1),SELECT);
    if (!$row) {return "-1^?".intext("Unable to undelete.  Deleted post no longer in database");}
	$row2 = perform_query("select title from thread where thread_id=". ($row->thread_id * -1),SELECT);
	
	perform_query("update post "
    	. "\n set "
		. "\n state='0', "
		. "\n message='" . mysql_real_escape_string($row->message) . "'"	
        . " where message_id='". $message_id ."'",UPDATE); 	
	
	perform_query("DELETE FROM post WHERE message_id=". ($message_id * -1),DELETE); 

    return "1^?".intext("Undeleted post in") . " %%t" . ($row->thread_id * -1) . ":" . $row2->title . ";";
}

function DeletePost($message_id) {	
	$row2 = perform_query("select thread_id from post where message_id=".$message_id,SELECT);
	$row3 = perform_query("select title from thread where thread_id=".$row2->thread_id,SELECT);

    //copy row to (message_id * -1)	
	perform_query("insert into post(message, author_id, message_id, thread_id, ip_address, reply_num, avatar_id, author_name ) " 
        . "select message, author_id, (message_id * -1), (thread_id * -1), ip_address, reply_num, avatar_id, author_name from post where message_id='". $message_id. "'",INSERT); 	
	
	$cur = perform_query("select * from file where post_id=" . $message_id, MULTISELECT);
	while ($row = mysql_fetch_array( $cur )) {
	   if ($row[file_type] == 1) {
		   unlink("files/t_" . $row[internal_id]);  
		   unlink("files/" . $row[internal_id]);
	   } else {
	       unlink("files/". $row[internal_id]);  
	   }
    }
    perform_query("update file set is_deleted=1 where post_id=".$message_id,UPDATE); 

	perform_query("update post "
    	. "\n set "
		. "\n state='1', "
		. "\n message='<p class=\"system\">".intext("Post Deleted")."</p>'"	
        . " where message_id='". $message_id ."'",UPDATE); 	
	
    return "1^?".intext("Deleted post in") . " %%t" . $row2->thread_id . ":" . $row3->title . ";";
}

function HardDeletePost($message_id) {	
	$row2 = perform_query("select thread_id from post where message_id=".$message_id,SELECT);
	$row3 = perform_query("select title from thread where thread_id=".$row2->thread_id,SELECT);

	$cur = perform_query("select * from file where post_id=" . $message_id, MULTISELECT);
	while ($row = mysql_fetch_array( $cur )) {
	   if ($row[file_type] == 1) {
		   unlink("files/t_" . $row[internal_id]);  
		   unlink("files/". $row[internal_id]);
	   } else {
	       unlink("files/" . $row[internal_id]);  
	   }
    }
    perform_query("update file set is_deleted=1 where post_id=".$message_id,UPDATE); 

	$ret_value = perform_query("update post "
    	. "\n set "
		. "\n state='1', "
		. "\n message='<p class=\"system\">".intext("Post Deleted")."</p>'"	
        . " where message_id='". $message_id ."'",UPDATE); 	
	
    return "1^?".intext("Deleted post in") . " %%t" . $row2->thread_id . ":" . $row3->title . ";";
}

function DeleteAvatar($user_id,$message_id) {
   global $settings;
   
   $row = perform_query("select avatar_id, author_id from post where message_id='". $message_id . "'",SELECT);   
   $row2 = perform_query("select internal_id from file where author_id='". $user_id . "' and avatar_number=" . $row->avatar_id, SELECT); 
   $row3 = perform_query("select username from user where user_id=".$user_id,SELECT);
   
   $target = "files/avatar_" . $row->author_id . "_" . $row->avatar_id . "_" . $row2->internal_id;
   unlink($target);  
   
   perform_query("update file set is_deleted=1 where author_id='". $user_id . "' and avatar_number=" . $row->avatar_id, UPDATE); 
   perform_query("update post set avatar_id='-1' where author_id='" . $user_id . "' and avatar_id='" . $row->avatar_id . "'", UPDATE); 	
   
   return "1^?".intext("Deleted avatar belonging to") . " %%u" . $user_id . ":" . $row3->username . ";";
}

//moderator changing a username from a post
function ChangeUsername($new_name,$user_id,$message_id) {
   global $settings;
   
   $new_name = trim($new_name);
   
   $temp = strtolower($new_name);
   if (mb_strlen($new_name) > $settings->max_username_length) {
      return "-1^?".intext("Name is too long");
   }
   if (($temp == "newusers") || ($temp == "allusers") || ($temp == "system")) {
        return "-1^?".intext("Name is reserved");
   }		
   if ($new_name == "") {
        return "-1^?".intext("Can't have blank name");
   }		
   if (is_numeric($new_name)) {
       return "-1^?".intext("User name can't be a number");
   }
   if (strpos($new_name, ";")) {
       return "-1^?".intext("Semicolons are not allowed in user name");
   }   
   if (strpos($new_name, ",")) {
	  return "-1^?".intext("Commas are not allowed in user name");
   }   
	//check availability
   	$row = perform_query("select username from user where username='".$new_name."'",SELECT); 
	if ($row) {
	   return "-1^?".intext("Username is already in use")."^?";
    }

    //get old username
	$row = perform_query("select author_name from post where message_id=".$message_id,SELECT); 
	
	perform_query("update post set author_name='" . $new_name . "' where author_name='" . mysql_real_escape_string($row->author_name) . "'",UPDATE);

	//if this is also the user's current username, change it to new_user and update their name in all private threads they're a member of
	$is_current = false;
   	$row2 = perform_query("select username from user where user_id='".$user_id."'",SELECT); 
	if ($row2->username == $row->author_name) {
	    $is_current = true;
	    perform_query("update user set username='".$new_name."' where user_id='".$user_id."'",UPDATE); 
	   
		$row5 = perform_query("select * from user where user_id='$user_id'", SELECT);
		$forum_array = explode(",",$row5->my_private_threads);

		foreach ($forum_array as $f) {
		   if ($f == "") {continue;}
		   if (preg_match('/-[0-9]+/',$f)) { continue; }
		   $row6 = perform_query("select block_allow_list from thread where thread_id='$f'", SELECT);
		  
		   $new_block_allow_list = preg_replace('/,' . $user_id . ';[^$,]+/', ',' . $user_id . ";" . $new_name, mysql_real_escape_string($row6->block_allow_list));
		   perform_query("update thread "
			  . "\n set "
			  . "\n  block_allow_list='" . $new_block_allow_list . "'"	
			  . " where thread_id='$f'",UPDATE);    
		}	
	}
	if ($is_current) {
	   return "1^?" . intext("Changed username of") . " %%u" . $user_id . ":" . $row5->username . ";^?";
	} else {
	   if ($row2->username == null) {$row2->username = $user_id;}
	   return "1^?" . intext("Changed old username of") . " %%u" . $user_id . ":" . $row2->username . ";^?";
    }	
}

//moderator changing username from their profile
function ChangeUsername2($new_name,$user_id) {
    global $settings;
	
	$new_name = trim($new_name);

	$temp = strtolower($new_name);
	if (mb_strlen($new_name) > $settings->max_username_length) {
	  return "-1^?".intext("Name is too long");
	}
	if (($temp == "newusers") || ($temp == "allusers") || ($temp == "system")) {
		return "-1^?".intext("Name is reserved");
	}		
	if ($new_name == "") {
		return "-1^?".intext("Can't have blank name");
	}		
	if (is_numeric($new_name)) {
	   return "-1^?".intext("User name can't be a number");
	}
	if (strpos($new_name, ";")) {
	   return "-1^?".intext("Semicolons are not allowed in user name");
	}   	
	if (strpos($new_name, ",")) {
	   return "-1^?".intext("Commas are not allowed in user name");
	}  
   
	//check availability
   	$row = perform_query("select username from user where username='".$new_name."'",SELECT); 
	if ($row) {
	   return "-1^?".intext("Username is already in use")."^?";
    }

	//update username in all private threads they're a member of
    $row = perform_query("select * from user where user_id='$user_id'", SELECT);
    $forum_array = explode(",",$row->my_private_threads);
    foreach ($forum_array as $f) {
       if ($f == "") {continue;}
	   if (preg_match('/-[0-9]+/',$f)) { continue; }
	   $row2 = perform_query("select block_allow_list from thread where thread_id='$f'", SELECT);
	  
	   $new_block_allow_list = preg_replace('/,' . $user_id . ';[^$,]+/', ',' . $user_id . ";" . $new_name, mysql_real_escape_string($row2->block_allow_list));
	   perform_query("update thread "
    	 . "\n set "
		 . "\n  block_allow_list='" . $new_block_allow_list . "'"	
         . " where thread_id='$f'",UPDATE);    
    }		
	
    //update name in their posts
	$row = perform_query("select username from user where user_id=".$user_id,SELECT); 
	perform_query("update post set author_name='" . $new_name . "' where author_name='" . mysql_real_escape_string($row->username) . "'",UPDATE);

    perform_query("update user set username='".$new_name."' where user_id='".$user_id."'",UPDATE); 

	return "1^?" . intext("Changed username of") . " %%u" . $user_id . ":" . $new_name . ";^?";
}

function Unban($user_id) {
	$row = perform_query("select * from user where user_id='". $user_id . "'",SELECT); 
    if (!$row) {return "-1^?".intext("User not found");}
	
	perform_query("delete from ban where user_id='$user_id'",DELETE);

	return "1^?" . intext("Unbanned") . " %%u" . $user_id . ":" . $row->username . ";^?";
}

function UnMute($user_id) {
	$row = perform_query("select * from user where user_id='". $user_id . "'",SELECT); 
    if (!$row) {return "-1^?".intext("User not found");}
	
	perform_query("delete from ban where user_id='$user_id'",DELETE);
		
	return "1^?" . intext("UnMuted") . " %%u" . $user_id . ":" . $row->username . ";^?";
}

function UnbanWiped($ban_id) {
	perform_query("delete from ban where ban_id='$ban_id'",DELETE);
	
    return "1^?" . intext("Unbanned ban id $ban_id");
}

function Ban($time,$user_id,$thread_id,$dupe_check) {
	$additional_bans = 0;
	$extra = "";
	$return_code = 1;
	$fb_li_id = "";
	$expires = "";
	
	if ($dupe_check == 0) {$return_code = 3;}
	
	$row = perform_query("select facebook_id, linkedin_id, last_ip, username from user where user_id='". $user_id . "'",SELECT); 
	if (!$row) {return "-1^?".intext("User not found");}
	
	if ($row->facebook_id) {$fb_li_id = $row->facebook_id;}
	if ($row->linkedin_id) {$fb_li_id = $row->linkedin_id;}
	
    $username = mysql_real_escape_string($row->username);	

	$row2 = perform_query("select cookie from session where user_id='". $user_id . "'",SELECT); 
		
	if ($time == "permanent") {$ban_type = "perm_ban"; $expires="''";} else {$ban_type = "ban"; $expires = " DATE_ADD(now(),INTERVAL $time DAY)";}
	
	perform_query("insert ban "
		. "\n set "
		. "\n type='$ban_type',"
		. "\n user_id='$user_id',"
		. "\n cookie='".$row2->cookie."',"
		. "\n fb_li_id='$fb_li_id',"
		. "\n expires=$expires,"	
		. "\n ip_address='".$row->last_ip."'",INSERT);

	perform_query("update session set session=0 where user_id=$user_id",DELETE);	
	
	if ($dupe_check) {
		//check for other accounts with the same IP and ask the mod if they want them banned as well
		$cur = perform_query("select user_id, username from user where last_ip ='".$row->last_ip."'",MULTISELECT);
		while ($row3 = mysql_fetch_array( $cur )) {
		    if ($row3["user_id"] == $user_id) {continue;}		
			$return_code = 2;
			$additional_bans++;
			$extra .= $row3["user_id"] . "^?" . $row3["username"] . "^?";
		}
	}

	if ($time == "permanent") {
       $message = intext("Banned") . " %%u" . $user_id . ":" . $username . ";"; 
	} else {
	   $message = intext("Banned") . " %%u" . $user_id . ":" . $username . "; " . intext("for") . " $time " . intext("days"); 
	}
    return "$return_code^?$message^?$additional_bans^?$time^?$extra";
}

function Mute($time,$user_id,$thread_id,$dupe_check) {
	$additional_bans = 0;
	$extra = "";
	$return_code = 1;
	$fb_li_id = "";
	$expires = "";
	
	if ($dupe_check == 0) {$return_code = 3;}
	
	$row = perform_query("select facebook_id, linkedin_id, last_ip, username from user where user_id='". $user_id . "'",SELECT); 
	if (!$row) {return "-1^?".intext("User not found");}
	
	if ($row->facebook_id) {$fb_li_id = $row->facebook_id;}
	if ($row->linkedin_id) {$fb_li_id = $row->linkedin_id;}
	
    $username = mysql_real_escape_string($row->username);	

	$row2 = perform_query("select cookie from session where user_id='". $user_id . "'",SELECT); 
		
	if ($time == "permanent") {$ban_type = "perm_mute"; $expires="''";} else {$ban_type = "mute"; $expires = " DATE_ADD(now(),INTERVAL $time DAY)";}
	
	perform_query("insert ban "
		. "\n set "
		. "\n type='$ban_type',"
		. "\n user_id='$user_id',"
		. "\n cookie='".$row2->cookie."',"
		. "\n fb_li_id='$fb_li_id',"
		. "\n expires=$expires,"	
		. "\n ip_address='".$row->last_ip."'",INSERT);
	
	if ($dupe_check) {
		//check for other accounts with the same IP and ask the mod if they want them banned as well
		$cur = perform_query("select user_id, username from user where last_ip ='".$row->last_ip."'",MULTISELECT);
		while ($row3 = mysql_fetch_array( $cur )) {
		    if ($row3["user_id"] == $user_id) {continue;}
			$return_code = 7;
			$additional_bans++;
			$extra .= $row3["user_id"] . "^?" . $row3["username"] . "^?";
		}
	}

	if ($time == "permanent") {
       $message = intext("Muted") . " %%u" . $user_id . ":" . $username . ";"; 
	} else {
	   $message = intext("Muted") . " %%u" . $user_id . ":" . $username . "; " . intext("for") . " $time " . intext("days"); 
	}
    return "$return_code^?$message^?$additional_bans^?$time^?$extra";
}

function WipeAccount($user_id,$wipe_type,$dupe_check) {
    global $settings;
    $additional_bans = 0;
	$extra = "";
	$fb_li_id = "";
	$return_code = 1;
	if ($dupe_check == 0) {$return_code = 4;}
	
	$row = perform_query("select username, facebook_id, linkedin_id, total_avatars, last_ip from user where user_id='". $user_id . "'", SELECT); 
	$username = $row->username;
	$last_ip = $row->last_ip;
	
	//Delete account
	if (preg_match('/1.../',$wipe_type)) {
       	perform_query("DELETE FROM user WHERE user_id=". $user_id,DELETE); 
	}

	//Ban last ip address
	if (preg_match('/.1../',$wipe_type)) {	
		if ($row->facebook_id) {$fb_li_id = $row->facebook_id;}
		if ($row->linkedin_id) {$fb_li_id = $row->linkedin_id;}
	    
		$row3 = perform_query("select cookie from session where user_id = $user_id",SELECT);
        
		if ($fb_li_id != "" || $last_ip != "") {
		
		perform_query("insert ban "
			. "\n set "
			. "\n type='wiped',"
			. "\n cookie='".$row3->cookie."',"
			. "\n fb_li_id='$fb_li_id',"
			. "\n user_id='-1',"
			. "\n ip_address='$last_ip'",INSERT);
	    }
	}
	
	perform_query("delete from session where user_id='" . $user_id . "'",DELETE); 

	//Delete all posts made by this account
	if (preg_match('/..1./',$wipe_type)) {
	    $cur2 = perform_query("select message_id from post where author_id=".$user_id, MULTISELECT);
		while ($row4 = mysql_fetch_array( $cur2 )) {
			if (GetStatus(Check_Auth()) >= $settings->status_to_hard_delete) {
			   HardDeletePost($row4["message_id"]);
			} else {
			   DeletePost($row4["message_id"]);
			}
		}
        perform_query("update post set avatar_id='-1', author_name='' where author_id='$user_id'",UPDATE); 		
		
		for ($i = 1; $i < ($row->total_avatars + 1); $i++) {
			$row6 = perform_query("select internal_id from file where author_id='". $user_id . "' and avatar_number=" . $i, SELECT); 
			$target = "files/avatar_" . $user_id . "_" . $i . "_" . $row6->internal_id;
			unlink($target);  
			perform_query("update file set is_deleted=2 where author_id='". $user_id . "' and avatar_number=" . $i . " and is_deleted=0", UPDATE); 
        }		
	}
	
	//Delete all threads created by this account
	if (preg_match('/...1/',$wipe_type)) {
	    $cur3 = perform_query("select thread_id from thread where author_id=".$user_id, MULTISELECT);
		while ($row5 = mysql_fetch_array( $cur3 )) {
			if (GetStatus(Check_Auth()) >= $settings->status_to_hard_delete) {
			   HardDeleteThread($row5["thread_id"]);
			} else {
			   DeleteThread($row5["thread_id"]);
			}
		}
	}    	

	if ($dupe_check) {
		//check for other accounts with the same IP and ask the admin if they want them wiped as well
		$cur = perform_query("select user_id, username from user where last_ip ='".$row->last_ip."'", MULTISELECT);
		while ($row3 = mysql_fetch_array( $cur )) {
			$return_code = 5;
			$additional_bans++;
			$extra .= $row3["user_id"] . "^?" . $row3["username"] . "^?";
		}
	}
	
	//clean their account number from System's block list if it's in there
	$row7 = perform_query("select thread_block_list from user where user_id='0'",SELECT); 
	$old_list = $row7->thread_block_list;
	if (preg_match('/,' . $user_id . '/',$old_list)) {
		$new_list = preg_replace('/,' . $user_id . '(,|$)/', '$1', $old_list);  
		perform_query("update user "
			. "\n set "
			. "\n thread_block_list='" . $new_list . "'"
			. " where user_id='0'",UPDATE);
	}
	
	$message = intext("Wiped account") . " %%u" . $user_id . ":" . $username . ";";
    if (preg_match('/.1../',$wipe_type)) {$message .= " + ".intext("IP ban");}	
	if (preg_match('/..1./',$wipe_type)) {$message .= " + ".intext("all posts deleted");}		
	if (preg_match('/...1/',$wipe_type)) {$message .= " + ".intext("all threads deleted");}	
	
	return "$return_code^?$message^?$additional_bans^?$wipe_type^?$extra";
}

function ThreadUnban($user_id,$thread_id) {
	$row = perform_query("select * from user where user_id='". $user_id . "'",SELECT); 
    if (!$row) {return "-1^?".intext("User not found");}

	$row2 = perform_query("select * from thread where thread_id='". $thread_id . "'",SELECT); 
	$old_list = $row2->block_allow_list;
	
	$new_list = preg_replace('/,' . $user_id . ';[0-9\.]*($|,)/', '${1}', $old_list);  
	
	perform_query("update thread "
    	. "\n set "
		. "\n block_allow_list='" . $new_list . "'"
        . " where thread_id='". $thread_id ."'",UPDATE); 	

	return "1^?" . intext("Unbanned") . " %%u" . $user_id . ":" . $row->username . "; " . intext("from thread") . " %%t" . $thread_id . ":" . $row2->title . ";";
}

function ThreadBan($user_id,$thread_id) {
    include("omegabb.php");
	$row = perform_query("select last_ip, username from user where user_id='". $user_id . "'",SELECT); 
	if (!$row) {return "-1^?".intext("User not found");}
    $entry = "," . $user_id . ";" . $row->last_ip;
	$username = mysql_real_escape_string($row->username);	   
	
	$row2 = perform_query("select * from thread where thread_id='". $thread_id . "'",SELECT); 
	$old_list = $row2->block_allow_list;
	
	$ret_value = perform_query("update thread "
    	. "\n set "
		. "\n block_allow_list='" . $old_list . $entry . "'"
        . " where thread_id='". $thread_id ."'",UPDATE); 	

	PostMsg(0, $username . " (" . $user_id . ") " . intext("has been banned from the thread") . ".", $thread_id,1);	
		
	return "1^?" . intext("Banned") . " %%u" . $user_id . ":" . $row->username . "; " . intext("from thread") . " %%t" . $thread_id . ":" . $row2->title . ";";
}

function KickFromPT($user_id,$thread_id) {
    include("omegabb.php");
	$row2 = perform_query("select username from user where user_id='". $user_id . "'",SELECT); 
	if (!$row2) {return "-1^?".intext("User not found");}
    $entry = "," . $user_id . ";" . $row2->username;
	$username = mysql_real_escape_string($row2->username);   
	
	$row = perform_query("select block_allow_list from thread where thread_id='". $thread_id . "'",SELECT); 
	$old_list = $row->block_allow_list;
	
	$new_list = preg_replace("/$entry/","",$old_list);  	 
	
	$ret_value = perform_query("update thread "
    	. "\n set "
		. "\n block_allow_list='" . mysql_real_escape_string($new_list) . "'"
        . " where thread_id='". $thread_id ."'",UPDATE); 	
	
    $row = perform_query("select * from user where user_id='". $user_id . "'",SELECT); 

	$new_my_threads = preg_replace('/,' . $thread_id . ':[0-9]+/', '' , $row->my_threads);
	$new_my_private_threads = preg_replace('/,(' . $thread_id . ')/', '', $row->my_private_threads);  
	   
    $ret_value = perform_query("update user "
    	. "\n set "
		. "\n  my_private_threads='" . $new_my_private_threads . "',"	
    	. "\n  my_threads='" . $new_my_threads . "'"
        . " where  user_id='$user_id'",UPDATE); 		
		
		
	PostMsg(0, $username . " (" . $user_id . ") " . intext("has been kicked from the thread") . ".", $thread_id,1);	
		
    return "1^?" . intext("Kicked") . " %%u" . $user_id . ":" . $row2->username . "; " . intext("from a private thread");
}

function RaiseStatus($user_id) {
    global $settings;

	$row = perform_query("select * from user where user_id='". $user_id . "'",SELECT); 
    if (!$row) {return "-1^?".intext("User not found");}
	
	$ret_value = perform_query("update user "
    	. "\n set "
		. "\n status='1'"
        . " where user_id='". $user_id ."'",UPDATE); 	
		
	if ($settings->bonus && ($settings->bonus_status == 1)) {
	   if (($settings->bonus_credits + $row->credits) > $settings->max_credits) {
			perform_query("update user "
				. "\n set "			
				. "\n credits='" . $settings->max_credits . "'"
				. " where user_id='$user_id'",UPDATE); 	 
	   } else {
			perform_query("update user "
				. "\n set "		
				. "\n credits=credits+'" . $settings->bonus_credits . "'"
				. " where user_id='$user_id'",UPDATE); 	 
	   }
    }	

    return "1^?%%u" . $user_id . ":" . $row->username . "; " . intext("status set to"). " 1"; 
}

function LowerStatus($user_id) {
	$row = perform_query("select * from user where user_id='". $user_id . "'",SELECT); 
    if (!$row) {return "-1^?".intext("User not found");}
	
	$ret_value = perform_query("update user "
    	. "\n set "
		. "\n status='0'"
        . " where user_id='". $user_id ."'",UPDATE); 	

    return "1^?%%u" . $user_id . ":" . $row->username . "; " . intext("status set to"). " 0"; 
}

function DeleteFile($file_id) { 
    global $settings;

	$row = perform_query("select * from file where file_id=". $file_id, SELECT); 
	if (IsMod($row->author_id) && !IsAdmin(Check_Auth())) {
	   echo "-1^?".intext("Cannot perform this action on a moderator.");
	   return;
	}
	if ($row->avatar_number) {
		$target = "files/avatar_" . $row->author_id . "_" . $row->avatar_number . "_" . $row->internal_id;
		unlink($target);  
		perform_query("update file set is_deleted=1 where file_id=".$file_id,UPDATE); 
		perform_query("update post set avatar_id='-1' where author_id='" . $row->author_id . "' and avatar_id='".$row->avatar_number."'",UPDATE); 		
	} else if (($row->post_id == 0) || ($row->post_id == -1)) {
	   unlink("files/tmp/from_" . $row->author_id."_".$row->file_id);  
	   unlink("files/tmp/from_" . $row->author_id."_t_".$row->file_id); 
       perform_query("update file set is_deleted=1 where file_id=".$file_id,UPDATE); 	   
	} else { 
	   unlink("files/" . $row->internal_id);
	   unlink("files/t_" . $row->internal_id);  	
       perform_query("update file set is_deleted=1 where file_id=".$file_id,UPDATE); 	   
	}

	$row2 = perform_query("select username from user where user_id=". $row->author_id, SELECT); 
	if ($row2) {$username = $row2->username;} else {$username = $row->author_id;}
	
	return "6^?".intext("Deleted file"). " \"" . $row->filename . "\" " . intext("uploaded by") . " %%u".$row->author_id.":".$username.";";
}

function DeleteCurrentAvatar($user_id) {
   global $settings;
   
   $row = perform_query("select username, current_avatar from user where user_id='". $user_id . "'",SELECT);   
   perform_query("update post set avatar_id='-1' where author_id='" . $user_id . "' and avatar_id='".$row->current_avatar."'",UPDATE); 	
	   
   if ($row->current_avatar != 0) {
	   $row2 = perform_query("select internal_id from file where author_id='". $user_id . "' and avatar_number=" . $row->current_avatar, SELECT); 
	   $target = "files/avatar_" . $user_id . "_" . $row->current_avatar . "_" . $row2->internal_id;
	   unlink($target);  
	   perform_query("update file set is_deleted=1 where internal_id='" . $row2->internal_id . "'",UPDATE); 
   }

   return "1^?".intext("Deleted avatar belonging to") . " %%u" . $user_id . ":" . $row->username . ";";
}

function GiveModStatus($user_id) {
    global $settings;
    $row = perform_query("select username from user where user_id='". $user_id . "'",SELECT);   
   
	perform_query("update user "
    	. "\n set "
		. "\n status='3'"
        . " where user_id='". $user_id ."'",UPDATE); 	

	if ($settings->bonus && ($settings->bonus_status == 3)) {
	   if (($settings->bonus_credits + $row->credits) > $settings->max_credits) {
			perform_query("update user "
				. "\n set "			
				. "\n credits='" . $settings->max_credits . "'"
				. " where user_id='$user_id'",UPDATE); 	 
	   } else {
			perform_query("update user "
				. "\n set "		
				. "\n credits=credits+'" . $settings->bonus_credits . "'"
				. " where user_id='$user_id'",UPDATE); 	 
	   }
    }

    return "1^?%%u" . $user_id . ":" . $row->username . "; " . intext("was given moderator status"); 
}

function RevokeModStatus($user_id) {
    $row = perform_query("select username from user where user_id='". $user_id . "'",SELECT);   
	
	perform_query("update user "
    	. "\n set "
		. "\n status='1'"
        . " where user_id='". $user_id ."'",UPDATE); 	

    return "1^?%%u" . $user_id . ":" . $row->username . "; " . intext("status set to"). " 1";
}

function GiveEditorStatus($user_id) {
    global $settings;
    $row = perform_query("select username from user where user_id='". $user_id . "'",SELECT);   
	
	perform_query("update user "
    	. "\n set "
		. "\n status='2'"
        . " where user_id='". $user_id ."'",UPDATE); 	

	if ($settings->bonus && ($settings->bonus_status == 2)) {
	   if (($settings->bonus_credits + $row->credits) > $settings->max_credits) {
			perform_query("update user "
				. "\n set "			
				. "\n credits='" . $settings->max_credits . "'"
				. " where user_id='$user_id'",UPDATE); 	 
	   } else {
			perform_query("update user "
				. "\n set "		
				. "\n credits=credits+'" . $settings->bonus_credits . "'"
				. " where user_id='$user_id'",UPDATE); 	 
	   }
    }

    return "1^?%%u" . $user_id . ":" . $row->username . "; " . intext("status set to"). " 2"; 
}

function RevokeEditorStatus($user_id) {
    $row = perform_query("select username from user where user_id='". $user_id . "'",SELECT);   
	
	perform_query("update user "
    	. "\n set "
		. "\n status='1'"
        . " where user_id='". $user_id ."'",UPDATE); 	

    return "1^?%%u" . $user_id . ":" . $row->username . "; " . intext("status set to"). " 1"; 
}

function UnDeleteThread($thread_id) {
    $row = perform_query("select * from thread where thread_id='". $thread_id ."'",SELECT);
	if (!$row) {return "-1^?".intext("Unable to undelete thread.  Deleted thread no longer in database");}
 
	perform_query("update thread "
    	. "\n set "
		. "\n state='0'"
        . " where thread_id='". $thread_id ."'",UPDATE); 	

    return "1^?".intext("Undeleted thread")." %%t".$thread_id.":".$row->title.";";
}

function DeleteThread($thread_id) {
    $row = perform_query("select title from thread where thread_id='". $thread_id ."'",SELECT);
	$ret_value = perform_query("update thread "
    	. "\n set "
		. "\n state='2'"
        . " where thread_id='". $thread_id ."'",UPDATE); 	

	$cur5 = perform_query("select * from file where thread_id=". $thread_id, MULTISELECT); 
	while ($row2 = mysql_fetch_array( $cur5 )) {
		if ($row2[file_type] == 1) {
			unlink("files/t_" . $row2[internal_id]);  
			unlink("files/" . $row2[internal_id]);
		} else {
			unlink("files/". $row2[internal_id]);  
		}
	}
	perform_query("update file set is_deleted=1 where thread_id=".$thread_id,UPDATE); 

    return "1^?".intext("Deleted thread")." %%t".$thread_id.":".$row->title.";";
}

function HardDeleteThread($thread_id) {
	$row = perform_query("select title from thread where thread_id=$thread_id", SELECT);
   	if (!$row) {return "-1^?".intext("Thread is already deleted");}

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
	perform_query("update file set is_deleted=1 where thread_id=".$thread_id,UPDATE); 

    return "1^?".intext("Deleted thread")." %%t".$thread_id.":".$row->title.";";
}

function OpenThread($thread_id) {
	$row = perform_query("select * from thread where thread_id='$thread_id'",SELECT); 			
    if ($row->state == 1) {
		$ret_value = perform_query("update thread "
			. "\n set "
			. "\n state=0,"
		    . "\n no_auto_close='1'"			
			. " where thread_id='". $thread_id ."'",UPDATE); 	
	} elseif ($row->state == 4) {
		$ret_value = perform_query("update thread "
			. "\n set "
			. "\n state=3,"
		    . "\n no_auto_close='1'"					
			. " where thread_id='". $thread_id ."'",UPDATE); 	
	}

    return "1^?".intext("Opened thread")." %%t".$thread_id.":".$row->title.";";
}

function CloseThread($thread_id) {
	$row = perform_query("select * from thread where thread_id='$thread_id'",SELECT); 			
    if ($row->state == 0) {
		$ret_value = perform_query("update thread "
			. "\n set "
			. "\n state=1"
			. " where thread_id='". $thread_id ."'",UPDATE); 	
	} elseif ($row->state == 3) {
		$ret_value = perform_query("update thread "
			. "\n set "
			. "\n state=4"
			. " where thread_id='". $thread_id ."'",UPDATE); 	
	}

    return "1^?".intext("Closed thread")." %%t".$thread_id.":".$row->title.";";
}

function PreventAutoClose($thread_id) {
	$row = perform_query("select title from thread where thread_id='$thread_id'",SELECT); 	
	
	$ret_value = perform_query("update thread "
    	. "\n set "
		. "\n no_auto_close='1'"
        . " where thread_id='". $thread_id ."'",UPDATE); 	

    return "1^?".intext("Prevented auto-close of thread")." %%t".$thread_id.":".$row->title.";";
}

function AllowAutoClose($thread_id) {
	$row = perform_query("select title from thread where thread_id='$thread_id'",SELECT); 	
	
	$ret_value = perform_query("update thread "
    	. "\n set "
		. "\n no_auto_close='0'"
        . " where thread_id='". $thread_id ."'",UPDATE); 	

    return "1^?".intext("Allowed auto-close of thread")." %%t".$thread_id.":".$row->title.";";
}

function UnStickyThread($thread_id) {
	$row = perform_query("select * from thread where thread_id='$thread_id'",SELECT); 			
	if ($row->forum_id == 12) {return "-1^?Can't unsticky a PT";}
		
    if ($row->state == 3) {
		perform_query("update thread "
			. "\n set "
			. "\n state=0"
			. " where thread_id='". $thread_id ."'",UPDATE); 	
	} elseif ($row->state == 4) {
		perform_query("update thread "
			. "\n set "
			. "\n state=1"
			. " where thread_id='". $thread_id ."'",UPDATE); 	
	}
	
    return "1^?".("Un-stickied")." %%t".$thread_id.":".$row->title.";";
}

function StickyThread($thread_id) {
	$row = perform_query("select * from thread where thread_id='$thread_id'",SELECT); 			
	if ($row->forum_id == 12) {return "-1^?Can't sticky a PT";}
	
    if ($row->state == 0) {
		perform_query("update thread "
			. "\n set "
			. "\n state=3"
			. " where thread_id='". $thread_id ."'",UPDATE); 	
	} elseif ($row->state == 1) {
		perform_query("update thread "
			. "\n set "
			. "\n state=4"
			. " where thread_id='". $thread_id ."'",UPDATE); 	
	}
		
    return "1^?".("Stickied")." %%t".$thread_id.":".$row->title.";";
}

function MoveThread($thread_id,$forum_id){
    global $settings;
	$row2 = perform_query("select title from thread where thread_id=$thread_id",SELECT);
	
	perform_query("update thread "
    	. "\n set " 
		. "\n forum_id='$forum_id'"
        . " where thread_id='". $thread_id ."'",UPDATE); 	
		
    return "1^?".intext("Moved")." %%t".$thread_id.":".$row2->title."; ".intext("to"). " \"".$settings->forum_topic_names[$forum_id-1]."\"";
}

function DeleteWikiPost($thread_id,$revision) {
	$row1 = perform_query("select * from post where thread_id='". $thread_id. "' and revision=" . $revision . " and ((reply_num = 1) or (reply_num = -1))", SELECT);

	if ($row1->state == 1) {return "-1^?".intext("Revision has already been deleted");}
	
	perform_query("insert into post(message, author_id, message_id, revision, thread_id, ip_address, reply_num, avatar_id, author_name ) " 
		. "select message, author_id, (message_id * -1), revision, (thread_id * -1), ip_address, reply_num, avatar_id, author_name from post where message_id=". $row1->message_id, INSERT); 	
	
	$cur = perform_query("select * from file where post_id=" . $row1->message_id, MULTISELECT);
	
	while ($row2 = mysql_fetch_array( $cur )) {
	   if ($row2[file_type] == 1) {
		   unlink("files/t_" . $row2[internal_id]);  
		   unlink("files/" . $row2[internal_id]);
	   } else {
	       unlink("files/".$row2[internal_id]);  
	   }
    }
    perform_query("update file set is_deleted=1 where post_id=".$row1->message_id,UPDATE); 
	
	perform_query("update post "
    	. "\n set "
		. "\n state='1', "
		. "\n message='<p class=\"system\">".intext("Post Deleted")."</p>'"	
        . " where message_id='". $row1->message_id ."'",UPDATE); 	
	
	$row3 = perform_query("select title from thread where thread_id=". $thread_id, SELECT);

    return "1^?".intext("Deleted revision number") . " " . ($revision+1) . " " . intext("in article")." %%t".$thread_id.":".$row3->title.";";
}

function HardDeleteWikiPost($thread_id,$revision) {
	$row1 = perform_query("select * from post where thread_id='". $thread_id. "' and revision=" . $revision . " and ((reply_num = 1) or (reply_num = -1))", SELECT);

	if ($row1->state == 1) {return "-1^?".intext("Revision has already been deleted");}
	
	$cur = perform_query("select * from file where post_id=" . $row1->message_id, MULTISELECT);
	
	while ($row2 = mysql_fetch_array( $cur )) {
	   if ($row2[file_type] == 1) {
		   unlink("files/t_" . $row2[internal_id]);  
		   unlink("files/" . $row2[internal_id]);
	   } else {
	       unlink("files/" . $row2[internal_id]);  
	   }
    }
    perform_query("update file set is_deleted=1 where post_id=".$row1->message_id,UPDATE); 
	
	perform_query("update post "
    	. "\n set "
		. "\n state='1', "
		. "\n message='<p class=\"system\">".intext("Post Deleted")."</p>'"	
        . " where message_id='". $row1->message_id ."'",UPDATE); 	
		
	$row3 = perform_query("select title from thread where thread_id=". $thread_id, SELECT);

    return "1^?".intext("In article")." %%t".$thread_id.":".$row3->title."; ".intext("deleted revision number")." ".($revision+1);
}
?> 