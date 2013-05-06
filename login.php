<?php
	/*OmegaBB 0.9.2*/
    include("captcha/securimage.php");
    include('omegabb.php');
	
    $username=GetParam($_REQUEST,'username','');
    $password=GetParam($_REQUEST,'password','');
	$rem=GetParam($_REQUEST,'rem','');

    Logout();  
	  
	if (($username  == "") || ($password == "")) {            
	   echo "-1^?".intext("Missing login information");
	   return;
	}	
	
	$temp_string = Login($username,$password,$rem);
	$temp_array = explode("^?",$temp_string);
	if ($temp_array[0] == 1){
	  echo GetInfo($username);   
	} else {
	  echo $temp_array[0] . "^?" . $temp_array[1];
	}
?>