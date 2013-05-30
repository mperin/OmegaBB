<?php
	/*OmegaBB 0.9.3*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	
	$user_id=GetParam($_REQUEST,'user_id','');
    
	$auth_ret = Check_Auth();

    if (($auth_ret <= 0) || ($user_id != $auth_ret)) {
    	echo "-1^?".intext("Not signed in");
    	return;
    }   
	
    echo GetSettingsInfo($user_id);   
?>