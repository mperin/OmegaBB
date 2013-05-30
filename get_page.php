<?php
	/*OmegaBB 0.9.3*/
	include('config.php');
    include('common.php');
	include('uncommon.php');
	
    $page=GetParam($_REQUEST,'page','');
	$forum_id=GetParam($_REQUEST,'forum_id','');
    
    echo GetPage($page, $forum_id);
?> 