<?php
/*OmegaBB 0.9.2*/
    include('omegabb.php');
	global $settings;
	
	$page=GetParam($_REQUEST,'page','');
	if ($page == "") {$page = 0;}
	
	$output = "";

	if (!lockdown_button_check(SITEDOWN)) {
	   if ($page == 0) {				   
			if ($settings->first_tab_indexable && $settings->first_tab_enabled) {$output .= PHP_EOL . '<br><a href="thread.php?id='.$settings->first_tab_location.'">'.$settings->first_tab_name.'</a>';}
			if ($settings->second_tab_indexable && $settings->second_tab_enabled) {$output .= PHP_EOL . '<br><a href="thread.php?id='.$settings->second_tab_location.'">'.$settings->second_tab_name.'</a>';}
			if ($settings->second_last_tab_indexable && $settings->second_last_tab_enabled) {$output .= PHP_EOL . '<br><a href="thread.php?id='.$settings->second_last_tab_location.'">'.$settings->second_last_tab_name.'</a>';}
			if ($settings->last_tab_indexable && $settings->last_tab_enabled) {$output .= PHP_EOL . '<br><a href="thread.php?id='.$settings->last_tab_location.'">'.$settings->last_tab_name.'</a>';} 		
			if ($settings->helpmenu1_indexable && $settings->helpmenu1_enabled && $settings->enable_helpmenu) {$output .= PHP_EOL . '<br><a href="thread.php?id='.$settings->helpmenu1_location.'">'.$settings->helpmenu1_name.'</a>';}
			if ($settings->helpmenu2_indexable && $settings->helpmenu2_enabled && $settings->enable_helpmenu) {$output .= PHP_EOL . '<br><a href="thread.php?id='.$settings->helpmenu2_location.'">'.$settings->helpmenu2_name.'</a>';}
			if ($settings->helpmenu3_indexable && $settings->helpmenu3_enabled && $settings->enable_helpmenu) {$output .= PHP_EOL . '<br><a href="thread.php?id='.$settings->helpmenu3_location.'">'.$settings->helpmenu3_name.'</a>';}
			if ($settings->helpmenu4_indexable && $settings->helpmenu4_enabled && $settings->enable_helpmenu) {$output .= PHP_EOL . '<br><a href="thread.php?id='.$settings->helpmenu4_location.'">'.$settings->helpmenu4_name.'</a>';}
			if ($settings->helpmenu6_indexable && $settings->helpmenu6_enabled && $settings->enable_helpmenu) {$output .= PHP_EOL . '<br><a href="thread.php?id='.$settings->helpmenu6_location.'">'.$settings->helpmenu6_name.'</a>';}		  
	   }
	   if ($settings->articles_indexable && $settings->enable_articles) {$output .= ArticlesIndex($page);}
	   if ($settings->forums_indexable && $settings->enable_forums) {$output .= ForumIndex($page);}
    } else {
	   $output = "";
	}
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">' . PHP_EOL .
'<html>' . PHP_EOL .
'<head>' . PHP_EOL .
  '<title>'.$settings->website_title.'</title>' . PHP_EOL .
  '<meta http-equiv="Content-type" content="text/html;charset=UTF-8" >' . PHP_EOL .
'</head>' . PHP_EOL .
'<body>';
    echo $output . PHP_EOL .     
'</body>' . PHP_EOL .
'</html>';
?> 