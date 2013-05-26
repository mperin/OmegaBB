<?php
	/*OmegaBB*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	
	$event_id=GetParam($_REQUEST,'event_id','');
	
	$auth_ret = Check_Auth();
	
    if ($auth_ret <= 0) {
    	echo "-1^?".intext("Not signed in");
    	return;
    }
	
    echo DisapproveEvent($event_id);
?>