<?php
	/*OmegaBB 0.9.3*/

	header( 'Cache-control: no-cache' );
	header( 'Cache-control: no-store' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' ); 

    include('omegabb.php');
	
    $action=GetParam($_REQUEST,'action','');
    $current_forum_id=GetParam($_REQUEST,'current_forum_id','');
	$total_forums=GetParam($_REQUEST,'total_forums','');	
    	
	if ($action == "get_forum_names") {
	   echo GetForumNames($current_forum_id,$total_forums);
	   return;
    }    		
?> 