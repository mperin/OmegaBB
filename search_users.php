<?php
/*OmegaBB*/
header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

    include('config.php');
    include('common.php');
    include('uncommon.php');
	
	$q=GetParam($_REQUEST,'q','');
    echo UserQuery($q);   
?>