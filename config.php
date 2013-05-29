<?php
/*OmegaBB*/
$settings = new StdClass;

//change these next four lines to your database configuration
$settings->server = "localhost";  
$settings->database = "name of database";
$settings->user = "database username";
$settings->pass = "database password";

//You can edit the rest of these variables in the site itself.  Sign in as admin and click on the Admin page.

//Basic settings
$settings->website_title = "Untitled";  
$settings->website_blurb = "";  //The text that appears at the bottom of the webpage, may be blank
$settings->website_url = "http://www.yourwebsite.xyz";  
$settings->welcome_thread = 453;  //All new users will automatically watch the thread specified here.  Set to 0 if you don't want one.
$settings->default_theme = "css/facebook.css";  //Default theme of your site.  For other choices, see the css directory
$settings->language = "en";  //Language.  See ./lang directory for info on translations.
$settings->time_zone = "America/Toronto";  //Example: "America/Los_Angeles", "Europe/London".  See http://www.php.net/manual/en/timezones.php for complete list
$settings->logo_image = "img/logo.jpg";  //Image file of the logo that appears in the top left corner
$settings->footer_text = "footer text here";  //The text that appears at the bottom of the webpage, may be blank
$settings->banner_space = 20; //Amount of vertical space given for the area between the top horizontal menu and the tabs.  
                              //After assigning more space here, you can insert html in the banner_area div in index.php and thread.php		
							  
//How users connect to your forum
$settings->connect_with_username = true;  //Set to 1 if you want users to connect by entering a username and password
$settings->connect_with_fb = false;  //Set to 1 if you want users to connect using their Facebook account
$settings->fb_appId = "";   //If you want users to connect using facebook you'll need to set up a Facebook app, see the file README.facebook for instructions,
$settings->fb_secret = "";  //When that's been set up, enter your appId and secret here.  
$settings->fb_references_needed = 0;  //This can be set to make your site more exclusive.  In order for a new member to join, they'll need this many local members who
                                      //are Facebook friends with them.  
$settings->minimum_status_of_fb_reference = 1;  //In order to be considered a reference, a local member must have a status level at least this high.
$settings->connect_with_linkedin = false;  //Set to 1 if you want users to connect using their LinkedIn account
$settings->linkedin_api_key = "";     //If you want users to connect using LinkedIn you'll need to set up a LinkedIn developer account, see the file README.linkedin for instructions,
$settings->linkedin_secret_key = "";  //when that's been set up, enter your API key and secret key.  
$settings->linkedin_request_connections = true;  //If set, during LinkedIn authentication the user will be asked to give their connections information in addition to their basic information.
                                                 //This only needs to be set if you're using linkedin_references_needed or fb_li_welcome_pt
$settings->linkedin_references_needed = 0;  //This can be set to make your site more exclusive.  In order for a new member to join, they'll need this many local members who
                                            //are LinkedIn contacts with them.  
$settings->minimum_status_of_linkedin_reference = 1;  //In order to be considered a reference, a local member must have a status level at least this high.
						  
//Site layout
$settings->first_tab_enabled = true;
$settings->first_tab_name = "Main";
$settings->first_tab_location = "main.html";
$settings->first_tab_is_div = true;
$settings->first_tab_indexable = true;
$settings->second_tab_enabled = true;
$settings->second_tab_name = "News";
$settings->second_tab_location = "news.html";
$settings->second_tab_is_div = true;
$settings->second_tab_indexable = true;
	
$settings->enable_articles = true;  //Articles are wikis that can be edited by the author at any time, and if the author chooses, by other members of the site.
$settings->articles_tab_name = "Articles";
$settings->articles_topic_name = "Articles";
$settings->articles_indexable = true;
	
$settings->enable_forums = true;  //Forums
$settings->total_forums = 9;  //Total number of forums, between 1 and 11.  
$settings->forums_per_tab = 3;  //Number of forums that appear on one tab, must be between 1 and 4				
$settings->forum_tab_names = array("Forum A","Forum B","Forum C","Forum D","Forum E","Forum F","Forum H","Forum I","Forum J","Forum K","Forum L");  
$settings->forum_topic_names = array("Topic 1","Topic 2","Topic 3","Topic 4","Topic 5","Topic 6","Topic 7","Topic 8","Topic 9","Topic 10","Topic 11");  			
$settings->forums_indexable = "true";

