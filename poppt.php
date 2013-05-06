<?php
	/*OmegaBB 0.9.2*/
	 
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 
 
    include('config.php');
    include('common.php');
    include('uncommon.php');
	
	$auth_ret = Check_Auth();
	if ($auth_ret <= 0) {
	   echo "-1^?".intext("You must sign in to see your private threads");	
	} else {
       echo GetPT($auth_ret);
	}
?> 