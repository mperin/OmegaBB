<?php
	/*OmegaBB 0.9.3*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

	include('omegabb.php');

	$auth_ret = Check_Auth();
	if (!IsAdmin($auth_ret)) {
	   echo "-1^?".intext("You must be an administrator to do this");
	   return;
	}

	echo SaveSiteSettings();
?> 