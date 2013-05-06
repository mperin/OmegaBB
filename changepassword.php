<?php
	/*OmegaBB 0.9.2*/
    include('omegabb.php');
	
    $oldpass=GetParam($_REQUEST,'oldpass',2);
	$newpass0=GetParam($_REQUEST,'newpass0',2);
	$newpass1=GetParam($_REQUEST,'newpass1',2);

    $auth_ret = Check_Auth();

    if( $auth_ret <= 0) {
    	   echo "-1^?".intext("Not signed in");
    	   return ;
    }   
	
    echo NewPassword($auth_ret, $oldpass, $newpass0, $newpass1);		
?> 