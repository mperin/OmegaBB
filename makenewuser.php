<?php
	/*OmegaBB 0.9.3*/
    include("captcha/securimage.php");
    include('omegabb.php');	
    $newuser=GetParam($_REQUEST,'newuser','');
    $newpassword0=GetParam($_REQUEST,'newpassword0','');
    $newpassword1=GetParam($_REQUEST,'newpassword1','');
    $captcha=GetParam($_REQUEST,'captcha','');

    Logout();  
	  
	if ($newpassword0 != $newpassword1) {
	  echo "-2^?".intext("Passwords don't match");
	  return;
	}		

	if ($settings->new_account_captcha) {
	   $img = new Securimage();
	   $valid = $img->check($captcha);
	   if (!$valid) {
		  echo "-2^?".intext("Bad captcha");
		  return;
	   }
	}

    $newuser = trim($newuser);

	$temp_string = NewUser($newuser,$newpassword0);
	$temp_array = explode("^?",$temp_string);
	if ($temp_array[0] != 1) {
	   echo $temp_string;
	   return;
	}

	$temp_string = Login($newuser,$newpassword0,true);
	$temp_array = explode("^?",$temp_string);
	if ($temp_array[0] == 1){
	  echo GetInfo($newuser);
	} else {
	  echo $temp_array[0] . "^?" . $temp_array[1];
	}

?>