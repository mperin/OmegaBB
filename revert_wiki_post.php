<?php
	/*OmegaBB 0.9.3*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');

    $msg_id=GetParam($_REQUEST,'msg_id','');
    $thread_id=GetParam($_REQUEST,'thread_id','');	
    $revision=GetParam($_REQUEST,'revision','');

    $user_id = Check_Auth();
    
    if ($user_id <= 0) {
    	echo "-1^?".intext("Not signed in");
    	return;
    } 
			
    echo RevertWikiPost($msg_id, $thread_id, $revision,$user_id);    	
?> 