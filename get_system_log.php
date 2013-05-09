<?php
	/*OmegaBB*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	
	$page=GetParam($_REQUEST,'page','');

    if (!IsMod(Check_Auth())) {
    	echo "-1^?".intext("user not a moderator");
    	return;
    }   
   
    echo GetSystemLog($page);   
?>