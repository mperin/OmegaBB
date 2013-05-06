<?php
	/*OmegaBB 0.9.2*/
	include("config.php");
	include("common.php");

	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

	$count = 0;
    $output = ""; 
	
	if ($info = lockdown_button_check(SITEDOWN)) {
	   $sysinfo = explode("^?",$info);
	   if ($sysinfo[1]) {
	      echo "-1^?".$sysinfo[1];
	   } else {
	      echo "-1^?".intext("Feature disabled");
	   }
	   return;
	}
	
	if ($handle = opendir('emotes')) {
		while ($file = readdir($handle)) {
			if ($file != "." && $file != "..") {
	            if ($file == "Thumbs.db") {continue;}
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