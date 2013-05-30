<?php
	/*OmegaBB 0.9.3*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	global $settings;
	
	$user_id=GetParam($_REQUEST,'user_id','');
    $blocked_user_list=GetParam($_REQUEST,'blocked_user_list',1);
	$blocknewusers=GetParam($_REQUEST,'blocknewusers','');
    $ptblocked_user_list=GetParam($_REQUEST,'ptblocked_user_list',1);
	$ptblocknewusers=GetParam($_REQUEST,'ptblocknewusers','');	
	$ptblockallusers=GetParam($_REQUEST,'ptblockallusers','');		
    
	$auth_ret = Check_Auth();

    if (($auth_ret <= 0) || ($user_id != $auth_ret)) {
    	echo "-1^?".intext("Not signed in");
    	return;
    }   
	if ($settings->status_to_have_block_list > GetStatus($user_id) ) {
	    echo "-1^?".intext("Status not high enough");
    	return;
	}
	if ($settings->user_block_list == 0) { 	    
	    echo "-1^?".intext("Feature disabled");
    	return;
	}
	
    echo SaveBlockList($user_id,$blocked_user_list,$blocknewusers,$ptblocked_user_list,$ptblocknewusers,$ptblockallusers);   
?> 