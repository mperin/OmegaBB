<?php
	/*OmegaBB 0.9.3*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include("captcha/securimage.php");
    include('omegabb.php');
		
	$user_id=GetParam($_REQUEST,'user_id','');
	$pt_type=GetParam($_REQUEST,'pt_type','');
    $members=GetParam($_REQUEST,'members',1);
    $content_of_thread=GetParam($_REQUEST,'content_of_thread',1);
    $thread_title=GetParam($_REQUEST,'thread_title',1);
    $captcha = GetParam($_REQUEST,'captcha','');
	
    $auth_ret = Check_Auth();
    
    if (($auth_ret <= 0) || ($user_id != $auth_ret)) {
    	echo "-1^?".intext("Not signed in");
    	return;
    }   
            
    $user_id = $auth_ret;
    $temp = GetInfo($user_id);
	$user_info = explode("^?",$temp);  
		   
	if ((lockdown_button_check(NEWUSERCAPTCHA) || $settings->new_user_post_captcha) && ($user_info[5] == "0")) {
		$img = new Securimage();
		$valid = $img->check($captcha);
		if (!$valid) {
		   echo "-1^?".intext("Bad captcha");
		   return;
		}
	}				

    echo PostPrivateThread($user_id, $pt_type, $members, $content_of_thread, $thread_title);
?> 