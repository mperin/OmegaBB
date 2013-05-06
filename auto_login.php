<?php
	/*OmegaBB 0.9.2*/

    include('omegabb.php');

    $temp_string = AutoLogin();
    $temp_array = explode("^?",$temp_string);
	
    if ($temp_array[0] == 1){
       $message = 'Login correct^?' . $temp_array[1];
       $user_info = GetInfo($temp_array[2]);         
       $user_info .= "^?" . $message;
       echo $user_info;   
    } else {
       $message = $temp_array[0] . "^?" . $temp_array[1];
       echo $message;
    }
   
?>