<?php
	/*OmegaBB 0.9.2*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	
	$thread_id=GetParam($_REQUEST,'thread_id','');
    
	$auth_ret = Check_Auth();

    if ($auth_ret <= 0) {
    	echo "-1^?".intext("Not signed in");
    	return;
    }   
	
    echo LeavePT($thread_id,$auth_ret);   	
?> 