$settings->enable_private_threads = true;  //Private threads are used as private messaging between two or more members.  Note: no one, including moderators, are able to view the private threads they're not a member of.
                                           //A database administrator can see their contents from the database, however.
$settings->pt_tab_name = "Private";
$settings->pt_topic_name = "Private Threads";

$settings->second_last_tab_enabled = false; 
$settings->second_last_tab_name = "Extra";
$settings->second_last_tab_location = "archive.php";
$settings->second_last_tab_is_div = false;
$settings->second_last_tab_indexable = true;
$settings->last_tab_enabled = false;  
$settings->last_tab_name = "Extra 2";
$settings->last_tab_location = "README";
$settings->last_tab_is_div = false;
$settings->last_tab_indexable = true;

$settings->enable_helpmenu = true; //A drop-down menu that appears in the top right corner
$settings->helpmenu_name = "Help";
$settings->helpmenu1_enabled = true;  
$settings->helpmenu1_name = "FAQ";
$settings->helpmenu1_location = "faq.html";
$settings->helpmenu1_is_div = true;
$settings->helpmenu1_indexable = true;
$settings->helpmenu2_enabled = true;  
$settings->helpmenu2_name = "Status";
$settings->helpmenu2_location = "status.html";
$settings->helpmenu2_is_div = true;
$settings->helpmenu2_indexable = true;
$settings->helpmenu3_enabled = true;  
$settings->helpmenu3_name = "Terms Of Service";
$settings->helpmenu3_location = "tos.html";
$settings->helpmenu3_is_div = true;
$settings->helpmenu3_indexable = true;
$settings->helpmenu4_enabled = true;  
$settings->helpmenu4_name = "Contact";
$settings->helpmenu4_location = "contact.html";
$settings->helpmenu4_is_div = true;
$settings->helpmenu4_indexable = true;
$settings->helpmenu5_enabled = true;  
$settings->helpmenu5_name = "Emotes";
$settings->helpmenu6_enabled = true;  
$settings->helpmenu6_name = "About";
$settings->helpmenu6_location = "about.html";
$settings->helpmenu6_is_div = true;
$settings->helpmenu6_indexable = true;			  							  
							  
//The following options control which features are enabled on your installation
$settings->allow_rich_text = true;  //If set, a poster may use <b>, <i>, and <u> in posts.  
$settings->image_linking_allowed = true;  //If set, a user may put an embedded, off-site image into their post, ex: [IMG]http://www.foo.com/foo.jpg[/IMG].  
$settings->youtube_linking_allowed = true;  //If set, a user may embed a youtube video, ex: [YOUTUBE]DlkdtS8OFlA[/YOUTUBE].  
$settings->emotes_allowed = true;  //If set, a user can enter ":lol:" and it'll replace it with an image.  See the directory ./emotes.  
$settings->user_block_list = true;  //If set, a user may specify users that can never post in threads they start.  
$settings->word_filter = true;  //If set, word filter is active, see word_filter.php for the list
$settings->file_upload_allowed = true;  //If set, a user may upload a file attachment with their post.  
$settings->file_upload_in_pt_allowed = true;  ///If set, a user may upload a file attachment with their post in a private thread.  
$settings->allowed_file_types = array("txt","pdf","jpg","jpeg","png","gif");  //Allowed file types for upload
$settings->thumbnail_uploaded_images = true;  //If set, attached images will be thumbnailed.  
$settings->permalinks_enabled = true;  //If set, each public thread and article will have a permalink for easy retrieval, ex: "http://www.foo.com/thread.php?id=889".   
                                    //Must be enabled if bots_allowed is set. 
$settings->new_accounts_allowed = true;  //If you don't want to allow new accounts to be made, set this to 0.  
$settings->may_undelete = true;  //If set, a moderator may undelete soft-deleted posts or threads.  
$settings->post_approval = false; //If set, new users (status = 0) will need to have their postings approved by a moderator.  Note: does not apply to private threads

