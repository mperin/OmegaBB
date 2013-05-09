<?php
/*OmegaBB*/
header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

    include('omegabb.php');
	
    if (Check_Auth() > 0) {       		
	   refresh_session();
       echo Logout();
    }
?> 