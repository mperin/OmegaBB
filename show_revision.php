<?php
/*OmegaBB 0.9.2*/

header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

    include('config.php');
	include('common.php');
	include('uncommon.php');
	
    $thread_id=GetParam($_REQUEST,'thread_id','');
    $revision=GetParam($_REQUEST,'revision','');
	$total=GetParam($_REQUEST,'total','');

    echo ShowRevision($thread_id, $revision, $total);
?> 