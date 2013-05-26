<?php
	/*OmegaBB*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	
    $name=GetParam($_REQUEST,'name','');
    $user_id=GetParam($_REQUEST,'user_id','');
    $msg=GetParam($_REQUEST,'msg',1);

    $auth_ret = Check_Auth();
    
    if ($auth_ret <= 0) {
	    echo "-1^?".intext("Not signed in");
	    return;
    } 
   
    echo SendGift($auth_ret, $user_id, $name, $msg);
?>