//Permissions based on status level.  0: new user, 1: regular user, 2: star user, 3: moderator, 5: administrator.
$settings->status_to_start_threads = 0;  //Users must have a status at least this high to start new threads.  
$settings->status_to_create_articles = 0;  //Users must have a status at least this high to create articles.  
$settings->status_to_upload_file = 0;  //Status needed to upload a file in your post.  
$settings->status_to_embed = 0;  //Status needed to be able to post [IMG][/IMG] and [YOUTUBE][/YOUTUBE].  
$settings->status_to_have_block_list = 1;  //Status needed to use block list.  
$settings->status_to_start_pt = 1;  //Status needed to start a private thread.  Note: anyone can be invited to a private thread, this only determines who may start one.  
$settings->status_to_have_avatar = 0;  //Minimum status needed to upload an avatar.  
$settings->status_to_hard_delete = 5;  //Deletions of posts or threads from moderators with this status level will be permanent deletions.  Set to either 3 (all moderators), 5 (admin only) or 6 (everyone soft deletes).  
                                       //Soft deletions cause the post or thread to remain in the database until it's been pruned (assuming you have the system prune active).  Note: file attachments are always hard deleted.

//Vanity Settings
$settings->avatars_allowed = true;  //If set, avatars are allowed on user profiles.  
$settings->animated_avatars = true;  //If set, animated gifs may be used as avatars, if 0, they won't animate.  Note: animated gifs must 80x80 or smaller to keep their animation
$settings->allow_username_change = true;  //If set, a user may change their username.  
$settings->allow_avatar_change = true;  //If set, a user may change their avatar.  
$settings->persistent_logo = false;  //If set, the corner logo will remain after a user logs in.  If set to 0, the corner logo is replaced with the user's avatar
$settings->new_user_avatar = 1; //0: no avatar, 1: use default avatar, 2: use identicon
$settings->max_avatar_dimensions = array(80,80); //Uploaded avatars will be resized if they're larger than these dimensions
$settings->avatars_same_size = true;  //If set, all avatars will have the dimentions of max_avatar_dimensions
$settings->user_info_permanentness = false;  //If set, when a user makes a posting, that post will always contain whatever their username and avatar were at that time, even if they
                                            //change it at a later time.  If set to 0, old postings will be updated if the user changes their name or avatar. 
$settings->profile_text_limit = 600; //Maximum number of characters allowed in a user's profile.  Set to 0 to not allow text in user's profiles.

//Privacy Settings
$settings->must_login_to_see_forum = false;  //If set, forums and articles will not be visible unless you're logged in
$settings->must_login_to_see_profile = false;  //Users must sign in to see a user's profile
$settings->allow_hotlinking = true; //to prevent other sites from hotlinking uploaded content, set this to false
$settings->strip_exif = true; //Digital cameras often insert extra information into the image, known as exif data, if set, this will strip out all exif data from uploaded jpeg images
$settings->truncate_name = 1; //Truncate name when users register from Facebook or LinkedIn.  0: don't truncate, 1: abbreviate last name, 2: keep first name only
$settings->status_to_see_fb_li_profile = 5;  //status needed to view any user's facebook or linkedin profile link on their local profile
$settings->fb_li_welcome_pt = true; //If set, when a user first connects with either LinkedIn or Facebook, a private thread containing local members who are also contacts with
                                    //with them on linkedin/facebook will be generated for them

//Cron Settings
$settings->auto_close_thread = array(0,0); //This is the option to cause old or inactive threads to automatically close.  The first number is the number of days after the thread was created, 
                                            //the second number is number of days the thread has been inactive.  Examples: Close thread 20 days after creation = array(20,0);    
                                            //Close thread after 3 days of inactivity = array(0,3);  Close thread after 3 days of inactivity and at least 20 days after creation = array(20,3);
                                            //To never auto-close threads, set this to array(0,0);						
$settings->prune_deleted_threads = true;  //If set, threads that have been set to state "deleted" will be removed from the database after at least 2 weeks, along with all of its posts and file attachments
$settings->prune_deleted_posts = true;  //If set, posts that have been set to state "deleted" will be removed from the database after at least 2 weeks
$settings->prune_watchlist = true;  //If set, threads that have been closed or deleted will be removed from a user's watchlist after at least a week.
$settings->prune_session_table = true; //Session rows that have been inactive for over a month will be deleted
$settings->prune_closed_threads = -1; //Closed threads will be deleted from the database after the number of days specified.  To never auto-delete closed threads set it to -1.
	                                  //Note: the system does this check once a week, so deletions won't happen exactly at the time specified									
