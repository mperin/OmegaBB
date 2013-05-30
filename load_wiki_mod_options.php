<?php
	/*OmegaBB 0.9.3*/

	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');

    $user_id=GetParam($_REQUEST,'user_id','');
    $thread_id=GetParam($_REQUEST,'thread_id','');
    $page=GetParam($_REQUEST,'page','');
    $post_position=GetParam($_REQUEST,'post_position','');
	$type=GetParam($_REQUEST,'type','');
	
    echo WikiModOptions($user_id,$thread_id,$page,$post_position);
?>