<?php
	/*OmegaBB 0.9.2*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');

    if (!IsMod(Check_Auth())) {
       echo "-1^?".intext("user not a moderator");
	} else {
       echo GetSiteSettings();   
	}
?> 