$settings->prune_old_pt = 0;  //Old pts will be deleted after this number of months of inactivity, set to 0 to never delete old PTs.

//Gifts settings
$settings->gifts_enabled = true; //enable giving gifts to other users
$settings->allowance = true;     //each gift costs one credit, users with at least $allowance_status status level will receive $allowance_credits per month
$settings->allowance_status = 1;
$settings->allowance_credits = 1;
$settings->bonus = true;   //when a user's status is raised to $bonus_status, they'll immediately receive $bonus_credits credits
$settings->bonus_status = 1;
$settings->bonus_credits = 1;
$settings->max_credits = 3; //maximum number of credits a user may hold
$settings->unlimited_credits = 5; //users with at least this status level have an unlimited number of credits
$settings->deliver_credits = 5; //users with at least this status level can send any user credits

//Miscellaneous Settings
$settings->name_of_status_2 = "Star Member";  //This is the name of the status with a rank above "regular user" (1) but below "moderator" (3).  
$settings->new_account_limit = 1;  //This to the maximum number of accounts that can be created from one IP address.  If you don't want a limit, set it to -1
$settings->default_avatar = "img/default.jpg";  //Image file of the logo that appears in the top left corner
$settings->system_avatar = "img/system.jpg";  //Image file of the logo that appears in the top left corner
$settings->narrow_width = 1000;  //When the browser width is less than this, the side panel becomes a horizontal bar (if you prefer to always have the side panel be horizontal, set this to a large number)
$settings->thumb_width = 600;  //Images with a width greater than this will be thumbnailed
$settings->thumb_height = 250; //Images with a height greater than this will be thumbnailed
$settings->max_uploaded_file_size = 4000000;  //Maximum size of a file attached to a posting, in bytes
$settings->max_post_length = 10000;  //Number of characters a post can be.  Anything larger will return an error message.
$settings->new_account_captcha = true;  //A user will have to enter a captcha when creating a new account
$settings->new_user_post_captcha = false;  //A new user will have to enter a captcha at each post
$settings->weak_captcha = true;  //If set, the captcha will use a list of the 500 most common English words, if set to 0 it will be a random string
$settings->captcha_distortion = 1;  //How distorted the captcha appears, 1 = slight, 2 = moderate, 3 = extreme
$settings->flood_time = 60;  //The following two settings control flood attacks.  If more than $flood_num_posts posts happen in $flood_time seconds, then the post is rejected
$settings->flood_num_posts = 5;
$settings->update_frequency = 20;  //number of seconds between the update poll to the server
$settings->edit_time = 10;  //Number of minutes during which you can edit a post, set to 0 to never allow editing, set to -1 for an unlimited amount of time to edit
$settings->max_file_attachments = 4;  //Maximum number of file attachments in a post
$settings->max_username_length = 20;  //Maximum number of characters a username can be.  
$settings->posts_per_page = 25;  //Number of posts displayed in a thread page
$settings->img_url_whitelist = array();  //If set, urls from the domain names listed here will turn into inline images using [IMG][/IMG].  Example: $settings->img_url_blacklist = array("flickr.com","tumblr.com"); 
$settings->img_url_blacklist = array();  //If set, urls from the domain names listed here will not turn into inline images using [IMG][/IMG].  Example: $settings->img_url_blacklist = array("flickr.com","tumblr.com"); 	
$settings->datetime_format = "d-M-Y g:i a";  //Format of date and time, see http://php.net/manual/en/datetime.formats.date.php  There must always be a space after the date portion and no space before it.

//number of characters allowed in forum titles, articles titles and pt titles.  Since you're unlikely to ever need to change these settings they don't show up on the admin settings page.
if ($settings->forums_per_tab == 1) {$settings->size_of_thread_title = 130;}
else if ($settings->forums_per_tab == 2) {$settings->size_of_thread_title = 66;} 
else if ($settings->forums_per_tab == 3) {$settings->size_of_thread_title = 45;} 
else {$settings->size_of_thread_title = 34;}
$settings->size_of_pt_title = 75;  	
$settings->size_of_article_title = 130;
$settings->max_gift_msg_length = 90;
//€
?>