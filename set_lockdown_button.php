<?php
/*OmegaBB*/
header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

    include('omegabb.php');
	
	$code=GetParam($_REQUEST,'code','');
    $expire_time=GetParam($_REQUEST,'expire_time','');
	$msg=GetParam($_REQUEST,'msg','');
	$turn_off=GetParam($_REQUEST,'turn_off','');
	
	$auth_ret = Check_Auth();
    if (!isMod($auth_ret)) {
    	echo "-1^?".intext("user not a moderator");
    	return;
    }   
	
	if ($turn_off) {echo UnsetLockdownButton(); return;}
	
	if (!isAdmin($auth_ret) && ($code > 7 || $expire_time == "i") ) {
    	echo "-1^?".intext("You must be an administrator to do this");
    	return;
    }   
	
    echo SetLockdownButton($auth_ret,$code,$expire_time,$msg);           		
?>