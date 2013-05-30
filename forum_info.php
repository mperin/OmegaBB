<?php
	/*OmegaBB 0.9.3*/
    include('omegabb.php');
	$forum_id=GetParam($_REQUEST,'forum_id','');   
	
    echo GetForumInfo($forum_id);
?> 