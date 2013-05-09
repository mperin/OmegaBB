<?php
/*OmegaBB*/

//initialization routine and a collection of commonly used functions

error_reporting(0);
@ini_set('display_errors', 0);
mb_internal_encoding("UTF-8");

$language_hash = array();
if ($settings->language != "en") {
   include('lang/language.'.$settings->language.'.php');
}

include('database.php');
$_db = new database( $settings->server, $settings->user, $settings->pass, $settings->database,'' );   

$userid=0;
$sessioncookie=null;
$session_id=null;

define('DISPLAY_SQL_ERRORS',1);

define('SELECT',0);
define('UPDATE',1);
define('MULTISELECT',2);
define('DELETE',3);
define('INSERT',4);

define('NEWUSERCAPTCHA',1);
define('NONEWACCOUNTS',2);
define('MUSTLOGIN',4);
define('FORUMDOWN',8);
define('PTDOWN',16);
define('SITEDOWN',32);

function lockdown_button_check($code) {
    global $settings;
	
    $row = perform_query("select settings, ban_expire_time, theme from user where user_id=0",SELECT);
    if ($row->settings & $code) {
	    //check to see if it has expired
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
				return 0;
	        } 
        }
    	return "1^?".$row->theme;
	}
    return 0;
}

function intext($text) {
   global $language_hash;
   global $settings;
   
   if ($settings->language == "en") {
	  return ($text);      
   }
   
   if ($language_hash[$text]) { 
      return $language_hash[$text]; 
   } else { 
	  return ($text);
   }
}

function GetParam( $arr, $name, $def=null ) {
    if (isset($arr[$name])) {
	   return sanitize($arr[$name],$def);
    }
} 

function sanitize($input,$type){
    if (!isset($input)) {return null;}
	
	if ($type >= 1) {
		$input = preg_replace('/</','&lt;',$input);
		$input = preg_replace('/>/','&gt;',$input);
		$input = preg_replace('/\^\?/','?',$input);
		$input = preg_replace('/\^\*/','\*',$input);	        
		$input = preg_replace('/\n/','<br>',$input);
	}        
	
	// Stripslashes is gpc on
	if (get_magic_quotes_gpc())
	{
		$input = stripslashes($input);
	}
	// Quote if not a number or a numeric string
	if ( !is_numeric($input) )
	{
		$input = mysql_real_escape_string($input);
	}
	
    return $input;
}

function refresh_session() {	
	$sessioncookie = $_COOKIE['sessioncookie'];
	$sess=md5( $sessioncookie . $_SERVER['REMOTE_ADDR'] );
	
	$row = perform_query("select * from session where session='$sess'",SELECT);
	
	if ($row) {
		//update only if last_activity is more than 5 minutes old
		$d1 = time() ;
		$d2 = strtotime($row->last_activity);
		$d2 += 300;
		
		if ($d1 > $d2) {
		   perform_query("update session set last_activity=now() where session='$sess'",UPDATE);
		}			
		return 1;
	} else { 
		return -1;
	}
}
	
function Check_Auth(){
	global $userid;
	global $session_id;
		
	if ($userid > 0) {
	   return $userid;
	}

	$isess = $_COOKIE['sessioncookie'];
	
	if ($isess == null) {
		return -1;
	}

	$sess=md5( $isess . $_SERVER['REMOTE_ADDR'] );
    
    if ($sess == "0") { return -2; }
	
	$row = perform_query("select * from session where session='$sess'",SELECT);

	if (!$row) {
	   return -3;
	}

	refresh_session();
	$userid=$row->user_id; 
	$session_id = $sess;
	  
	return $row->user_id;
}

function perform_query($query,$type){
	global $_db;
		
    $_db->setQuery( $query );
    $cur = $_db->query();
   
	if (!$_db->_cursor) {
	    if (DISPLAY_SQL_ERRORS) {
			$bt = debug_backtrace();
			$file = basename($bt[0]["file"]);
			$line = $bt[0]["line"];
			exit("-1^?".intext("Failed query").": \"" . $query . "\" ".intext("called in")." $file at line $line");
		} else {
			exit("-1^?".intext("Database error"));
		}
	}	  
	if ($type == SELECT) {
		$row = NULL;
		$_db->loadObject( $row );
		return $row;
	} else if ($type == INSERT) {
		return $_db->insertid();
	} else {
		return $cur;
	} 
}	

