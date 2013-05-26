<?php
	/*OmegaBB*/
    include('config.php');
	include('common.php');
?>
	
var settings = new Object();
settings.website_title = "<?php echo $settings->website_title?>";  
settings.website_blurb = "<?php echo $settings->website_blurb?>";  
settings.website_url = "<?php echo $settings->website_url?>";  
settings.logo_image = "<?php echo $settings->logo_image?>";  
settings.footer_text = "<?php echo $settings->footer_text?>";  
settings.language = "<?php echo $settings->language?>"; 
settings.connect_with_username = <?php var_export($settings->connect_with_username)?>; 
settings.connect_with_fb = <?php var_export($settings->connect_with_fb)?>; 
settings.connect_with_linkedin = <?php var_export($settings->connect_with_linkedin)?>; 
settings.enable_articles = <?php echo var_export($settings->enable_articles)?>;  
settings.enable_forums = <?php echo var_export($settings->enable_forums)?>;
settings.enable_private_threads = <?php echo var_export($settings->enable_private_threads)?>; 					 
settings.allow_rich_text = <?php echo var_export($settings->allow_rich_text)?>;  
settings.image_linking_allowed = <?php echo var_export($settings->image_linking_allowed)?>; 
settings.youtube_linking_allowed = <?php echo var_export($settings->youtube_linking_allowed)?>; 
settings.user_block_list = <?php echo var_export($settings->user_block_list)?>; 
settings.avatars_allowed = <?php echo var_export($settings->avatars_allowed)?>; 
settings.allow_username_change = <?php echo var_export($settings->allow_username_change)?>;  
settings.allow_avatar_change = <?php echo var_export($settings->allow_avatar_change)?>;  
settings.file_upload_allowed = <?php echo var_export($settings->file_upload_allowed)?>;  
settings.file_upload_in_pt_allowed = <?php echo var_export($settings->file_upload_in_pt_allowed)?>;  		
settings.allowed_file_types = [<?php foreach ($settings->allowed_file_types as $value) {echo "\"".$value."\",";}?>];					 
settings.thumbnail_uploaded_images = <?php echo var_export($settings->thumbnail_uploaded_images)?>;  
settings.permalinks_enabled = <?php echo var_export($settings->permalinks_enabled)?>;  
settings.may_undelete = <?php echo var_export($settings->may_undelete)?>;  
settings.new_accounts_allowed = <?php echo var_export($settings->new_accounts_allowed)?>;  	
settings.post_approval = <?php echo var_export($settings->post_approval)?>;  	
settings.must_login_to_see_forum = <?php echo var_export($settings->must_login_to_see_forum)?>; 
settings.status_to_start_threads = <?php echo $settings->status_to_start_threads?>;  
settings.status_to_create_articles = <?php echo $settings->status_to_create_articles?>;  
settings.status_to_upload_file = <?php echo $settings->status_to_upload_file?>; 
settings.status_to_embed = <?php echo $settings->status_to_embed?>; 
settings.status_to_have_block_list = <?php echo $settings->status_to_have_block_list?>; 
settings.status_to_start_pt = <?php echo $settings->status_to_start_pt?>; 
settings.status_to_have_avatar = <?php echo $settings->status_to_have_avatar?>; 
settings.forum_tab_names = [<?php foreach ($settings->forum_tab_names as $name) {echo "\"$name\",";}?>];
settings.name_of_status_2 = "<?php echo $settings->name_of_status_2?>";    
settings.default_avatar = "<?php echo $settings->default_avatar?>";  
settings.max_avatar_dimensions = [<?php foreach ($settings->max_avatar_dimensions as $value) {echo $value.",";}?>];
settings.system_avatar = "<?php echo $settings->system_avatar?>";  	   
settings.total_forums = <?php echo $settings->total_forums?>;  
settings.forums_per_tab = <?php echo $settings->forums_per_tab?>;  	   
settings.size_of_thread_title = <?php echo $settings->size_of_thread_title?>;  
settings.new_user_avatar = <?php echo $settings->new_user_avatar?>;  
settings.banner_space = <?php echo $settings->banner_space?>;  						 
settings.persistent_logo = <?php echo var_export($settings->persistent_logo)?>;  
settings.avatars_same_size = <?php echo var_export($settings->avatars_same_size)?>;  
settings.update_frequency = <?php echo $settings->update_frequency?>; 
settings.new_account_captcha = <?php echo var_export($settings->new_account_captcha)?>;  
settings.new_user_post_captcha = <?php echo var_export($settings->new_user_post_captcha)?>; 
settings.size_of_article_title = <?php echo $settings->size_of_article_title?>;  
settings.size_of_pt_title = <?php echo $settings->size_of_pt_title?>;  
settings.narrow_width = <?php echo $settings->narrow_width?>;  
settings.max_username_length = <?php echo $settings->max_username_length?>;  
settings.user_info_permanentness = <?php echo var_export($settings->user_info_permanentness)?>;  
settings.profile_text_limit = <?php echo var_export($settings->profile_text_limit)?>;  
settings.pt_tab_name = "<?php echo $settings->pt_tab_name?>";  	 
settings.articles_tab_name = "<?php echo $settings->articles_tab_name?>";  	 
settings.first_tab_enabled = <?php echo var_export($settings->first_tab_enabled)?>;  
settings.first_tab_name = "<?php echo $settings->first_tab_name?>";  	 
settings.first_tab_location = "<?php echo $settings->first_tab_location?>";  	 
settings.first_tab_is_div = <?php echo var_export($settings->first_tab_is_div)?>;  
settings.second_tab_enabled = <?php echo var_export($settings->second_tab_enabled)?>;  
settings.second_tab_name = "<?php echo $settings->second_tab_name?>";  	 
settings.second_tab_location = "<?php echo $settings->second_tab_location?>";  	 
settings.second_tab_is_div = <?php echo var_export($settings->second_tab_is_div)?>;  
settings.second_last_tab_enabled = <?php echo var_export($settings->second_last_tab_enabled)?>;  
settings.second_last_tab_name = "<?php echo $settings->second_last_tab_name?>";  	 
settings.second_last_tab_location = "<?php echo $settings->second_last_tab_location?>";  	 
settings.second_last_tab_is_div = <?php echo var_export($settings->second_last_tab_is_div)?>;  
settings.last_tab_enabled = <?php echo var_export($settings->last_tab_enabled)?>;  
settings.last_tab_name = "<?php echo $settings->last_tab_name?>";  	 
settings.last_tab_location = "<?php echo $settings->last_tab_location?>";  	 
settings.last_tab_is_div = <?php echo var_export($settings->last_tab_is_div)?>;  
settings.enable_helpmenu = <?php echo var_export($settings->enable_helpmenu)?>;  
settings.helpmenu1_enabled = <?php echo var_export($settings->helpmenu1_enabled)?>;  
settings.helpmenu_name = "<?php echo $settings->helpmenu_name?>";
settings.helpmenu1_name = "<?php echo $settings->helpmenu1_name?>";  	 
settings.helpmenu1_location = "<?php echo $settings->helpmenu1_location?>";  	 
settings.helpmenu1_is_div = <?php echo var_export($settings->helpmenu1_is_div)?>;  
settings.helpmenu2_enabled = <?php echo var_export($settings->helpmenu2_enabled)?>;  
settings.helpmenu2_name = "<?php echo $settings->helpmenu2_name?>";  	 
settings.helpmenu2_location = "<?php echo $settings->helpmenu2_location?>";  	 
settings.helpmenu2_is_div = <?php echo var_export($settings->helpmenu2_is_div)?>;  
settings.helpmenu3_enabled = <?php echo var_export($settings->helpmenu3_enabled)?>;  
settings.helpmenu3_name = "<?php echo $settings->helpmenu3_name?>";  	 
settings.helpmenu3_location = "<?php echo $settings->helpmenu3_location?>";  	 
settings.helpmenu3_is_div = <?php echo var_export($settings->helpmenu3_is_div)?>;  
settings.helpmenu4_enabled = <?php echo var_export($settings->helpmenu4_enabled)?>;  
settings.helpmenu4_name = "<?php echo $settings->helpmenu4_name?>";  	 
settings.helpmenu4_location = "<?php echo $settings->helpmenu4_location?>";  	 
settings.helpmenu4_is_div = <?php echo var_export($settings->helpmenu4_is_div)?>;  
settings.helpmenu5_enabled = <?php echo var_export($settings->helpmenu5_enabled)?>;  
settings.helpmenu5_name = "<?php echo $settings->helpmenu5_name?>";  	
settings.helpmenu6_enabled = <?php echo var_export($settings->helpmenu6_enabled)?>;  
settings.helpmenu6_name = "<?php echo $settings->helpmenu6_name?>";  	 
settings.helpmenu6_location = "<?php echo $settings->helpmenu6_location?>";  	 
settings.helpmenu6_is_div = <?php echo var_export($settings->helpmenu6_is_div)?>;  
settings.max_gift_msg_length = <?php echo var_export($settings->max_gift_msg_length)?>;  
settings.site_down = false; 
settings.site_down_msg = ""; 

<?php 
	if ($info = lockdown_button_check(SITEDOWN)) {
		$sysinfo = explode("^?",$info);
		if ($sysinfo[1]) {
		   echo "settings.site_down_msg = \"". $sysinfo[1]."\";";
		} 
		echo "settings.first_tab_enabled = false;\n";
		echo "settings.second_tab_enabled = false;\n";  
		echo "settings.second_last_tab_enabled = false;\n";
		echo "settings.last_tab_enabled = false;\n";		
		echo "settings.enable_articles = false;\n";
		echo "settings.enable_forums = false;\n";
		echo "settings.enable_private_threads = false;\n";
		echo "settings.enable_helpmenu = false;\n";
		echo "settings.site_down = true;\n";
	}
	if (lockdown_button_check(NEWUSERCAPTCHA)) {
	    echo "settings.new_user_post_captcha = true;\n";
	}
?>

var language_hash = new Array();
if (settings.language != "en") {
   var src = 'lang/language.'+settings.language+'.js';
   var head = document.getElementsByTagName('head')[0];
   var script = document.createElement('script');
   script.setAttribute('type', 'text/javascript');
   script.setAttribute('src', src);
   head.appendChild(script);   
}
