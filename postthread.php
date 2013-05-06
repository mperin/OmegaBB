<?php
/*OmegaBB 0.9.2*/
header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

    include("captcha/securimage.php");
    include('omegabb.php');

	$user_id=GetParam($_REQUEST,'user_id','');
    $forum_id=GetParam($_REQUEST,'forum_id','');
    $content_of_thread=GetParam($_REQUEST,'content_of_thread',1);
    $thread_title=GetParam($_REQUEST,'thread_title',1);
    $postcaptcha=GetParam($_REQUEST,'captcha','');	
    $wiki_type=GetParam($_REQUEST,'wiki_type','');	
    $comment_type=GetParam($_REQUEST,'comment_type','');
	
    $auth_ret = Check_Auth();
    
    if (($user_id != 0) && ($user_id != $auth_ret)) {
    	echo "-1^?Error occurred, " . $auth_ret;
    	return;
    }   
	
	if (( $auth_ret <= 0) || ($user_id == 0)) {
	   echo "^?-1^?".intext("Not signed in");
	   return;
    } else {
        $user_id = $auth_ret;
		$temp = GetInfo($user_id);
	    $user_info = explode("^?",$temp);  
    }
	
	if ((lockdown_button_check(NEWUSERCAPTCHA) || $settings->new_user_post_captcha) && ($user_info[5] == "0")) {
		$img = new Securimage();
		$valid = $img->check($postcaptcha);
		if (!$valid) {
		   echo "-1^?".intext("Bad captcha");
		   return;
		}
	}	

	if ($forum_id == 13) {
	    echo PostThread($user_id, $forum_id, $content_of_thread, $thread_title, $wiki_type, $comment_type);
	} else {
        echo PostThread($user_id, $forum_id, $content_of_thread, $thread_title);
    }
?> 