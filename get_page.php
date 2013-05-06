<?php
	/*OmegaBB 0.9.2*/
    include('omegabb.php');
	
    $page=GetParam($_REQUEST,'page','');
	$forum_id=GetParam($_REQUEST,'forum_id','');
    
    echo GetPage($page, $forum_id);
?> 