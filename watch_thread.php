<?php
	/*OmegaBB 0.9.3*/
	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	
	$thread_id=GetParam($_REQUEST,'thread_id','');
    $total_posts=GetParam($_REQUEST,'total_posts','');
    
	$auth_ret = Check_Auth();

    if( $auth_ret <= 0) {
       echo "-1^?".intext("You must be signed in to watch threads");
       return;
    }
	
    echo WatchThread($thread_id,$total_posts);   
?> 