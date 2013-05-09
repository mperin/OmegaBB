<?php
/*OmegaBB*/

header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

    include("captcha/securimage.php");
    include('omegabb.php');
	
    $user_id=GetParam($_REQUEST,'user_id','');
    $input=GetParam($_REQUEST,'input',1);
    $thread_id=GetParam($_REQUEST,'thread_id','');
    $postcaptcha=GetParam($_REQUEST,'postcaptcha','');	

    $auth_ret = Check_Auth();
    
    if (( $auth_ret <= 0) || ($user_id == 0)) {
	    echo "-1^?".intext("Not signed in");
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
   
    echo PostMsg($user_id, $input, $thread_id);  
?> 