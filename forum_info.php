<?php
	/*OmegaBB*/
    include('omegabb.php');
	$forum_id=GetParam($_REQUEST,'forum_id','');   
	
    echo GetForumInfo($forum_id);
?> 