function IsValidThread($thread_id,$for_entry=0) {
   global $settings;

   $row = perform_query("select * from thread where thread_id='" . $thread_id . "'", SELECT);
   if (!$row) { return 0;}
   
    if (!IsValidForum($row->forum_id)) {
       return 0;
    }

	//deleted
	if ($row->state == 2) {
	   return 0;
	}
	
	if ($for_entry) {
		//1 = closed thread, 4 = sticky and closed thread
		if ( (($row->state == 4) && ($row->forum_id != 13)) || (($row->state == 1) && ($row->forum_id != 13))){
		   return 0;
		}
	}
	
    return 1;
}

function IsValidForum($forum_id) {
  global $settings;

   if ($forum_id <= (11 - $settings->total_forums)) {
      return 0;
   }
   
   if ($settings->enable_articles) {
		if ($forum_id > 13) {
		  return 0;
	   }
   } else {
		if ($forum_id > 12) {
		  return 0;
	   }
   }  
   
   if ((!($settings->enable_private_threads)) && ($forum_id == 12)) {
      return 0;
   }
   if ((!($settings->enable_forums)) && ($forum_id != 13) && ($forum_id != 12)  ) {
      return 0;
   }   

   if ((lockdown_button_check(FORUMDOWN+SITEDOWN) && $forum_id != 12) || (lockdown_button_check(PTDOWN) && $forum_id == 12)) {
      return 0;
   }
	
   return 1;
}

function GetStatus($user_id) {
    $row = perform_query("select status from user where user_id='". $user_id . "'",SELECT); 
	
	if (!$row) { 
	   return -1 ; 
	} else {
	   return $row->status;
	}
}

function IsBanned($user_id) {
	global $settings;

	$row = perform_query("select * from ban where user_id=".$user_id,SELECT);
	if ($row) {
		if ($row->type == "perm_ban") {$state = "You are banned";}
		if ($row->type == "perm_mute") {$state = "You are muted";}
		if ($row->type == "ban") {$state = "Your are banned until";}
		if ($row->type == "mute") {$state = "Your are muted until";}
		if ($row->type == "wiped") {$state = "New accounts not allowed";}
		
		if ($row->type == "ban" || $row->type == "mute") {
			$d1 = time();
			$d2 = strtotime($row->expires);	
			if ($d1 > $d2) {			
                perform_query("delete from ban where user_id='" . $user_id . "'",DELETE); 
				return 0;
			} else {
				$dtime = new DateTime($row->expires);
				$dtime->setTimeZone(new DateTimeZone($settings->time_zone));
				$timestamp = $dtime->format($settings->datetime_format);  
				return intext("$state")." ". $timestamp;
			}
		} else {
			return intext("$state");
		}
	} else {
		return 0;
	}
}

function IsMod($user_id) {		
    $row = perform_query("select status from user where user_id='". $user_id . "'",SELECT); 
	
	if ($row->status > 2) {
	   return 1; 
	} else {
	   return 0;
	}
}    

function IsAdmin($user_id) {		
    $row = perform_query("select status from user where user_id='". $user_id . "'",SELECT); 
	
	if ($row->status > 4) {
	   return 1; 
	} else {
	   return 0;
	}
}    

function LogEvent($type,$text) {
    if ($type == 2) {
		$query = "insert log "
			. "\n set "
			. "\n  event_type='" . $type . "',"
			. "\n  text='" . mysql_real_escape_string($text) . "',"
			. "\n  user_id='0'";
    } else {
		$query = "insert log "
			. "\n set "
			. "\n  event_type='" . $type . "',"
			. "\n  text='" . mysql_real_escape_string($text) . "',"
			. "\n  user_id='" . Check_Auth() . "'";
	}
	perform_query($query, INSERT);
}
?>