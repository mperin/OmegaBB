 <?php
/*OmegaBB 0.9.2*/
    include('config.php');
	include('common.php');
	
	$user_id=GetParam($_REQUEST,'user_id','');
    $thread_id=GetParam($_REQUEST,'thread_id','');
?>   

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html style="overflow-y:auto;">

<head>
  <title>Profile</title>
  
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8" >
  <meta name="Description" content="omegabb message board">
  <meta name="Keywords" content="message board imageboard ajax bbs">
    
  <link id="main_css" href="<?php echo $settings->default_theme ?>" type="text/css" rel="stylesheet"> 
  
  <script type="text/javascript" src="client_settings.php"></script>   
  <script type="text/javascript" src="js/prototype.js" ></script>
  <script type="text/javascript" src="js/omegabb.js" ></script>

  <link rel="icon" href="img/favicon.ico" type="image/png"> 

</head>

<?php 
   echo '<body style="height:200px;" onload=load_pt_mod_options(' . $user_id . ',' . $thread_id . ')>';
?>

<div id="small_popup_content">

  <div id="small_popup_title"><img src="img/indicator.gif"></div>  
  
  <div id="inner_small_popup_content"></div>   
  
</div>

</body>
</html>