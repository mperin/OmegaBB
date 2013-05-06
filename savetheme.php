<?php
/*OmegaBB 0.9.2*/
    include('omegabb.php');

	$user_id=GetParam($_REQUEST,'user_id','');
    $theme=GetParam($_REQUEST,'theme','');

    $auth_ret = Check_Auth();
    if( $auth_ret <= 0) {
    	echo "-1^?".intext("Not signed in");
    	return;
    }   

    echo SaveTheme($user_id, $theme);	
?> 