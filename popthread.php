<?php
/*OmegaBB 0.9.2*/
header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

include_once('config.php');
include_once('common.php');
include_once('uncommon.php');

$thread_id=GetParam($_REQUEST,'thread_id','');
$offset=GetParam($_REQUEST,'offset','');

echo GetThreadPage($thread_id,$offset);

?>