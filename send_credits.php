<?php
	/*OmegaBB*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	
    $user_id=GetParam($_REQUEST,'user_id','');
    $amount=GetParam($_REQUEST,'amount','');

    $auth_ret = Check_Auth();
    
    if ($auth_ret <= 0) {
	    echo "-1^?".intext("Not signed in");
	    return;
    } 
   
    echo SendCredits($auth_ret, $user_id, $amount);
?>