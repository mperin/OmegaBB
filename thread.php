 <?php
/*OmegaBB 0.9.2*/

	include('config.php');
    include('common.php');
	include('uncommon.php');
	
    global $settings;
    
	$thread_id=GetParam($_REQUEST,'id','');
    $page_num=GetParam($_REQUEST,'page','');	

	if ($page_num == "") { $page_num = 0;} 
	$page_is_div = -1;
	
	if (!lockdown_button_check(SITEDOWN)) {
	    if(is_numeric($thread_id)) { 
		    $row = perform_query("select forum_id from thread where thread_id=$thread_id",SELECT);
			if ($row->forum_id == 12) {
			    $output = "";
				$thread_title = "";
			} elseif ((($row->forum_id == 13) && !$settings->enable_articles) || (($row->forum_id == 13) && !$settings->articles_indexable)) {
			    $output = "";
				$thread_title = "";
			} elseif (!$settings->enable_forums || !$settings->forums_indexable) {
			    $output = "";
				$thread_title = "";
		    } elseif (!isValidThread($thread_id)) {
			    $output = "";
				$thread_title = "";
			} else {
				if ($page_num != 0) {
				   $output = GetThreadPage($thread_id,(string) ($page_num - 1),1);
				   $thread_title = GetThreadTitle($thread_id) . " : ";
				} else {
				   $output = GetThreadPage($thread_id,"0",1);
				   $thread_title = GetThreadTitle($thread_id). " : ";
				}
			}
		} else {
		    //If it's not a number, then thread_id is being used as the filename that's being displayed, ex: main.html
		    $is_indexable = array($settings->first_tab_location => $settings->first_tab_indexable,
			$settings->second_tab_location => $settings->second_tab_indexable,
			$settings->helpmenu1_location => $settings->helpmenu1_indexable,
			$settings->helpmenu2_location => $settings->helpmenu2_indexable,
			$settings->helpmenu3_location => $settings->helpmenu3_indexable,
			$settings->helpmenu4_location => $settings->helpmenu4_indexable,	
			$settings->helpmenu6_location => $settings->helpmenu6_indexable,
			$settings->second_last_tab_location => $settings->second_last_tab_indexable,
			$settings->last_tab_location => $settings->last_tab_indexable
			);	
			
		    $is_div = array($settings->first_tab_location => $settings->first_tab_is_div,
			$settings->second_tab_location => $settings->second_tab_is_div,
			$settings->helpmenu1_location => $settings->helpmenu1_is_div,
			$settings->helpmenu2_location => $settings->helpmenu2_is_div,
			$settings->helpmenu3_location => $settings->helpmenu3_is_div,
			$settings->helpmenu4_location => $settings->helpmenu4_is_div,	
			$settings->helpmenu6_location => $settings->helpmenu6_is_div,
			$settings->second_last_tab_location => $settings->second_last_tab_is_div,
			$settings->last_tab_location => $settings->last_tab_is_div
			);	
			
		    $is_enabled = array($settings->first_tab_location => $settings->first_tab_enabled,
			$settings->second_tab_location => $settings->second_tab_enabled,
			$settings->helpmenu1_location => $settings->helpmenu1_enabled & $settings->enable_helpmenu,
			$settings->helpmenu2_location => $settings->helpmenu2_enabled & $settings->enable_helpmenu,
			$settings->helpmenu3_location => $settings->helpmenu3_enabled & $settings->enable_helpmenu,
			$settings->helpmenu4_location => $settings->helpmenu4_enabled & $settings->enable_helpmenu,	
			$settings->helpmenu6_location => $settings->helpmenu6_enabled & $settings->enable_helpmenu,
			$settings->second_last_tab_location => $settings->second_last_tab_enabled,
			$settings->last_tab_location => $settings->last_tab_enabled
			);				

			if ($is_indexable[$thread_id] && $is_div[$thread_id] && $is_enabled[$thread_id]) {
		       $output = file_get_contents($thread_id);
			} else if ($is_indexable[$thread_id] && $is_enabled[$thread_id]) {
			   $output = '<iframe id="frontpageiframe" frameborder="" style="height: 600px;width: 100%;" src="'.$thread_id.'"></iframe>' ;  
			} else {
			   $output = "";
			   $thread_id = "";
			}
			
			$page_is_div = (int) $is_div[$thread_id];
		}
	} else {
       $output = "";
	   $thread_title = "";
	}
	
	if ($settings->permalinks_enabled == 0) {
	   $output = "";
	   $thread_id= "";
	}
?>   

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
  <title><?php echo $thread_title .  $settings->website_title?></title>
  
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8" >
  <meta name="Description" content="omegabb">
  <meta name="Keywords" content="omegabb">
   
  <link id="main_css" href="<?php echo $settings->default_theme ?>" type="text/css" rel="stylesheet"> 

  <link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />
  <link rel="stylesheet" type="text/css" href="css/jquery.fancybox-1.3.1.css" media="screen" />  
    	
  <script type="text/javascript" src="client_settings.php"></script>
  <script type="text/javascript" src="js/prototype.js"></script>
  <script type="text/javascript" src="js/omegabb.js"></script>
  
  <script type="text/javascript" src="js/jquery-1.6.4.min.js"></script>
  <script type="text/javascript" src="js/jquery.bgiframe.min.js"></script>
  <script type="text/javascript" src="js/jquery.ajaxQueue.js"></script>
  <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
  <script type="text/javascript" src="js/jquery.fancybox-1.3.1.js"></script>
	  
  <script type="text/javascript">
    jQuery.noConflict();
    jQuery().ready(function() { });  
  </script>

  <link rel="icon" href="img/favicon.ico" type="image/png"> 

</head>

<?php 
	if ($thread_id != "") {
	   if (!$page_num) { $page_num = 0; } else {$page_num--;}
	   echo '<body onload="load_site(\'' . $thread_id . '\',' . $page_num . ',' . $page_is_div . ')">';
	} else {
	   echo '<body onload="load_site(-1,-1,-1)">';
	}   
?>		
	<div id="obb_header">
		<div id="container">
		   <div id="menu"></div>
		 </div>
		 <div id="banner_area"></div>
		 <div id="tabs"></div>
	</div>
	<div id="Content">
	  <div id="debug_area" STYLE="display:none;"> 
		<textarea class="console" id="result" cols=90 rows=5></textarea> 
	  </div>
	  <div id="top_area"></div>  
	  <div id="midrow"></div>
	  <div id="topbar"></div>
	  <div id="content_area">  
		  <?php echo "<h2>" . $thread_title . $settings->website_title . "</h2>"?>
		  <?php echo $output?>
	  </div>   
	  <div id="menu"></div>    	  
	  <div id="bottombar"></div>
	  <div id="inputdiv"></div>
	</div>
	<div id="Panel"></div>
    <a href='fullindex.php?page=0'>index</a>
</body>
</html>
