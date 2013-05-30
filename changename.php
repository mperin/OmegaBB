<?php
	/*OmegaBB 0.9.3*/
    include('omegabb.php');
	
    $newusername=GetParam($_REQUEST,'newusername',1);

    $auth_ret = Check_Auth();

    if( $auth_ret <= 0) {
    	   echo "-1^?".intext("Not signed in");
    	   return;
    }   
    
    echo NewUsername($newusername, $auth_ret);
?> 