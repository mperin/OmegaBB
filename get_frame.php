<?php
	/*OmegaBB 0.9.2*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

	include('config.php');
    include('common.php');
	
	$page=GetParam($_REQUEST,'page','');

	if ($info = lockdown_button_check(SITEDOWN)) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      echo "-1^?".$sysinfo[1];
	   } else {
	      echo "-1^?".intext("Feature disabled");
	   }
	   return;
	}
    
    echo "1^?" . file_get_contents("./$page");
?> 