<?php
	/*OmegaBB*/

	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');

    $thread_id=GetParam($_REQUEST,'thread_id','');
	
    echo ThreadModOptions($thread_id);
?> 