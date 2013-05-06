<?php
	/*OmegaBB 0.9.2*/
    include('config.php');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<title><?php echo $settings->website_title ?></title>
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

	<body onload="load_site(-1,-1)">
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
				<img src="img/indicator.gif">
			</div>
			<div id="bottombar"></div>
			<div id="inputdiv"></div>
		</div>
		<div id="Panel"></div>
		<a href='fullindex.php?page=0'>index</a>
	</body>
</html>