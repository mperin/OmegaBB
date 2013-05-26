<?php
/*OmegaBB*/
header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

	include('config.php');
    include('common.php');
	include('uncommon.php');

    $user_id=GetParam($_REQUEST,'user_id','');
    $page=GetParam($_REQUEST,'page','');
	
    echo GiftScroll($user_id, $page);    
?> 
