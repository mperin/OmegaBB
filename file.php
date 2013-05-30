<?php
	/*OmegaBB 0.9.3*/
	include('config.php');
	include('common.php');

	global $settings; 

    $id=GetParam($_REQUEST,'id','');
	$t=GetParam($_REQUEST,'t','');
	$d=GetParam($_REQUEST,'d','');
	$avatar_number=GetParam($_REQUEST,'avatar_number','');
	$uid = GetParam($_REQUEST,'uid','');	
	$file_id = GetParam($_REQUEST,'file_id','');
	$emote = GetParam($_REQUEST,'emote','');
	$gift = GetParam($_REQUEST,'gift','');

	$user_id = Check_Auth();

	if ($settings->must_login_to_see_forum && ($user_id <= 0)) {header("HTTP/1.0 404 Not Found"); exit;}
 	if (lockdown_button_check(MUSTLOGIN) && ($user_id <= 0)) {header("HTTP/1.0 404 Not Found"); exit;}
	if (lockdown_button_check(SITEDOWN)) {header("HTTP/1.0 404 Not Found"); exit;}
	if ($settings->allow_hotlinking == false && strpos($_SERVER['HTTP_REFERER'],$settings->website_url)!==0) {header("HTTP/1.0 404 Not Found"); exit;}
 
    if ($emote) {
	    //displaying emote
		if ($settings->emotes_allowed == false) {header("HTTP/1.0 404 Not Found"); exit;}
		$filename = $emote;
		$datafile = "emotes/$emote";
	} else if ($gift) {
	    //displaying gift
		if ($settings->gifts_enabled == false) {header("HTTP/1.0 404 Not Found"); exit;}  
		if ($settings->must_login_to_see_profile && ($user_id <= 0)) {header("HTTP/1.0 404 Not Found"); exit;}
		$filename = $gift;		
		$datafile = "gifts/$gift";
    } else if ($file_id) { 
	    //direct access using file_id, mods only
		if (!isMod($user_id)) {header("HTTP/1.0 404 Not Found"); exit;}
		$row = perform_query("select * from file where file_id='$file_id'",SELECT);
		if (!$row) {header("HTTP/1.0 404 Not Found"); exit;}		   
		$datafile = "files/tmp/from_".$row->author_id."_".$file_id;
		if (preg_match('/^image/',$row->mime_type)) {$d = "inline";}
	} else if ($avatar_number) { 
	    //displaying an avatar
		if (($settings->avatars_allowed == false) && (!isMod($user_id))) {header("HTTP/1.0 404 Not Found"); exit;}
		$row = perform_query("select * from file where author_id='$uid' and avatar_number='$avatar_number'",SELECT);
		if (!$row) {header("HTTP/1.0 404 Not Found"); exit;}		   
		$datafile = "files/avatar_".$uid."_".$avatar_number."_".$row->internal_id;
	} else {
        //displaying a file attachment	
		if (($settings->file_upload_allowed == false) && (!isMod($user_id))) {header("HTTP/1.0 404 Not Found"); exit;}
		$row = perform_query("select * from file where external_id='$id'",SELECT);
		if (!$row) {header("HTTP/1.0 404 Not Found"); exit;}		   
		if (!IsValidThread($row->thread_id) && !isMod($user_id)) {header("HTTP/1.0 404 Not Found"); exit;}		
		if ($row->forum_id == 12) {
		   if ($user_id <= 0) {header("HTTP/1.0 404 Not Found"); exit;}
		   $row2 = perform_query("select block_allow_list from thread where thread_id='".$row->thread_id."'",SELECT);
		   if (!preg_match('/,' . $user_id . ';/',$row2->block_allow_list) && !isMod($user_id)) {header("HTTP/1.0 404 Not Found"); exit;}
		}		   
		if ($t == "small") {
		   $datafile = "files/t_". $row->internal_id;
		} else {
		   $datafile = "files/". $row->internal_id;
		}		   
	}
	
	if (!file_exists($datafile)) {header("HTTP/1.0 404 Not Found"); exit;}

	if ($row) {
		$mimetype = $row->mime_type; 
		//for security reasons, mimetype is octet-stream except for images
		if (strpos($mimetype, 'image') === false) {
			if ((strpos(strtolower($HTTP_USER_AGENT), 'msie') !== false) || (strpos(strtolower($HTTP_USER_AGENT), 'opera') !== false )) {
			   $mimetype = 'application/octetstream';
			} else {
			   $mimetype = 'application/octet-stream';
			}
		} 
		header('Content-type: '.$mimetype);

		if (($d == "inline") || ($avatar_number) ) {
		   if ($t == "small") {
			  header("Content-Disposition: inline; filename=\"t_".$row->filename."\"");
		   } else {
			  header("Content-Disposition: inline; filename=\"".$row->filename."\"");
		   }
		} else {
		   header("Content-Disposition: attachment; filename=\"".$row->filename."\"");
		}
    } else { //either a gift or emote
	    header('Content-type: image');
	    header("Content-Disposition: inline; filename=\"".$filename."\"");
	}
	readfile($datafile);
	exit;
?>