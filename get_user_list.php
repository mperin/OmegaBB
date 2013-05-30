<?php
	/*OmegaBB 0.9.3*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	
	$page=GetParam($_REQUEST,'page','');
	$filter=GetParam($_REQUEST,'filter','');
    
	$auth_ret = Check_Auth();

    if ($auth_ret <= 0) {
    	echo "-1^?".intext("Not signed in");
    	return;
    }   
	
    echo GetUserList($page,$filter);   
?> 