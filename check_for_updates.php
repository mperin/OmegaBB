<?php
	/*OmegaBB*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	
    if (!isAdmin(Check_Auth())) {
    	echo "-1^?".intext("You must be an administrator to do this");
    	return;
    }

    echo CheckForUpdates();           		
?>