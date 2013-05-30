<?php
	/*OmegaBB 0.9.3*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('config.php');
	include('common.php');
	include('uncommon.php');
	
	$gift_id=GetParam($_REQUEST,'gift_id','');
	
	$auth_ret = Check_Auth();

    if ($auth_ret <= 0) {
    	echo "-1^?".intext("Not signed in");
    	return;
    }   
	
    echo DeleteGift($auth_ret,$gift_id);   
?> 