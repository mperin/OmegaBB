 <?php
/*OmegaBB 0.9.2*/
    include('config.php');
    include('common.php');
	
    $user_id=GetParam($_REQUEST,'user_id','');
    $ip=GetParam($_REQUEST,'ip','');
    $fb=GetParam($_REQUEST,'fb','');	
?>   

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html style="overflow-y:auto;">

<head>
  <title>Profile</title>
  
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8" >
  <meta name="Description" content="omegabb">
  <meta name="Keywords" content="omegabb">
    
  <link id="main_css" href="<?php echo $settings->default_theme ?>" type="text/css" rel="stylesheet"> 
  
  <script type="text/javascript" src="client_settings.php"></script>   
  <script type="text/javascript" src="js/prototype.js" ></script>
  <script type="text/javascript" src="js/omegabb.js" ></script>

  <link rel="icon" href="img/favicon.ico" type="image/png"> 

</head>

<?php 
if (isset ($user_id)) {
   echo '<body style="overflow:hidden;" onload="load_profile_page(' . $user_id . ')">';
} else if (isset ($ip)) {
   echo '<body style="overflow:hidden;" onload="load_ipaddr_page(\'' . $ip . '\')">';
} else {
   echo '<body style="overflow:hidden;" onload="load_fb_page(\'' . $fb . '\')">';
}
?>

<div id="profile_content">

  <div id="profile_title"><img src="img/indicator.gif"></div>  
  
  <div id="inner_profile_content"></div>   

</div>

</body>
</html>


