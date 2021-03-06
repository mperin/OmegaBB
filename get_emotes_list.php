<?php
	/*OmegaBB 0.9.3*/
	include("config.php");
	include("common.php");

	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

	$count = 0;
    $output = ""; 
	global $settings;
	
	if ($info = lockdown_button_check(SITEDOWN)) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      echo "-1^?".$sysinfo[1];
	   } else {
	      echo "-1^?".intext("Feature disabled");
	   }
	   return;
	}
	
	if ($settings->emotes_allowed == false || $settings->helpmenu5_enabled == false) {
	   echo "-1^?".intext("Feature disabled");
	   return;
	}
	
	if ($settings->must_login_to_see_forum && (Check_Auth() <= 0)) {
		echo "-1^?".intext("Must sign in to see forum and articles"); 
		return;
	}
	
	if ($handle = opendir('emotes')) {
		while ($file = readdir($handle)) {
			if ($file != "." && $file != "..") {
	            if ($file == "Thumbs.db") {continue;}
				if ($file == ".htaccess") {continue;}				
				$files[] = $file;
				$count++;
			}
		}
		closedir($handle);
	}

	sort($files);

	foreach ($files as $f) {
	   $output .= "^?" . $f;  
	}	

    echo "1^?" . $count . $output; 
?> 