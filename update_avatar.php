<?php
	/*OmegaBB*/
	include('omegabb.php');

	$avatar_number=GetParam($_REQUEST,'avatar_number','');

	$auth_ret = Check_Auth();
	
	if ($settings->allow_avatar_change == 0) {
	   return "-1^?".intext("Feature disabled");
	}
	if( $auth_ret <= 0) {
	   echo "-1^?".intext("Not signed in");
	   return;
	}

	echo UpdateAvatar($avatar_number);
?>