 <?php
	/*OmegaBB 0.9.2*/
    include('config.php');
    include('common.php');
?>   

<html style="overflow-y:auto;">

<head>
  <title><?php echo $settings->website_title ?></title>
  
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8" >
   
  <link id="main_css" href="<?php echo $settings->default_theme ?>" type="text/css" rel="stylesheet"> 
 
  <script type="text/javascript" src="client_settings.php"></script> 
  <script type="text/javascript" src="js/prototype.js" ></script>
  <script type="text/javascript" src="js/omegabb.js" ></script>
</head>

<body class="main_tab" onload="javascript:set_theme(parent.window.account_info.theme);">	
	<br>
	<?php echo "Example php file, see archive.php";?>
	<br>
	<br>

<?php	

if ($handle = opendir('archive')) {
    while ($file = readdir($handle)) {
        if ($file != "." && $file != "..") {
            $files[] = "<a href='archive/$file'>" . $file . "</a>&nbsp;&nbsp;&nbsp;" . date("d F Y", filemtime("archive/" . $file)) . "<br>";
        }
    }
    closedir($handle);
}

sort($files);

foreach ($files as $f) {
   echo $f;  
}	

?>	
	</body>
	
</html>
