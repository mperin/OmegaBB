<?php
/*OmegaBB*/
header( 'Cache-control: no-cache' );
header( 'Cache-control: no-store' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' ); 

    include('omegabb.php');
	
    $post_id=GetParam($_REQUEST,'post_id','');
    $content_of_post=GetParam($_REQUEST,'content_of_post',1);
	
    $auth_ret = Check_Auth();
    
    if ($auth_ret <= 0) {
    	echo "-1^?".intext("Not signed in");
    	return;
    } 

    echo EditWiki($post_id, $content_of_post, $auth_ret);    
?> 