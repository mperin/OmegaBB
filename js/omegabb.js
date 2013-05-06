/* 
OmegaBB 0.9.2 (build 215)  Copyright (c) 2013, Ryan Smiderle.  All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted
provided that the following conditions are met:

    * Redistributions of source code must retain the above product name, version number, 
	copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above product name, version number,  
	copyright notice, this list of conditions and the following disclaimer in the documentation 
	and/or other materials provided with the distribution.
    * Neither the name of OmegaBB nor the names of its contributors may be used to endorse or
	promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR 
IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND 
FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR 
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL 
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT 
OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

var globals = new Object();
globals.debug = 0;
globals.pe = "";
globals.newly_read_threads = "";
globals.is_connected = false;
globals.attempting_auto_login = false;
globals.current_forum = -1;
globals.current_page_of_thread = "";
globals.thread_watching = 0;
globals.number_of_posts_displayed = 0;
globals.current_forum_tab = "None";
globals.temp_string = "";
globals.temp_number = 0;
globals.pagenumbar_expanded = 0;   
globals.get_thread_page_done = 0;
globals.quoted_text = "";  
globals.last_wiki_revision = 0;  
globals.wiki_msg_id = 0;  
globals.wiki_revision = 0;  
globals.content_left = "180px";
globals.content_height = "";		
globals.content_top = (79 + settings.banner_space) + "px";
globals.narrow_width = 0;
globals.footer_is_displayed = 0;
globals.current_width = 0;
globals.post_mutex = 0;  
globals.login_mutex = 0; 
globals.highest_post_id = 0;  
globals.highest_post_in_thread = 0;
globals.updater_mutex = 0;
globals.highest_thread_in_forums = 0;
globals.hitf_semaphore = 0;  
globals.popforum_mutex = 0;
globals.poppt_mutex = 0;
globals.temp_settings = new Hash();

var account_info = new Object();
account_info.user_id = 0;
account_info.username = "";
account_info.status = -1;
account_info.theme = "";
account_info.avatar_number = 0;
account_info.total_avatars = 0;
account_info.my_threads = "";

//message center hashes
var mc_title_hash = new Hash();
var mc_num_posts_hash = new Hash();
var mythreads_hash = new Hash();

var cache = new Object();
cache.forum_is_set = 0;
cache.pt_is_set = 0;
cache.forum_title = new Array(13);  
cache.forum_id = new Array(13); 
cache.thread_title = new Array(13); 
cache.thread_id = new Array(13);
cache.thread_state = new Array(13);

for (i = 0; i < 13; i++) {
   cache.thread_title[i] = new Array(11);
   for (j = 0; j < 13; j++) {
      cache.thread_title[i][j] = "&nbsp";
   }
}
for (i = 0; i < 13; i++) {
   cache.thread_id[i] = new Array(11);
   for (j = 0; j < 13; j++) {
      cache.thread_id[i][j] = 0;
   }
}
for (i = 0; i < 13; i++) {
   cache.thread_state[i] = new Array(11);
   for (j = 0; j < 13; j++) {
      cache.thread_state[i][j] = 0;
   }
}

//each forum topic has scroll arrows, this keeps track of what page you've scrolled to 
cache.current_page = new Array(13);  
for (i = 0; i < 13; i++) {
   cache.current_page[i] = 0;
}

//each forum topic displays a block of ten posts, as you scroll through them with the up/down arrows, it will 
//call add_page_to_cache and set its position to this array 1
cache.is_stored = new Array(13);  
for (i = 0; i < 13; i++) {
   cache.is_stored[i] = new Array();
}
   
function load_site(thread_id,page_num,page_is_div) {
	document.title = settings.website_title;
    show_tabs();
	show_login_panel(); 
	fill_help_button();
		
    if (document.cookie.indexOf("sessioncookie=") != -1) {
       auto_login();
    } 
	
    if (thread_id != -1){   
	   if (!isNaN(thread_id)) {
          get_thread_page(thread_id,page_num,0);
	   } else {
	      show_frame(thread_id,page_is_div);
	   }
    } else {
	   if (settings.first_tab_enabled) {
	      show_frame(settings.first_tab_location,settings.first_tab_is_div);
	   } else if (settings.second_tab_enabled) {
	      show_frame(settings.second_tab_location,settings.second_tab_is_div);
	   } else if (settings.enable_forums) {
		  get_tab("forum"+ (6 - Math.ceil((settings.total_forums / settings.forums_per_tab)))); 
       } else if (settings.enable_articles) {
	      get_tab("articles");
	   } else if (settings.enable_private_threads) {
	      get_tab("pt");  
	   } else if (settings.second_last_tab_enabled) {
	      show_frame(settings.second_last_tab_location,settings.second_last_tab_is_div);
	   } else if (settings.last_tab_enabled) {
	      show_frame(settings.last_tab_location,settings.last_tab_is_div);
	   } else if (settings.site_down) {
		  set_display("top_area:none","midrow:none","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
		  $('content_area').setAttribute("style","display:block;width:1000px;max-width:1000px;");
		  $('content_area').innerHTML = settings.site_down_msg;
	   } else {
		  set_display("top_area:none","midrow:none","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
		  $('content_area').setAttribute("style","display:block;width:1000px;max-width:1000px;");
		  $('content_area').innerHTML = intext("No features enabled, check configuration"); 
	   }
    }

	resize_site();
}
	
function fill_help_button() {
    if (!settings.enable_helpmenu) {return;}
	$('menu').innerHTML = '<ul id="sddm">' +
		'<li><a onmouseover="mopen(\'m1\')" onmouseout="mclosetime()">'+settings.helpmenu_name+'</a>' +
			'<div id="m1" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">' +
			   help_submenu() +
			'</div>' +
		'</li>' +
		'<li><a></a></li>' +	
	'</ul>';
}
	
function help_submenu() {
	var acontent = new Array(6);
	var return_string = "";

	if (settings.helpmenu1_enabled) {return_string += '<a onclick="javascript:show_frame(\''+settings.helpmenu1_location+'\','+settings.helpmenu1_is_div+')">'+settings.helpmenu1_name+'</a>';}	
	if (settings.helpmenu2_enabled) {return_string += '<a onclick="javascript:show_frame(\''+settings.helpmenu2_location+'\','+settings.helpmenu2_is_div+')">'+settings.helpmenu2_name+'</a>';}
	if (settings.helpmenu3_enabled) {return_string += '<a onclick="javascript:show_frame(\''+settings.helpmenu3_location+'\','+settings.helpmenu3_is_div+')">'+settings.helpmenu3_name+'</a>';}
	if (settings.helpmenu4_enabled) {return_string += '<a onclick="javascript:show_frame(\''+settings.helpmenu4_location+'\','+settings.helpmenu4_is_div+')">'+settings.helpmenu4_name+'</a>';}
	if (settings.helpmenu5_enabled) {return_string += '<a onclick="javascript:gen_emotes()">'+settings.helpmenu5_name+'</a>';}
	if (settings.helpmenu6_enabled) {return_string += '<a onclick="javascript:show_frame(\''+settings.helpmenu6_location+'\','+settings.helpmenu6_is_div+')">'+settings.helpmenu6_name+'</a>';}
    
	if (!settings.enable_helpmenu) {return_string = "";}
	
	return return_string;
}

var addEvent = function(elem, type, eventHandle) {
    if (elem == null || elem == undefined) return;
    if ( elem.addEventListener ) {
        elem.addEventListener( type, eventHandle, false );
    } else if ( elem.attachEvent ) {
        elem.attachEvent( "on" + type, eventHandle );
    }
};

function resize_site() {
	var new_width = jQuery(window).width();
    globals.current_width = new_width
	$('result').value = new_width +" x "+ jQuery(window).height(); 

	if (new_width < settings.narrow_width) {
	   globals.narrow_width = 1;
	   globals.content_left = "0px";
	   globals.content_top = (139+settings.banner_space)+"px";
	   $('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 
	   $('Panel').setAttribute("style","height:35px;width:100%;");
       $('message_center').setAttribute("style","position:relative;top:-55px;left:140px;width:"+(new_width-150)+"px;");
       $('tabs').setAttribute("style","left:0px;top:"+(103+settings.banner_space)+"px;width:"+(new_width-20)+"px;");
	   $('obb_header').setAttribute("style","height:"+(100+settings.banner_space)+"px;");
	   $('container').setAttribute("style","top:55px;");
	   $('banner_area').setAttribute("style","top:95px;left:0px;");
	   if (globals.is_connected) {
	      update_message_center();
	   } else {
	      show_login_panel();
	   }
	} else {
		if (globals.narrow_width == 1) {
		   globals.narrow_width = 0;
		   if (globals.is_connected) {
			  update_message_center();
		   } else {
		      show_login_panel();
		   }
	    }
	    globals.narrow_width = 0;
	    $("Panel").setAttribute("style","height:95.5%;"); 
		globals.content_left = "180px";
		globals.content_top = (79+settings.banner_space)+"px";
		$('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 
		$('message_center').setAttribute("style","position:;top:;left:;width:;");
	    $('tabs').setAttribute("style","left:180px;top:"+(43+settings.banner_space)+"px;width:"+(new_width-200)+"px;");	
		$('obb_header').setAttribute("style","height:"+(40+settings.banner_space)+"px;");
		$('container').setAttribute("style","top:0px;");
		$('banner_area').setAttribute("style","top:35px;left:186px;");
	}
}

addEvent(window, "resize", resize_site );

function intext(s) {
	if (settings.language == "en") {
		return (s);
	}
	if (language_hash[s]) { 
		return language_hash[s]; 
	} else { 
		return (s);
	}
}

function isIE() {
   return /msie/i.test(navigator.userAgent);
}

function error_code(input) {
   if (parseInt(input[0]) < 1 || input[0] == "" || input[0] == undefined) {
	  if (input[1] == "" || input[1] == undefined) {input[1] = intext("An error has occurred");}
	  return 1; 
   } else {
      return 0;
   }
}

function show_tabs() {  
   if (settings.first_tab_enabled) {var html = '<div id="first_tab" class="tab"><a onclick="javascript:show_frame(\''+settings.first_tab_location+'\','+settings.first_tab_is_div+')">'+settings.first_tab_name+'</a></div>';
   } else {var html = '<div id="first_tab" class="tab" STYLE="display:none;width:0%;min-width:0%;"></div>';}

   if (settings.second_tab_enabled) {html += '<div id="second_tab" class="tab"><a onclick="javascript:show_frame(\''+settings.second_tab_location+'\','+settings.second_tab_is_div+')">'+settings.second_tab_name+'</a></div>';
   } else {html += '<div id="second_tab" class="tab" STYLE="display:none;width:0%;min-width:0%;"></div>';} 
   
   if (settings.enable_articles) {html += '<div id="articlestab" class="tab" ><a onclick="javascript:get_tab(\'articles\')">'+settings.articles_tab_name+'</a></div>';
   } else {html += '<div id="articlestab" class="tab" STYLE="display:none;width:0%;min-width:0%;"></div>';}

   var number = 5;
   var part = "";
   if (settings.enable_forums) {	
	   for (i = 0; i < settings.total_forums; i += settings.forums_per_tab) {
    	  part = '<div id="forumtab'+number+'" class="tab" ><a onclick="javascript:get_tab(\'forum'+number+'\')">'+settings.forum_tab_names[number]+'</a></div>' + part;
		  number--;   
		  if (number == -2) {alert(intext("invalid configuration, you may only have up to six forum tabs")); break;}
	   }
   } 
   html += part;
   
   //unused forum tabs must still exist, but display is set to none
   while (number > -1) {
   	  html += '<div id="forumtab'+number+'" class="tab" STYLE="display:none;width:0%;min-width:0%;"></div>';
      number--;
   }

   if (settings.enable_private_threads) {html += '<div id="pttab" class="tab" ><a onclick="javascript:get_tab(\'pt\')">'+settings.pt_tab_name+'</a></div>';
   } else {html += '<div id="pttab" class="tab" STYLE="display:none;width:0%;min-width:0%;"></div>';}

   if (settings.second_last_tab_enabled) {html += '<div id="second_last_tab" class="tab"><a onclick="javascript:show_frame(\''+settings.second_last_tab_location+'\','+settings.second_last_tab_is_div+')">'+settings.second_last_tab_name+'</a></div>';
   } else {html += '<div id="second_last_tab" class="tab" STYLE="display:none;width:0%;min-width:0%;"></div>';}

   if (settings.last_tab_enabled) {html += '<div id="last_tab" class="tab" ><a onclick="javascript:show_frame(\''+settings.last_tab_location+'\','+settings.last_tab_is_div+')">'+settings.last_tab_name+'</a></div>';
   } else {html += '<div id="last_tab" class="tab" STYLE="display:none;width:0%;min-width:0%;"></div>';}
   
   $('tabs').innerHTML = '<div class="filled">' + html + '</div>';	  
}

function show_login_panel_horizontal(message) {
	if (settings.new_accounts_allowed) {
	   var part1 = '<input class="rtbutton" type="button" onClick="javascript:show_new_account_entry()" name="sumbit" value="'+intext("Make New Account")+'">';
	} else {
	   var part1 = "";
	}
	
	var part2 = "";

	if (settings.connect_with_username) {
	  part2 += '<iframe id="login_iframe" frameborder="" scrolling="0" src="login_frame_horizontal.php" style="left: 300px; height:50px;width:335px;">' +
    '<p>'+intext("Your browser does not support iframes.")+'</p>' +
    '</iframe>' + part1;
	}	
	if (settings.connect_with_fb) {
	   part2 += '<a href="fb_login.php"><img src="img/fb.png"></a> ';  
	}
	if (settings.connect_with_linkedin) {
	   part2 += '<a href="linkedin_login.php"><img src="img/linkedin.png"></a>';  
	}
	
   $('Panel').innerHTML = '<div id="loginbox">' +
    '<div id="myavatar" class="obbimage"></div>' +
    '<div class="obbtitle">'+settings.website_title+'</div>' +
    '<div class="obbblurb">'+settings.website_blurb+'</div>' +
    '<br><br><br>' +
    '<div id="message_center" class="message_center1"></div>' +

    '<div id="strong" STYLE=position:absolute;left:150px;top:0px;>'+part2+'</div>';

    $('myavatar').innerHTML = '<img src="' +settings.logo_image+ '">';
		
	if (message) {
	   $('message_center').innerHTML = message;
	}
}

function show_login_panel(message) {
    if (globals.narrow_width) {
	   show_login_panel_horizontal(message);
	   return;
	}

	var html = "";

	if (settings.connect_with_username) {
	  html += '<iframe id="login_iframe" frameborder="" scrolling="0" src="login_frame.php" style="height:155px;width:165px;">' +
    '<p>'+intext("Your browser does not support iframes.")+'</p>' +
    '</iframe>';
	}	
	
	if ((settings.new_accounts_allowed) && (settings.connect_with_username)) {
	   html += intext('Or')+'<br><br><input class="rtbutton" type="button" onClick="javascript:show_new_account_entry()" name="submit" value="'+intext("Make New Account")+'"><br>';
	   
	   if ((settings.connect_with_fb) || (settings.connect_with_linkedin)) {
	      html += '<br>'+intext('Or')+'<br><br>';
	   }
    }	

	if (settings.connect_with_fb) {
	   html += '<a href="fb_login.php"><img src="img/fb.png"></a><br><br>';  
	}
	if (settings.connect_with_linkedin) {
	   html += '<a href="linkedin_login.php"><img src="img/linkedin.png"></a><br><br>';  
	}

	
   $('Panel').innerHTML = '<div id="loginbox">' +
    '<div id="myavatar" class="obbimage"></div>' +
    '<div class="obbtitle">'+settings.website_title+'</div>' +
    '<div class="obbblurb">'+settings.website_blurb+'</div>' +
    '<br><br><br>' +
    '<div id="message_center" class="message_center1"></div>' +

    '<div id="strong">'+intext('Sign In')+'<br><br>'+html+'</div>';

    $('myavatar').innerHTML = '<img src="' +settings.logo_image+ '">';

	if (message) {
	   $('message_center').innerHTML = message;
	}
}

function show_new_account_entry() {
	set_display("top_area:none","midrow:none","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
	
	globals.content_height = "";
	$('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 

    var content = '<div id="strong">'+intext("New Account")+'</div> ' +
	'<br>'+intext('Name')+':<span style="position:absolute;left:160px;"> <input type="text" size="17" name="newuser" MAXLENGTH='+settings.max_username_length+' id="newuser"></span><br>' +
	'<br>'+intext('Password')+':<span style="position:absolute;left:160px;"> <input type="password" size="17" name="newpassword0" MAXLENGTH=20 id="newpassword0"></span><br>' +
	'<br>'+intext('Repeat Password')+':<span style="position:absolute;left:160px;"> <input type="password"size="17" name="newpassword1" MAXLENGTH=20 id="newpassword1"></span><br>';

    if (settings.new_account_captcha) {
		content += '<br>' +
		'<img id="siimage" align="left" style="padding-right: 5px; border: 0" src="captcha/securimage_show.php"><br><br><br>' +		
		'<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="19" height="19" id="SecurImage_as3" align="top">'+
			'<param name="allowScriptAccess" value="sameDomain" />'+
			'<param name="allowFullScreen" value="false" />'+
			'<param name="movie" value="captcha/securimage_play.swf?audio=captcha/securimage_play.php&bgColor1=#777&bgColor2=#fff&iconColor=#000&roundedCorner=5" />'+
			'<param name="quality" value="high" />'+
			'<param name="bgcolor" value="#ffffff" />'+
			'<embed src="captcha/securimage_play.swf?audio=captcha/securimage_play.php&bgColor1=#777&bgColor2=#fff&iconColor=#000&roundedCorner=5" quality="high" bgcolor="#ffffff" width="19" height="19" name="SecurImage_as3" align="top" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />'+
		'</object>'+	
		'<a tabindex="-1" style="border-style: none" title="'+intext("Refresh Image")+'" onclick="document.getElementById(\'siimage\').src = \'captcha/securimage_show.php?sid=\' + Math.random(); return false"><img src="captcha/images/refresh.gif" alt="Reload Image" border="0" onclick="this.blur()"  /></a>'+
		'<br>' + intext("Enter the word you see:") + ' <input id="captcha" type="text" name="code" size="12"><br>';
	} else {
		content += '<input id="captcha" style="display:none" type="text" name="code" size="12">';
	} 

	content += '<br><input class="rtbutton" type="button" onClick="javascript:makenewuser()" name="submit" value="'+intext("Register")+'"></div>';

	$('content_area').innerHTML = content;
	   
    $('myavatar').innerHTML = '<img src="' +settings.logo_image+ '">';
}

function get_tab(page){
    if (globals.debug == 1) { $('debug_area').setAttribute("style","display:inline;"); }

	globals.content_height = "85%";
	$('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 

    if (page == "forum0") {
        set_display("top_area:inline","midrow:none","content_area:none","inputdiv:none","topbar:none","bottombar:none");
		set_class("forumtab0");
        globals.current_forum_tab = 0;
        show_forum();
	}
	if (page == "forum1") {
        set_display("top_area:inline","midrow:none","content_area:none","inputdiv:none","topbar:none","bottombar:none");
        set_class("forumtab1");
        globals.current_forum_tab = 1;
		show_forum();
	}
	if (page == "forum2") {
        set_display("top_area:inline","midrow:none","content_area:none","inputdiv:none","topbar:none","bottombar:none");
        set_class("forumtab2");
        globals.current_forum_tab = 2;
		show_forum();
	}
	if (page == "forum3") {
        set_display("top_area:inline","midrow:none","content_area:none","inputdiv:none","topbar:none","bottombar:none");
        set_class("forumtab3");
        globals.current_forum_tab = 3;
		show_forum();
	}	
	if (page == "forum4") {
        set_display("top_area:inline","midrow:none","content_area:none","inputdiv:none","topbar:none","bottombar:none");
        set_class("forumtab4");
        globals.current_forum_tab = 4;
		show_forum();
	}	
	if (page == "forum5") {
        set_display("top_area:inline","midrow:none","content_area:none","inputdiv:none","topbar:none","bottombar:none");
        set_class("forumtab5");
        globals.current_forum_tab = 5;		
		show_forum();
	}		
	if (page == "articles") {
        set_display("top_area:inline","midrow:none","content_area:none","inputdiv:none","topbar:none","bottombar:none");
        set_class("articlestab");
        globals.current_forum_tab = "a";	
		show_forum();
	}			
	if (page == "pt") {
        set_display("top_area:inline","midrow:none","content_area:none","inputdiv:none","topbar:none","bottombar:none");
        set_class("pttab");
        globals.current_forum_tab = "pt";		
		show_forum();
	}			
}

function set_display() {
    for (var i = 0;i < arguments.length ;i++) {
       var a = arguments[i];
       var aa = a.split(":");
       $(aa[0]).setAttribute("style","display:" +aa[1]+ ";"); 
    }
}

function set_class(selected_tab){
    var tab_array = ['first_tab', 'second_tab', 'articlestab', 'forumtab0', 'forumtab1', 'forumtab2', 'forumtab3', 'forumtab4', 'forumtab5', 'pttab', 'second_last_tab', 'last_tab'];

    for (i = 0;i < tab_array.length ;i++) {
	   var foo = tab_array[i];
       $(foo).className = "tab"; 
    }
	if (selected_tab) {	
	   $(selected_tab).className = "tabselected";
	}
	globals.highest_post_in_thread = 0;
    globals.thread_watching = 0; 
	globals.current_forum = -1;
	globals.last_wiki_revision = 0;
}

function set_footer() {
   if ((settings.footer_text) && (globals.footer_is_displayed == 0)) {
	   $('Content').innerHTML += ' <div id="wrapper1" class="wrapper">' +
            '<p></p></div>' +
        '<div id="footerdiv" class="footer">' +
            '<p align=center>'+settings.footer_text+'</p>' +
        '</div>';
		globals.footer_is_displayed = 1;
	}

	if ((settings.footer_text) && (globals.footer_is_displayed == -1)) {
	   $('footerdiv').setAttribute("style","display:inline;")
	   $('wrapper1').setAttribute("style","display:inline;")
	   globals.footer_is_displayed = 1;
	}
	
	if (globals.footer_is_displayed == 1) {
		if (globals.current_forum_tab != "None") {
			$("wrapper1").setAttribute("style","min-height:60%;height:60%;"); 
		} else {
			$("wrapper1").setAttribute("style","min-height:90%;height:90%;");
		}
	}
}

function hide_footer() {
    if (globals.footer_is_displayed == 1) {
	    $('wrapper1').setAttribute("style","display:none;")
	    $('footerdiv').setAttribute("style","display:none;")
		globals.footer_is_displayed = -1;
	}	
}

function show_forum() {
    var start, end, display_string;
	$('midrow').innerHTML = "";
	
	if (!isNaN(globals.current_forum_tab)) { //it's one of the public forums tab
	    if (cache.forum_is_set == 0 ) {
		   hide_footer();
		   $('top_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
		   if (globals.popforum_mutex) {return;}
		   globals.popforum_mutex = 1;
		   globals.hitf_semaphore++;
		   var myAjax3 = new Ajax.Request('popforum.php', {method: 'get', parameters: '', onComplete: populate_forum});
		   return;
	    } 
		end = 11 - ((5 - globals.current_forum_tab) * settings.forums_per_tab);
		start = end - settings.forums_per_tab;
		
		//if the total number forums dosen't divide evenly with the total number of tabs, then you have to do this adjustment
		if ((settings.total_forums % settings.forums_per_tab != 0) && (5 - globals.current_forum_tab) == (Math.floor((settings.total_forums / settings.forums_per_tab)))) {
		   start += settings.forums_per_tab - (settings.total_forums % settings.forums_per_tab);
		}		
	} else if (globals.current_forum_tab == "a") { //it's the articles tab
	    if (cache.forum_is_set == 0 ) {
		   hide_footer();
		   $('top_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
		   if (globals.popforum_mutex) {return;}
		   globals.popforum_mutex = 1;
		   globals.hitf_semaphore++;
		   var myAjax3 = new Ajax.Request('popforum.php', {method: 'get', parameters: '', onComplete: populate_forum});
		   return;
	    } 
	    end = 13;
	    start = 12;	
	} else 	if (globals.current_forum_tab == "pt") { //it's the private forums tab
		if (cache.pt_is_set == 0 ) {
			hide_footer();
			$('top_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
		    if (globals.poppt_mutex) {return;}
		    globals.poppt_mutex = 1;			
			globals.hitf_semaphore++;
			var myAjax3 = new Ajax.Request('poppt.php', {method: 'get', parameters: '', onComplete: populate_pt});
			return;
		} 
		end = 12;
		start = 11;	
	} else {
	   return;
	}

	display_string = '<div id="width_kludge"> <table cellspacing="0" id="thread_table"> <tr>';
	
    for (i = start; i < end; i++) {
	   if (!(settings.enable_private_threads) && (i == 11)) { 
	      display_string = display_string + '<td class="tdcoltitle"> </td>';
	   } else {
	      display_string = display_string + '<td class="tdcoltitle"> <a onclick=\"javascript:get_forum_info('  + cache.forum_id[i] + ')\">' + cache.forum_title[i] + '</td>';
	   }
    }

    for (i = 0; i < 10; i++) {
	   display_string += '<tr>';
	   for (j = start; j < end; j++) {
	      if (!(settings.enable_private_threads) && (j == 11)) { continue; }		
		  x = i + (cache.current_page[j] * 10);		  
		  if ((cache.thread_title[j][x] != undefined) && (cache.thread_title[j][x] != "&nbsp")) {
             if ((cache.thread_state[j][x] == 3) || (cache.thread_state[j][x] == 4)) {
		        display_string += '<td class="tdcol">' + '<a class=\"thread_title\" onclick=\"javascript:get_thread_page('  + cache.thread_id[j][x] + ',0,0,0)\"><b>' + cache.thread_title[j][x] + '</b></a></td>';
			 } else {
		        display_string += '<td class="tdcol">' + '<a class=\"thread_title\" onclick=\"javascript:get_thread_page('  + cache.thread_id[j][x] + ',0,0,0)\">' + cache.thread_title[j][x] + '</a></td>';
			 }
		  } else {
		     display_string += '<td class="tdcol">&nbsp;</td>';
		  }
	   }
	   display_string += '</tr>';
	}

    display_string += '<tr>';
    for (j = start; j < end; j++) {
	   if (!(settings.enable_private_threads) && (j == 11)) { continue; }		  
	   display_string += '<td class="scroll"><font size="+1">';
	   if ((cache.thread_title[j][(cache.current_page[j] + 1) * 10] != "&nbsp") && (cache.thread_title[j][(cache.current_page[j] + 1) * 10] != undefined) ) {
		  display_string += '<a onclick="javascript:forum_scroll('+ j + "," + (cache.current_page[j] + 1) +',0)" >&#9660;</a>'; 
	   } else {
	      display_string += '&nbsp;';
	   }
	   if (cache.current_page[j] > 0 ) {
		  display_string += '&nbsp;&nbsp;<a onclick="javascript:forum_scroll('+ j + "," + (cache.current_page[j]-1) +',0)" >&#9650;</a>'; 
	   } 
	   display_string += '&nbsp;</font></td>'; 
    }

    display_string += '</tr></table></div>';

    $('top_area').innerHTML = display_string;   

	set_footer();
}

function populate_forum(originalRequest) {
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
	if (!error_code(temp_array)) {
		for (var i = 0; i < parseInt(temp_array[0]); i++) {
		    if (i == 11) {continue;} //skip PT
			cache.forum_id[i] = parseInt(temp_array[1 + i * 3]);
			cache.forum_title[i] = temp_array[2 + i * 3];
			cache.is_stored[i][0] = 1;
		}

		var x = 2 + (parseInt(temp_array[0]) * 3);
		var number_of_threads = new Array();

		for (var i = 0; i < parseInt(temp_array[0]); i++) {
			number_of_threads[i] = parseInt(temp_array[3+(i*3)]);
			for (var j = 0; j < number_of_threads[i]; j++) {
				cache.thread_title[i][j] = temp_array[x];
				cache.thread_id[i][j] = parseInt(temp_array[x-1]);
				cache.thread_state[i][j] = parseInt(temp_array[x+1]);
				if (parseInt(temp_array[x-1]) > globals.highest_thread_in_forums) { globals.highest_thread_in_forums = parseInt(temp_array[x-1]); }
				x += 3;
			}
		}
		globals.hitf_semaphore--;	
		if (globals.is_connected && (globals.hitf_semaphore == 0)) { updater(); }		
		cache.forum_is_set = 1;
		globals.popforum_mutex = 0;
		show_forum()
	} else {
		globals.hitf_semaphore--;
	    globals.popforum_mutex = 0;
		$('top_area').innerHTML = "<p class='system'>" +temp_array[1]+ "</p>";
	}
}

function populate_pt(originalRequest) {
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
	if (!error_code(temp_array)) {
	    cache.forum_title[11] = temp_array[2];
		cache.forum_id[11] = 12;
		cache.is_stored[11][0] = 1;
		number_of_threads = parseInt(temp_array[3]);
		var x = 5;
		for (var j = 0; j < number_of_threads; j++) {
			cache.thread_title[11][j] = temp_array[x];
			cache.thread_id[11][j] = parseInt(temp_array[x-1]);
			cache.thread_state[11][j] = parseInt(temp_array[x+1]);
			if (parseInt(temp_array[x-1]) > globals.highest_thread_in_forums) { globals.highest_thread_in_forums = parseInt(temp_array[x-1]); }
			x += 3;
		}
		globals.hitf_semaphore--;
		if (globals.is_connected && (globals.hitf_semaphore == 0)) { updater(); }		
		cache.pt_is_set = 1;
		globals.poppt_mutex = 0;
		show_forum();
	} else {
		globals.hitf_semaphore--;
		globals.poppt_mutex = 0;
		$('top_area').innerHTML = "<p class='system'>" +temp_array[1]+ "</p>";
	}
}

function forum_scroll(forum_id, page, silent) {
    if ((cache.is_stored[forum_id][page] == undefined) || (cache.is_stored[forum_id][page] == 0)) {
       var pars = 'page=' + page + "&forum_id=" + (forum_id+1);  
       var myAjax3 = new Ajax.Request('get_page.php', {method: 'get', parameters: pars, onComplete: add_page_to_cache});
	   return;
	}   
   
    cache.current_page[forum_id] = parseInt(page);
    if (silent == 0) {
       show_forum();  
	}
}

function add_page_to_cache(originalRequest) {
    var temp_string = originalRequest.responseText;
    temp_array = temp_string.split("^?");
	
	if (error_code(temp_array)) {alert(temp_array[1]); return;}
	
	forum_id = parseInt(temp_array[0]);
	page = parseInt(temp_array[1]);
	count = parseInt(temp_array[2]);
	
	for (var i = 0; i < count; i++) {
	   var x = i + (page * 10);
	   cache.thread_title[forum_id-1][x] = temp_array[5 + i * 4];
       cache.thread_id[forum_id-1][x] = parseInt(temp_array[4 + i * 4]);
       cache.thread_state[forum_id-1][x] = parseInt(temp_array[6 + i * 4]);	   
	}
	cache.current_page[forum_id-1] = page;
    cache.is_stored[forum_id-1][page] = 1;
	
	show_forum();  
}

function get_forum_info(forum_id){
    set_display("top_area:inline","midrow:none","content_area:inline","inputdiv:inline","topbar:none","bottombar:none");
	globals.thread_watching = 0;

	if (forum_id == 12) {	  
	   if (settings.status_to_start_pt > account_info.status) {
	   	  $('content_area').innerHTML = "<br><p class=\"system\">"+intext("Your status isn't high enough to start private threads")+"</p>";
          $('inputdiv').innerHTML = "";	   
	   } else {
	      display_private_thread_entry();
	   }
	} else {
	   $('content_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
	   $('inputdiv').innerHTML = "";
       var pars = "forum_id=" + forum_id;
       var myAjax = new Ajax.Request('forum_info.php', {method: 'get', parameters: pars, onComplete: populate_forum_info});
    }
}

function populate_forum_info(originalRequest) {
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
	
	if (error_code(temp_array)) {$('content_area').innerHTML = temp_array[1]; return;}
	globals.current_forum = parseInt(temp_array[0]);

	if (account_info.user_id == 0) {
	    $('content_area').innerHTML = "<br><p class=\"system\">"+intext("Only signed in users can post")+"</p>";
        $('inputdiv').innerHTML = "";
		return;
	}

    if (temp_array[0] == 13) {
	   if (settings.status_to_create_articles > account_info.status) {
		  $('content_area').innerHTML = "<br><p class=\"system\">"+intext("Your account status isn't high enough to post articles")+"</p>";
		  $('inputdiv').innerHTML = "";		  
	   } else {	 
		  $('content_area').innerHTML = "<br><br>"+intext("Post a new article below:")+"<br>";
		  $('inputdiv').innerHTML = '<br>'+intext("Title")+': <br><form><input type="text" class="theinputbox" style="width: 730px" MAXLENGTH='+settings.size_of_article_title+
		  ' id="thread_title"></form><br><br>'+intext("Article")+':<br><br>'+display_input(4,temp_array[0]) +
		  '<div id="optionbox" class="articleoptionbox">'+intext("Wiki Access")+'<br><form name="wiki_type">' +
		  '<INPUT TYPE=RADIO NAME="wiki_opt" id="author" VALUE="1" checked="checked">'+intext("Author Only")+'<br>' +
		  '<INPUT TYPE=RADIO NAME="wiki_opt" id="editor" VALUE="2">'+settings.name_of_status_2+intext("s and Moderators")+'<BR>' +
		  '<INPUT TYPE=RADIO NAME="wiki_opt" id="regular_user" VALUE="3">'+intext("Regular Users")+'<BR>' +
		  '<INPUT TYPE=RADIO NAME="wiki_opt" id="all_users" VALUE="4">'+intext("All Users")+'<BR>' +	 
		  '</form></div>'+
		  '<br><br><div id="optionbox2" class="articleoptionbox">'+("Comments")+'<br><form name="comment_type">' +
		  '<INPUT TYPE=RADIO NAME="comment_opt" id="comments" VALUE="0" checked="checked">'+intext("Comments allowed")+'<br>' +
		  '<INPUT TYPE=RADIO NAME="comment_opt" id="no_comments" VALUE="1">'+intext("Comments not allowed")+'<BR>' +
		  '</form></div>';
	  }
   } else {
	   if (settings.status_to_start_threads > account_info.status) {
		  $('content_area').innerHTML = "<br><p class=\"system\">"+intext("Your account status isn't high enough to start a new thread")+"</p>";
		  $('inputdiv').innerHTML = "";		  
	   } else {	    
   		   $('content_area').innerHTML = "<br><br>"+intext("Post a new thread below:")+"<br>";
		   $('inputdiv').innerHTML = '<br>'+intext("Title")+': <br><form><input type="text" class="theinputbox" style="width: 300px" MAXLENGTH='+settings.size_of_thread_title+
		   ' id="thread_title"></form><br><br>'+intext("Message")+':<br><br>'+display_input(2,temp_array[0]);
	   }
	}	  

	globals.content_height = "";
	$('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 
}

function display_private_thread_entry() {
   get_tab('pt');
   set_display("top_area:inline","midrow:none","content_area:inline","inputdiv:inline","topbar:none","bottombar:none");
   globals.current_forum = 12;

   $('content_area').innerHTML = "<br><br>"+intext("Make a private thread") + ":<br><br>";
   $('inputdiv').innerHTML = '' +
	 intext('Users')+':<br>' +
	 '<textarea class="theinputbox2" cols=70 rows=2 style="height: 36px" id=\'private_user_list\'></textarea>' +
     '<div id="optionbox" class="optionbox"><form name="form_pt_type">' +
     '<INPUT TYPE=RADIO NAME="pt_type" id="author_only_box" VALUE="3" checked="checked">'+intext("author-only invite")+'<br>' +
     '<INPUT TYPE=RADIO NAME="pt_type" id="open_invite_box" VALUE="2">'+intext("open invite")+'<BR>' +
	 '<INPUT TYPE=RADIO NAME="pt_type" id="closed_box" VALUE="1">'+intext("no further invites")+'<BR>' +
     '</form></div>' +
	 intext("Title")+': <br><form><input type="text" class="theinputbox" style="width: 300px" MAXLENGTH='+settings.size_of_pt_title+' id="thread_title">' +
	 '<br><br>'+intext("Message")+':<br><br>'+display_input(3,12);
		
	jQuery().ready(function() {
		function log(event, data, formatted) {
			$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
		}
		function formatItem(row) {
			return row[0] + " (<strong>id: " + row[1] + "</strong>)";
		}
		function formatResult(row) {
			return row[0].replace(/(<.+?>)/gi, '');
		}

		jQuery("#private_user_list").autocomplete('search_users.php', {
			width: 300,
			multiple: true,
			matchContains: true,
			formatItem: formatItem,
			formatResult: formatResult
		});
	});
}			

function post_private_thread() {  
   for (var i=0;i<document.form_pt_type.pt_type.length;i++)
   {
      if (document.form_pt_type.pt_type[i].checked)
      {
         pt_type = document.form_pt_type.pt_type[i].value;  
      }
   } 
	  	 
   var captcha = $('postcaptcha').value;	 
		 
   var pars = 'user_id=' + account_info.user_id + "&pt_type=" + pt_type + "&members=" + encodeURIComponent($("private_user_list").value)  + '&content_of_thread=' + encodeURIComponent($("theinputbox").value) + '&thread_title=' + encodeURIComponent($("thread_title").value) + '&captcha=' + captcha;
   var myAjax = new Ajax.Request('postprivatethread.php', {method: 'post', parameters: pars, onComplete: refresh_forum});
   
   $('content_area').innerHTML = '';
   $('inputdiv').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';   
}

function display_file_upload2(forum_id){
    var file_types = settings.allowed_file_types.toString()
    file_types = file_types.replace(/,/g,", ");
	 
     $('uploadbutton').innerHTML = '<div id="fileblurb">'+intext("Upload file")+':<br>'+intext("allowed file types")+': ' + file_types +'</div>'
	+ '        <div id="iframe">'
	+ '            <iframe id="foo_frame_0" src="attach_file.php?forum_id='+forum_id+'" frameborder="" height="50px" scrolling="0"></iframe>'
	+ '        </div>'
	+ '        <div id = "list2"></div>';
}

function post_thread(forum_id) {
   if (forum_id	== 13) {
	   for (var i=0;i<document.wiki_type.wiki_opt.length;i++)
	   {
		  if (document.wiki_type.wiki_opt[i].checked)
		  {
			 var_wiki_type = document.wiki_type.wiki_opt[i].value;  
		  }
	   } 
	   for (i=0;i<document.comment_type.comment_opt.length;i++)
	   {
		  if (document.comment_type.comment_opt[i].checked)
		  {
			 var_comment_type = document.comment_type.comment_opt[i].value;  
		  }
	   } 	   
	   
      globals.last_wiki_revision = 0;
      var pars = 'user_id=' + account_info.user_id + '&forum_id=' + forum_id + '&content_of_thread=' + encodeURIComponent($("theinputbox").value) + '&thread_title=' + encodeURIComponent($("thread_title").value) + "&captcha=" + postcaptcha+ "&wiki_type=" + var_wiki_type + "&comment_type=" + var_comment_type;   
   } else {
      var pars = 'user_id=' + account_info.user_id + '&forum_id=' + forum_id + '&content_of_thread=' + encodeURIComponent($("theinputbox").value) + '&thread_title=' + encodeURIComponent($("thread_title").value) + "&captcha=" + postcaptcha;
   }
   
   var myAjax = new Ajax.Request('postthread.php', {method: 'post', parameters: pars, onComplete: refresh_forum});
   
   $('content_area').innerHTML = '';
   $('inputdiv').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
}

function refresh_forum(originalRequest){
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");     
	
    if (!error_code(temp_array)) {
       globals.thread_watching = 0;
	   updater();
	   globals.thread_watching = parseInt(temp_array[1]);
       mythreads_hash.set(globals.thread_watching,1) 
	   
       var pars = 'thread_id=' + temp_array[1] + '&offset=' + 0;
       var myAjax2 = new Ajax.Request('popthread.php', {method: 'get', parameters: pars, onComplete: populate_thread});
    } else {
		if (settings.new_user_post_captcha && (account_info.status == 0)) {
		   reset_captcha();
		}  	
        alert(temp_array[1]);
    }      
}

function get_thread_page(thread_id,offset,delay,mutex) {
    globals.get_thread_page_done = 0;

    if (globals.attempting_auto_login) {
        setTimeout("get_thread_page(" +thread_id+ "," +offset+ ",0,"+mutex+")",50);
        return 0;
    }
	
    if (delay > 0) {
        setTimeout("get_thread_page(" +thread_id+ "," +offset+ ",0,"+mutex+")",delay);
        return 0;
    }
    if (mutex) {
       globals.post_mutex = 0
    }

	set_display("top_area:inline","midrow:none","content_area:inline","inputdiv:none");
		
	hide_footer();	
    $('content_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
		
    var pars = 'thread_id=' + thread_id + '&offset=' + offset;
    var myAjax = new Ajax.Request('popthread.php', {method: 'get', parameters: pars, onComplete: populate_thread});
}

function populate_thread(originalRequest)
{
	if (globals.current_forum_tab == "None") {
		if (settings.enable_forums) {
		   get_tab("forum"+ (6 - Math.ceil((settings.total_forums / settings.forums_per_tab)))); 
		} else if (settings.enable_articles) {
		   get_tab("articles");
		} else if (settings.enable_private_threads) {
		   get_tab("pt");
		}
	}

    set_display("top_area:inline","midrow:table-cell","content_area:inline","inputdiv:none","topbar:inline","bottombar:inline");
    var display_string = "";
	
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
	var temp_string2;
	
	if (error_code(temp_array)) {$('content_area').innerHTML = temp_array[1]; return;}

	var thread_id = parseInt(temp_array[0]);
	var forum_id = parseInt(temp_array[1]);	
	var type = parseInt(temp_array[2]);  //0: public thread, 1, 2 or 3: private thread, -1:not a member of a private thread, -2:former member of a private thread
	var block_allow_list = temp_array[3];
    if (!isNaN(parseInt(temp_array[4]))) {globals.highest_post_in_thread = parseInt(temp_array[4]);} else {globals.highest_post_in_thread = 0;}
    var num_posts = parseInt(temp_array[5]);  //number of posts the list is giving you
    var total_posts = parseInt(temp_array[6]); //total number of posts this thread has
    var thread_title = temp_array[7]; //title of thread
    var total_pages = parseInt(temp_array[8]); //total pages this thread has
    var thread_offset = parseInt(temp_array[9]); //current page being displayed, counting from zero
    var state = parseInt(temp_array[10]); //0: normal, 1: closed, 2: deleted, 3: sticky, 4: sticky and closed, 5: invalid thread
    var wiki_info = temp_array[11];  //wiki type, total revisions and author of first and latest revision
	
	var is_wiki_page = 0;
	if ((wiki_info != "") && (wiki_info != "0") && (wiki_info != undefined) ){
	   is_wiki_page = 1;
	   temp_wiki_info = wiki_info.split(",");
       globals.last_wiki_revision = parseInt(temp_wiki_info[1]); 
	   globals.wiki_revision = globals.last_wiki_revision;
	}

	if (globals.thread_watching != thread_id) {
	   globals.pagenumbar_expanded = 0;
	}
	
	globals.current_page_of_thread = thread_offset;
	globals.thread_watching = thread_id;
	globals.number_of_posts_displayed = num_posts;
	globals.current_forum = forum_id;
	
	if ((state == 3) || (state == 4)) {
	   temp_string2 = '<img src="img/sticky.gif"> ';
	} else {
	   temp_string2 = '';
	}
	
	if (account_info.status > 2) {
		temp_string2 += '<span class="big_thread_title"> <a id="thread_mod_box" href="popup_thread_title.php?thread_id=' + thread_id + '">'  + thread_title + '</a></span>'; 
	} else {
		temp_string2 += '<span class="big_thread_title">' + thread_title + '</span>';
	}

    if (state == 2) {
		$('midrow').innerHTML = temp_string2;
		$('content_area').innerHTML = "<br><br>&nbsp;&nbsp;&nbsp;"+intext("This thread has been deleted")+"<br><br>";
		
		if (parseInt(total_posts) > parseInt(mythreads_hash.get(globals.thread_watching))) {
	       mythreads_hash.set(globals.thread_watching,total_posts);
           globals.newly_read_threads += "," + globals.thread_watching + ":" + total_posts;
        }
		
		jQuery("#thread_mod_box").fancybox({
			'width'				: 400,
			'height'			: 230,
			'autoScale'			: false,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'type'				: 'iframe'
		});		
		return;
    }	
    if (state == 5) {
	    set_display("midrow:none");
		$('content_area').innerHTML = "<br><br>&nbsp;&nbsp;&nbsp;"+intext("Invalid thread")+"<br><br>";
		
		if (parseInt(total_posts) > parseInt(mythreads_hash.get(globals.thread_watching))) {
	       mythreads_hash.set(globals.thread_watching,total_posts);
           globals.newly_read_threads += "," + globals.thread_watching + ":" + total_posts;
        }		
		return;
    }		

	if (is_wiki_page) {
		creator = temp_wiki_info[3].split(";")
		if (account_info.status > 2) {
    	   temp_string2 += "<br><span id='wiki_info1' class='plink'>"+intext("Created by: ")
		   + "<span class='member' title='" + (parseInt(creator[0])).toString().toUpperCase() + "'>" 
		   + "<a id='Wprofile_boxC' href='popup_wiki.php?user_id=" + (parseInt(creator[0])).toString().toUpperCase() + "&thread_id="+thread_id+"&page=0&post_position=0&type=1'>" + creator[1] + "</a></span>"
		   + " - ";
		} else {
    	   temp_string2 += "<br><span id='wiki_info1' class='plink'>"+intext("Created by: ")
		   + "<span class='member' title='" + (parseInt(creator[0])).toString().toUpperCase() + "'>" 
		   + "<a id='Wprofile_boxC0' href='profile.php?user_id=" + (parseInt(creator[0])).toString().toUpperCase()+"'>" + creator[1] + "</a></span>"
		   + " - ";
        }		   
		
		switch(parseInt(temp_wiki_info[0]))
		{
			case 1:
			  temp_string2 += intext("Only author may edit")+" - ";
			  break;
			case 2:
			  temp_string2 += intext("Moderators and star members may edit")+" - ";
			  break;
			case 3:
			  temp_string2 += intext("Regular users or higher may edit")+" - ";
			  break;
			case 4:
			  temp_string2 += intext("All users may edit")+" - ";
			  break;	  
			default:
		}
		var current_rev = parseInt(temp_wiki_info[1])+1;
		temp_string2 += intext("Total revisions: ")+(parseInt(temp_wiki_info[1])+1) +"</span>";
		temp_string2 += "<span id='wiki_info2' class='plink'>"+intext("revision:")+" <span class='wikiscroll'><a onclick=\"show_revision("+thread_id+","+(current_rev-2)+","+temp_array[18]+")\">&#9668;</a> "+current_rev+" <a onclick=\"show_revision("+thread_id+","+(current_rev)+","+temp_array[18]+")\">&#9658</a></span></span>";

		if (account_info.status > 2) {		
			temp_string2 += "<span id='wiki_info4' class='plink'>"+intext("This revision by: ")
			+ "<span class='member' title='" + temp_array[12] + "'>" 
			+ "<a id='Wprofile_boxR' href='popup_wiki.php?user_id=" + temp_array[12] + "&thread_id="+thread_id+"&page=0&post_position=0&type=0'>" + temp_array[13] + "</a></span>"		
			+ "</span>";
		} else {
			temp_string2 += "<span id='wiki_info4' class='plink'>"+intext("This revision by: ")
			+ "<span class='member' title='" + temp_array[12] + "'>" 
			+ "<a id='Wprofile_boxR0' href='profile.php?user_id=" + temp_array[12] + "'>" + temp_array[13] + "</a></span>"		
			+ "</span>";		
		}
	}		   
	
	if (type < 0) {
        set_display("top_area:inline","midrow:table-cell","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
		globals.thread_watching = thread_id;	
		globals.current_page_of_thread = thread_offset;
			
	    if (type == -2) {	
		  $('midrow').innerHTML = thread_title;
		  $('content_area').innerHTML = "<br><br>&nbsp;&nbsp;&nbsp;"+intext("You're no longer a member of this thread")+"<br><br><span class='plink'><a onclick='javascript:join_thread("+thread_id+")'>"+intext("join thread")+"</a></span>";
		} else {
		  $('midrow').innerHTML = intext("Private Thread");
		  $('content_area').innerHTML = "<br><br>&nbsp;&nbsp;&nbsp;"+intext("You're not a member of this thread");				
	    }
		if (parseInt(total_posts) > parseInt(mythreads_hash.get(globals.thread_watching))) {
	      mythreads_hash.set(globals.thread_watching,total_posts);
          globals.newly_read_threads += "," + globals.thread_watching + ":" + total_posts;
        }
	    globals.content_height = "87%";
		$('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 
		return;
    } 
	
	if (type == 0) {
		temp_string2 += "<span class='pcontainer'>";

		if (mythreads_hash.get(thread_id) != undefined) {
			temp_string2 += "<span STYLE=\"display:none;\" class='plink'><a onclick='javascript:watch_thread("+thread_id+", "+total_posts+")'>"+intext("watch ")+"</a></span>" +
			"<span STYLE=\"display:inline;\" class='plink'><a onclick='javascript:unwatch_thread("+thread_id+")'>"+intext("unwatch ")+"</a></span>";
		} else {
			temp_string2 += "<span STYLE=\"display:inline;\" class='plink'><a onclick='javascript:watch_thread("+thread_id+", "+total_posts+")'>"+intext("watch ")+"</a></span>" +
			"<span STYLE=\"display:none;\" class='plink'><a onclick='javascript:unwatch_thread("+thread_id+")'>"+intext("unwatch ")+"</a></span>";
		} 
		
		if (settings.permalinks_enabled) {
			if (thread_offset > 0) { 
				temp_string2 += "<span class='plink'><a href='"+settings.website_url+"/thread.php?id="+thread_id+"&page="+(thread_offset+1)+"'>"+intext("permalink")+"</a></span></span>";
			} else {
				temp_string2 += "<span class='plink'><a href='"+settings.website_url+"/thread.php?id="+thread_id+"'>"+intext("permalink")+"</a></span>";
			}		
		}

	    temp_string2 += "</span>";	
	} else {
		temp_string2 += "<span class='pcontainer2'>";
	    if ((type == 2) || (type == 4) ) {
            temp_string2 += "<span id='invite_area' class='plink'><a onclick='javascript:invite_to_pt()'>"+intext("invite users")+"</a></span>"; 
		}
		temp_string2 += "<span class='plink'><a onclick='javascript:leave_pt("+thread_id+")'>"+intext("leave thread")+"</a></span>"; 
		temp_string2 += "</span>";	
	}
	
	if (type > 0) {

	    temp_string2 += "<br><span class='private_thread_box'>"+intext("Private thread");

		switch(type)
		{
		case 1:
		  temp_string2 += " - ";
		  break;
		case 2:
		  temp_string2 += " - "+intext("Open invite")+" - ";
		  break;
		case 3:
		  temp_string2 += " - "+intext("Author may invite more members")+" - ";
		  break;
		case 4:
		  temp_string2 += " - "+intext("Only you may invite")+" - ";
		  break;	  
		default:
		}
		
		temp_string2 += intext("Members")+": " + display_member_box(block_allow_list) + "</span>";
	}	

	$('midrow').innerHTML = temp_string2;

	var temp_array2 = block_allow_list.split(",");    
    var temp_img_src, temp_colone_src, edit_button;
    display_string = '<div id="width_kludge2"> <table cellspacing="0" id="bigtable"> ';
	
    for (i = 0; i < num_posts; i++) {
        offset = 12 + (i * 7);

		if (temp_array[offset+0] == 0) {
		   temp_img_src = '<img border=0 src="'+settings.system_avatar+'">';
        } else {
			if (parseInt(temp_array[offset+2]) == 0) {
			   if (settings.avatars_same_size) {
				  temp_img_src = '<img border=0 width="'+settings.max_avatar_dimensions[0]+'" height="'+settings.max_avatar_dimensions[1]+'" src="'+settings.default_avatar+'">';
			   } else {
                  temp_img_src = '<img border=0 src="'+settings.default_avatar+'">';
			   }					   
			} else {
			   if (settings.avatars_same_size) { extra = 'width="'+settings.max_avatar_dimensions[0]+'" height="'+settings.max_avatar_dimensions[1]+'"';} else { extra = '';}
			   temp_img_src = '<img border=0 '+extra+' src="file.php?uid=' +temp_array[offset+0]+ '&avatar_number=' +temp_array[offset+2]+ '">';
			}
		}
		if ((settings.avatars_allowed == false) || parseInt(temp_array[offset+2]) == -1 || (parseInt(temp_array[offset+2]) == 0 && settings.new_user_avatar == 0)) {temp_img_src = "";}

		if (account_info.status > 2) {
		   temp_colone_src = '<a id="post_mod_box'+i+'" href="popup_user_id.php?user_id=' + temp_array[offset+0] + '&page=' + thread_offset + '&post_position=' + i + '&thread_id=' + thread_id + '">'  + temp_array[offset+0] + '</a>'; 
		} else {
		   temp_colone_src = temp_array[offset+0];
		}
		
		if (temp_array[offset+5] == 1) {
		   edit_button = '<span id=edit_button'+temp_array[offset+6]+' class="plink" style="margin-left:0px;position:relative;top:-4px;padding-top:6px;"><a onclick="javascript:edit_post('+temp_array[offset+6]+')">'+intext('edit')+'</a></span><br>';
	    } else {
		   edit_button = '';
	    }
		
	    var file_attachments = '';
	    var tmp_result = temp_array[offset+3].match(/&lt;&lt;&lt;FILE:(.*?)&gt;&gt;&gt;/g);
	    if (tmp_result) {
			for (x = 0; x < tmp_result.length; x++) {
				tmp_filename = tmp_result[x].replace(/&lt;&lt;&lt;FILE:(.*?)\|.*?&gt;&gt;&gt;/,'$1');      
                tmp_fileid = tmp_result[x].replace(/&lt;&lt;&lt;FILE:.*?\|(.*?)&gt;&gt;&gt;/,'$1');  				
				file_attachments += '<a href="file.php?id=' +tmp_fileid+ '">' + tmp_filename + '</a><br>';
			}
			file_attachments = '<tr id="attachment_row"> <td class="colone"></td><td class="coltwo"> </td><td id="attachments" class="colthree">File attachments:<br>'+file_attachments+'</td><td class="colfour"></td></tr>';
			temp_array[offset+3] = temp_array[offset+3].replace(/(&lt;&lt;&lt;FILE:.*?&gt;&gt;&gt;)/g,'<span style="display:none;">$1</span>');
		}

	    if ((is_wiki_page) && (i == 0)) {
		    if (account_info.status > 2) {
			   if (edit_button == '') {
			      edit_button += "<span style='margin-left:0px;position:relative;top:-4px;padding-top:6px;' class='plink' id='delete_button"+temp_array[offset+6]+"'><a onclick=\"javascript:delete_wiki_post("+thread_id+","+globals.wiki_revision+","+parseInt(temp_array[offset+0])+")\">"+intext('delete')+"</a></span>"
			   } else {
			      edit_button += "<span style='margin-left:60px;position:relative;top:-22px;padding-top:6px;' class='plink' id='delete_button"+temp_array[offset+6]+"'><a onclick=\"javascript:delete_wiki_post("+thread_id+","+globals.wiki_revision+","+parseInt(temp_array[offset+0])+")\">"+intext('delete')+"</a></span>"
			   }
			}
			display_string = display_string + '<tr> <td class="colone">' + '</td>' +
				 '<td class="coltwo"> </td>' +
				 '<td class="colthree">' +edit_button+ '<div id=msg'+temp_array[offset+6]+' >'+  temp_array[offset+3] + '</div></td>' +
				 '<td class="colfour"><span id="wiki_op_date">' +  temp_array[offset+4] + '</span><br><a class="quote_button" onclick="quote_post('+temp_array[offset+6]+',\''+temp_array[offset+1]+'\','+total_pages+')">'+intext('Quote')+'</a>' +'</td>' +
				 '</tr>'+file_attachments;	 
	    } else {
			display_string = display_string + '<tr> <td class="colone">' + temp_colone_src + '</td>' +
				 '<td class="coltwo"> <a id="profile_box'+i+'" href="profile.php?user_id=' + temp_array[offset+0] + '">' + temp_array[offset+1] + '<br>' +temp_img_src+ '</a></td>' +
				 '<td class="colthree">' +edit_button+ '<div id=msg'+temp_array[offset+6]+' >'+  temp_array[offset+3] + '</div></td>' +
				 '<td class="colfour">' +  temp_array[offset+4] + '<br><a class="quote_button" onclick="quote_post('+temp_array[offset+6]+',\''+temp_array[offset+1]+'\','+total_pages+')">'+intext('Quote')+'</a>' +'</td>' +
				 '</tr>'+file_attachments;	 
	    }
    }

    display_string = display_string + '  </table></div>';	
    $('content_area').innerHTML = display_string;
		
    var j, i;
    var tstring = intext("Page")+" ";
		
	if (total_pages < 21) {	
		for (i = 0; i < total_pages; ++i) {
			if (i == thread_offset) {
			   tstring = tstring + "<a class='pageclassselected' id=\"pagenumber\">" + (i+1) + "</a> ";           
			} else {
			   tstring = tstring + "<a class='pageclass' id=\"pagenumber\" onclick=\"javascript:get_thread_page(" +thread_id+ "," + i + ",0,0)\">" + (i+1) + "</a> ";
			}
		}
    } else {
	   if (globals.pagenumbar_expanded == 0) {
			for (i = 0; i < 10; ++i) {
				 if (i == thread_offset) {
				   tstring = tstring + "<a class='pageclassselected' id=\"pagenumber\">" + (i+1) + "</a> ";           
				 } else {
				   tstring = tstring + "<a class='pageclass' id=\"pagenumber\" onclick=\"javascript:get_thread_page(" +thread_id+ "," + i + ",0,0)\">" + (i+1) + "</a> ";
				 }
			}	   	   
			tstring = tstring + " <a class='pageexpandlink' onclick=\"javascript:expand_pagenumbar("+total_pages+")\">[ ... ]</a> ";
			for (i = total_pages - 10; i < total_pages; ++i) {
			   if (i == thread_offset) {
				   tstring = tstring + "<a class='pageclassselected' id=\"pagenumber\">" + (i+1) + "</a> ";           
			   } else {
				   tstring = tstring + "<a class='pageclass' id=\"pagenumber\" onclick=\"javascript:get_thread_page(" +thread_id+ "," + i + ",0,0)\">" + (i+1) + "</a> ";
			   }
			}
	   } else {
			for (i = 0; i < total_pages; ++i) {
				if (i == thread_offset) {
				   tstring = tstring + "<a class='pageclassselected' id=\"pagenumber\">" + (i+1) + "</a> ";           
				} else {
				   tstring = tstring + "<a class='pageclass' id=\"pagenumber\" onclick=\"javascript:get_thread_page(" +thread_id+ "," + i + ",0,0)\">" + (i+1) + "</a> ";
				}
			}	   
	   }
	}

    if (total_pages > 1) {
       $('topbar').innerHTML = '<p class="pagenumbar" align="center">' +  tstring + '</p>';
       $('bottombar').innerHTML = '<p class="pagenumbar" align="center">' +  tstring + '</p>';
    } else {
       $('topbar').innerHTML = '<p class="pagenumbar" align="center"></p>';
       $('bottombar').innerHTML = '<p class="pagenumbar" align="center"></p>';
    }
	
	//if you're on the last page of the thread
    if (total_pages == parseInt(thread_offset,10) + 1) {
	   //todo: as soon as you're on a new page an nrt gets set, even if you're not on the last page.
       if (parseInt(total_posts) > parseInt(mythreads_hash.get(globals.thread_watching))) {
	      mythreads_hash.set(globals.thread_watching,total_posts);
          globals.newly_read_threads += "," + globals.thread_watching + ":" + total_posts;
       }
	      	   
	   if ((state == 1) || (state == 4)) {
		   $('inputdiv').setAttribute("style","display:inline;");
		   $('inputdiv').innerHTML = '<p class="system">'+intext("Thread has been closed")+'</p><br><br>';
		   if (forum_id == 13) {
		      $('inputdiv').setAttribute("style","display:none;");		   
		   }
	   } else if (account_info.user_id == 0) {
	   	   $('inputdiv').setAttribute("style","display:inline;");
		   $('inputdiv').innerHTML = '<p class="system">'+intext("Must be signed in to post")+'</p><br><br>';
	   } else {
		   $('inputdiv').setAttribute("style","display:inline;");
		   $('inputdiv').innerHTML = display_input(1,thread_id);
       }
	} else {
       $('inputdiv').setAttribute("style","display:none;");
       globals.highest_post_in_thread = 0;  //?
    }

	globals.content_height = "";
	$('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 
	
    set_footer();
	
	jQuery().ready(function() {
		for (i = 0; i < num_posts; i++) {
			jQuery("#profile_box"+i).fancybox({
				'width'				: 800,
				'height'			: 400,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
			});
			jQuery("#post_mod_box"+i).fancybox({
				'width'				: 400,
				'height'			: 245,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
			});			
		}
		for (i = 0; i < temp_array2.length; i++) {
		    if (account_info.status > 2) {	
				jQuery("#pt_mod_box"+i).fancybox({
					'width'				: 400,
					'height'			: 245,
					'autoScale'			: false,
					'transitionIn'		: 'none',
					'transitionOut'		: 'none',
					'type'				: 'iframe'
				});		
			} else {
				jQuery("#Zprofile_box"+i).fancybox({
					'width'				: 800,
					'height'			: 400,
					'autoScale'			: false,
					'transitionIn'		: 'none',
					'transitionOut'		: 'none',
					'type'				: 'iframe'
				});		
			}
		}		
		jQuery("#thread_mod_box").fancybox({
			'width'				: 400,
			'height'			: 230,
			'autoScale'			: false,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'type'				: 'iframe'
		});		
		jQuery("#Wprofile_boxR").fancybox({
				'width'				: 400,
				'height'			: 225,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
		});		
		
		jQuery("#Wprofile_boxC").fancybox({
				'width'				: 400,
				'height'			: 225,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
		});		
		jQuery("#Wprofile_boxC0").fancybox({
				'width'				: 800,
				'height'			: 400,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
		});				
		jQuery("#Wprofile_boxR0").fancybox({
				'width'				: 800,
				'height'			: 400,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
		});		
	});	
	
    globals.get_thread_page_done = 1
}

function display_member_box(block_allow_list) {
   var ret_value = "";
   var temp_array = block_allow_list.split(",");    
   for (i = 0; i < temp_array.length; i++) {
	  if (temp_array[i] == "") {continue;}
      var temp_array2 = temp_array[i].split(";");
	  
	  if (i == temp_array.length - 1) {
	     comma = "";
      } else {
	     comma = ",";
	  }
	  
	  if (account_info.status > 2) {		
		 ret_value += "<span class='member' title='" + temp_array2[0] + "'>" 
			+ "<a id='pt_mod_box"+i+"' href='popup_pt.php?user_id=" + temp_array2[0] + "&thread_id="+globals.thread_watching+ "'>" + temp_array2[1] + "</a>"+comma+"</span>";	
	  } else {
		 ret_value += "<span class='member' title='" + (parseInt(temp_array2[0])).toString().toUpperCase() + "'><a id='Zprofile_box"+i+"' href='profile.php?user_id=" + (parseInt(temp_array2[0])).toString().toUpperCase() + "'>" + temp_array2[1] + "</a>"+comma+"</span>"; 
	  }
   }

   return ret_value;   
}

function show_revision(thread_id,revision,msg_id) {
   if ((revision < 0) || (revision > globals.last_wiki_revision)) {return;}
   
   globals.wiki_revision = revision;
   globals.wiki_msg_id = msg_id;
   
   $('msg'+msg_id).innerHTML = '<div style="width:100%;margin:0 auto;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';

   var pars = 'thread_id=' + thread_id + '&revision=' + revision + '&total=' + globals.last_wiki_revision;
   var myAjax = new Ajax.Request('show_revision.php', {method: 'get', parameters: pars, onComplete: show_revision_response});
}

function show_revision_response(originalRequest) {
    var temp_string = originalRequest.responseText;

    var temp_array = temp_string.split("^?");    
		
	if (error_code(temp_array)) {$('msg'+globals.wiki_msg_id).innerHTML = temp_array[1]; return;}

	var tmp_result = temp_array[1].match(/&lt;&lt;&lt;FILE:(.*?)&gt;&gt;&gt;/g);
	if (tmp_result) {
	    var file_attachments = '';
		for (x = 0; x < tmp_result.length; x++) {
			tmp_filename = tmp_result[x].replace(/&lt;&lt;&lt;FILE:(.*?)\|.*?&gt;&gt;&gt;/,'$1');      
            tmp_fileid = tmp_result[x].replace(/&lt;&lt;&lt;FILE:.*?\|(.*?)&gt;&gt;&gt;/,'$1');  				
			file_attachments += '<a href="file.php?id=' +tmp_fileid+ '">' + tmp_filename + '</a><br>';			
		}
		if ($("attachment_row")) {
		   $("attachments").innerHTML = 'File attachments:<br>'+file_attachments;
		} else {
		   jQuery('#bigtable tr:first').after('<tr id="attachment_row" ><td class="colone"></td><td class="coltwo"></td><td id="attachments" class="colthree"></td><td class="colfour"></td></tr>');
		   $("attachments").innerHTML = 'File attachments:<br>'+file_attachments;
		}
		temp_array[1] = temp_array[1].replace(/(&lt;&lt;&lt;FILE:.*?&gt;&gt;&gt;)/g,'<span style="display:none;">$1</span>');
	} else {
	   if ($("attachment_row")) {
	      jQuery('#attachment_row').remove();
	   }
	}

	$('msg'+globals.wiki_msg_id).innerHTML = temp_array[1];

	$('wiki_info2').innerHTML = intext("revision")+": <span class='wikiscroll'><a onclick=show_revision("+globals.thread_watching+","+(globals.wiki_revision-1)+","+globals.wiki_msg_id+")>&#9668</a> "+
	(globals.wiki_revision+1)+" <a onclick=\"show_revision("+globals.thread_watching+","+(globals.wiki_revision+1)+","+globals.wiki_msg_id+")\">&#9658</a></span>" 

	if (account_info.status > 2) {		
		$('wiki_info4').innerHTML = intext("This revision by: ") 
	    + "<span class='member' title='" + parseInt(temp_array[2]).toString().toUpperCase() + "'>" 
		+ "<a id='Wprofile_boxR' href='popup_wiki.php?user_id=" + parseInt(temp_array[2]).toString().toUpperCase() + "&thread_id="+globals.thread_watching+"&page=0&post_position=0&type=0'>" + temp_array[3] + "</a>"		
		+ "</span>";
	} else {
		$('wiki_info4').innerHTML = intext("This revision by: ") 
	    + "<span class='member' title='" + parseInt(temp_array[2]).toString().toUpperCase() + "'>" 
		+ "<a id='Wprofile_boxR0' href='profile.php?user_id=" + parseInt(temp_array[2]).toString().toUpperCase() + "'>" + temp_array[3] + "</a>"		
		+ "</span>";
	}		

	if ($('edit_button'+globals.wiki_msg_id)) {	
		if (globals.last_wiki_revision == globals.wiki_revision) {
		   $('edit_button'+globals.wiki_msg_id).innerHTML = '<a onclick="javascript:edit_post('+globals.wiki_msg_id+')">'+intext('edit')+'</a>';
		} else {
		   $('edit_button'+globals.wiki_msg_id).innerHTML = '<a onclick="javascript:revert_wiki_post('+globals.thread_watching+","+globals.wiki_revision+','+globals.wiki_msg_id+')">'+intext('revert')+'</a>';
		}
	}
	
	if (account_info.status > 2) {
	   $('delete_button'+globals.wiki_msg_id).innerHTML = "<a onclick=\"javascript:delete_wiki_post("+globals.thread_watching+","+globals.wiki_revision+","+parseInt(temp_array[2])+")\">"+intext('delete')+"</a>";
	}
		
	$('wiki_op_date').innerHTML = temp_array[4];

	jQuery().ready(function() {
		jQuery("#Wprofile_boxR").fancybox({
				'width'				: 400,
				'height'			: 225,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
		});		
		jQuery("#Wprofile_boxR0").fancybox({
				'width'				: 800,
				'height'			: 400,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
		});				
	});		
}

function revert_wiki_post(thread_id,revision,msg_id) {
   //store the post in a global variable to be used in the event that the revert fails
   globals.temp_string = $('msg' + msg_id).innerHTML;
   globals.temp_number = msg_id;
  
   $('msg'+msg_id).innerHTML = '<div style="width:100%;margin:0 auto;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
   
   var pars = 'msg_id=' + msg_id + '&thread_id=' + thread_id + '&revision=' + revision;
   var myAjax = new Ajax.Request('revert_wiki_post.php', {method: 'get', parameters: pars, onComplete: revert_wiki_post_response});
}

function revert_wiki_post_response(originalRequest) {
    var temp_string = originalRequest.responseText;
    var temp_array = temp_string.split("^?");    
	
	if (error_code(temp_array)) {
	   alert(temp_array[1]);
	   $('msg' + globals.temp_number).innerHTML = globals.temp_string;
	   $('edit_button' + globals.temp_number).innerHTML = '<a onclick="javascript:edit_post('+globals.temp_number+')">edit</a>'
	   return;	
	}
	
	get_thread_page(globals.thread_watching,globals.current_page_of_thread,0,0);
}

function display_input(type,argument) {
	if ((settings.new_user_post_captcha ) && (account_info.status == 0)) {
		var part0 = '' +
		'<img id="postsiimage" align="left" style="padding-right: 5px; border: 0" src="captcha/securimage_show.php?sid=' + Math.random() + '"<br><br><br>' +		
		 '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="19" height="19" id="SecurImage_as3" align="top">'+
			'<param name="allowScriptAccess" value="sameDomain" />'+
			'<param name="allowFullScreen" value="false" />'+
			'<param name="movie" value="captcha/securimage_play.swf?audio=captcha/securimage_play.php&bgColor1=#777&bgColor2=#fff&iconColor=#000&roundedCorner=5" />'+
			'<param name="quality" value="high" />'+
			'<param name="bgcolor" value="#ffffff" />'+
			'<embed src="captcha/securimage_play.swf?audio=captcha/securimage_play.php&bgColor1=#777&bgColor2=#fff&iconColor=#000&roundedCorner=5" quality="high" bgcolor="#ffffff" width="19" height="19" name="SecurImage_as3" align="top" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />'+
		'</object>'+	
		'<a tabindex="-1" style="border-style: none" title="'+intext("Refresh Image")+'" onclick="document.getElementById(\'postsiimage\').src = \'captcha/securimage_show.php?sid=\' + Math.random(); return false"><img src="captcha/images/refresh.gif" alt="Reload Image" border="0" onclick="this.blur()"  /></a>'+
		'<br><br><br>'+intext("Enter the word you see")+':' +
		'<input id="postcaptcha" type="text" name="code" size="12"><br><br>';
	} else {
	    var part0 = '<input id="postcaptcha" style="display:none" type="text" name="code" size="12">';
	}   
	   
	var rich_text_buttons = '<input tabindex=5 type="button" class="rtbutton" onClick="javascript:insert_code(\'b\')" value="[b]")+"/>' +
	'<input tabindex=5 type="button" class="rtbutton" onClick="javascript:insert_code(\'i\')" value="[i]")+"/>' +
	'<input tabindex=5 type="button" class="rtbutton" onClick="javascript:insert_code(\'u\')" value="[u]")+"/>';   
	   
	var img_button =  '<input tabindex=5 type="button" class="rtbutton" onClick="javascript:insert_code(\'img\')" value="'+intext('[img]')+'"/>';
	var youtube_button = '<input tabindex=5 type="button" class="rtbutton" onClick="javascript:insert_code(\'youtube\')" value="'+intext('[youtube]')+'"/>';

	var tawidth = 730;
	if (globals.current_width < 775) {
	   tawidth = 730 + (globals.current_width - 775);
	}	
	
	var upload_button, end_part;
	
	//1: post in an existing thread, 2: OP of a thread, 3: OP of a private thread, 4: OP of an article
	if (type == 1) {
	   upload_button = '<span id="uploadbutton"><input tabindex=5 type="button" class="rtbutton" onClick="javascript:display_file_upload('+argument+')" name="diu" value="'+intext("Attach File")+'"/></span>'
	                + '<span id="upload_info"></span>';
	   end_part = '<br><textarea style="width:'+tawidth+'px;" tabindex=0 class="theinputbox" id="theinputbox" cols=90 rows=5></textarea><br>' + part0 +
			   '<input type="button" class="postbutton" onClick="javascript:post_msg('+argument+')" id="thepostbutton" value="'+intext('Post')+'"/>';
	} else if (type == 2) {
	   upload_button = '<span id="uploadbutton"><input tabindex=5 type="button" class="rtbutton" onClick="javascript:display_file_upload2('+argument+')" name="diu" value="'+intext("Attach File")+'"/></span>'
	                + '<span id="upload_info"></span>';	   
       end_part = '<br><textarea style="width:'+tawidth+'px;" tabindex=0 class="theinputbox" id="theinputbox" cols=90 rows=10></textarea><br>' + part0 +
			   '<input type="button" class="postbutton" onClick="javascript:post_thread('+argument+')" id="thepostbutton" value="'+intext('Post Thread')+'"/>';	   
	} else if (type == 3) {
	   upload_button = '<span id="uploadbutton"><input tabindex=5 type="button" class="rtbutton" onClick="javascript:display_file_upload2('+argument+')" name="diu" value="'+intext("Attach File")+'"/></span>'
	                + '<span id="upload_info"></span>';	   
       end_part = '<br><textarea style="width:'+tawidth+'px;" tabindex=0 class="theinputbox" id="theinputbox" cols=90 rows=10></textarea><br>' + part0 +
			   '<input type="button" class="postbutton" onClick="javascript:post_private_thread()" id="thepostbutton" value="'+intext('Post Thread')+'"/>';	   
	} else if (type == 4) {  
	   upload_button = '<span id="uploadbutton"><input tabindex=5 type="button" class="rtbutton" onClick="javascript:display_file_upload2('+argument+')" name="diu" value="'+intext("Attach File")+'"/></span>'
	                + '<span id="upload_info"></span>';	   
       end_part = '<br><textarea style="width:'+tawidth+'px;" tabindex=0 class="theinputbox" id="theinputbox" cols=90 rows=15></textarea><br>' + part0 +
			   '<input type="button" class="postbutton" onClick="javascript:post_thread('+argument+')"" id="thepostbutton" value="'+intext('Post Article')+'"/>';	   
	}	

	if (!settings.allow_rich_text) {rich_text_buttons = "";}
	if (!settings.image_linking_allowed) {img_button = "";}
	if (!settings.youtube_linking_allowed) {youtube_button = "";}	
	if (settings.status_to_embed > account_info.status) {img_button = ""; youtube_button = "";}
	if (!settings.file_upload_allowed) {upload_button = "";}	
	if ((settings.status_to_upload_file > account_info.status) || (!(settings.file_upload_in_pt_allowed) && globals.current_forum == 12)) {upload_button = "";}
	if ((type == 3) && (!settings.file_upload_in_pt_allowed)) {upload_button = "";}
	
	return rich_text_buttons + img_button + youtube_button + upload_button + end_part;
}

function insert_code(code) {
	var temp_string = $("theinputbox").value;
	$("theinputbox").value = temp_string + "["+code+"][/"+code+"]";	  
}

function quote_post(message_id,author_name,total_pages,new_page_kludge) {
    if (new_page_kludge != 1) {
        globals.quoted_text = convert_to_input($("msg"+message_id).innerHTML);
	   
	    globals.quoted_text = globals.quoted_text.replace(/<<<FILE.*>>>/ig,'');        
		   
		if (total_pages != (globals.current_page_of_thread + 1)) {
		   get_thread_page(globals.thread_watching,total_pages-1,0,0);	         
		}		   
    }

    while(globals.get_thread_page_done == 0) {
		self.setTimeout("quote_post("+message_id+",\""+author_name+"\","+total_pages+",1)",500);
		return;
    }   	

	$("thepostbutton").focus();
	$("theinputbox").focus();	
	$("theinputbox").value = '[QUOTE]'+author_name+' '+intext("said")+':\n\n'+globals.quoted_text+'[/QUOTE]\n\n';		
}

function expand_pagenumbar(total_pages) {
	globals.pagenumbar_expanded = 1;

	var tstring = "";
	for (i = 0; i < total_pages; ++i) {
		if (i == thread_offset) {
		   tstring = tstring + "<a class='pageclassselected' id=\"pagenumber\">" + (i+1) + "</a> ";           
		} else {
		   tstring = tstring + "<a class='pageclass' id=\"pagenumber\" onclick=\"javascript:get_thread_page(" +globals.thread_watching+ "," + i + ",0,0)\">" + (i+1) + "</a> ";
		}
	}

   $('topbar').innerHTML = '<p class="pagenumbar" align="center">' +  tstring + '</p>';
   $('bottombar').innerHTML = '<p class="pagenumbar" align="center">' +  tstring + '</p>';		
}

function edit_post(post_id) {
   var temp_string = convert_to_input($('msg' + post_id).innerHTML);

   //store the post in a global variable to be used in the event that an edit fails
   globals.temp_string = $('msg' + post_id).innerHTML;
   globals.temp_number = post_id;   
   
   if ( globals.current_forum_tab == "a" ) {
      $('msg' + post_id).innerHTML = '<textarea class="theinputbox" cols=50 rows=15  id="edit_box'+post_id+'"></textarea>'   
   } else {
      $('msg' + post_id).innerHTML = '<textarea class="theinputbox" cols=50 rows=5  id="edit_box'+post_id+'"></textarea>'
   }
      
   $('edit_box'+post_id).value = temp_string;
   $('edit_button'+post_id).innerHTML = '<a onclick="javascript:save_edit('+post_id+')">save</a>';
}

function save_edit(post_id){
   var pars = 'post_id=' + post_id + '&content_of_post=' + encodeURIComponent($("edit_box"+post_id).value)
   $('msg' + post_id).innerHTML = '<div style="width:100%;margin:0 auto;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
   var myAjax = new Ajax.Request('save_edit.php', {method: 'post', parameters: pars, onComplete: save_edit_response});
}

function save_edit_response(originalRequest) {
    var temp_string = originalRequest.responseText;

    var temp_array = temp_string.split("^?");    
	
	if (error_code(temp_array)) {
	   alert(temp_array[1]);
	   $('msg' + globals.temp_number).innerHTML = globals.temp_string;
	   $('edit_button' + globals.temp_number).innerHTML = '<a onclick="javascript:edit_post('+globals.temp_number+')">edit</a>'
	   return;
	}
	
	if (temp_array[3] == 1) {
       get_thread_page(globals.thread_watching,globals.current_page_of_thread,0,0);
	} else {
	   //current_wiki_revision should be raised
	   temp_array[2] = temp_array[2].replace(/\\r/g, '');  //replace is for IE
	   temp_array[2] = temp_array[2].replace(/(&lt;&lt;&lt;FILE:.*?&gt;&gt;&gt;)/g,'<span style="display:none;">$1</span>');
	   $('msg' + globals.temp_number).innerHTML = temp_array[2]
	   $('edit_button' + globals.temp_number).innerHTML = '<a onclick="javascript:edit_post('+globals.temp_number+')">edit</a>'
	   updater();
	}
	 
	if (parseInt(temp_array[4]) != 0) { 
	   mythreads_hash.set(globals.thread_watching,parseInt(temp_array[4])); 
	}
}

function convert_to_input(return_string) {
   var return_string = return_string.replace(/<br>/ig,'\n');
   var return_string = return_string.replace(/&nbsp;/ig,' ');      
   var return_string = return_string.replace(/<a target="_blank" href="(https?:\/\/)([\S]+)">(https?:\/\/)([\S]+)<\/a>/ig,'$1$2');        
   var return_string = return_string.replace(/<div class="inline_img" id="([0-9]+)_eimg".*?src="(.*?)".*?<\/div>/ig,'\[IMG\]$2\[/IMG\]');   
   var return_string = return_string.replace(/<img alt="\S*" title="\S*" src="(\S*)">/ig,'\[IMG\]$1\[/IMG\]');   
   var return_string = return_string.replace(/<div class="inline_img" id="(.*?)\|([0-9]+)_img".*?<\/div>/ig,'<<<image:$1>>>'); 
   var return_string = return_string.replace(/<div class="inline_img" id="(.*?)">.*?<\/div>/ig,'<<<image:$1>>>');               
   var return_string = return_string.replace(/<img title="?:(\S*):"? alt="?\S*"? src="\S*">/ig,':$1:');        
   var return_string = return_string.replace(/&gt;/ig,'>');        
   var return_string = return_string.replace(/&lt;/ig,'<');          
   var return_string = return_string.replace(/<div class="?quote"?>/ig,'[QUOTE]');  
   var return_string = return_string.replace(/<iframe class="?youtube-player.*?youtube.com\/embed\/([A-Za-z0-9\+_-]+)\?start=([0-9]+).*?<\/iframe>/ig,'[YOUTUBE]$1#t=$2[/YOUTUBE]');     
   var return_string = return_string.replace(/<iframe class="?youtube-player.*?youtube.com\/embed\/([A-Za-z0-9\+_-]+).*?<\/iframe>/ig,'[YOUTUBE]$1[/YOUTUBE]');     
   var return_string = return_string.replace(/<span style="display:none;"><<<(FILE:.*?)>>><\/span>/ig,'<<<$1>>>');  
   
   //just for IE
   var return_string = return_string.replace(/<a href="(https?:\/\/)([\S]+)" target="?_blank"?>(https?:\/\/)([\S]+)<\/a>/ig,'$1$2');        
   var return_string = return_string.replace(/<DIV id="?([0-9]+)_eimg"? class="?inline_img.*?src="(.*?)".*?<\/div>/ig,'\[IMG\]$2\[/IMG\]');  
   var return_string = return_string.replace(/<IMG title=\S* alt=\S* src="(\S*)">/ig,'\[IMG\]$1\[/IMG\]');      
   var return_string = return_string.replace(/<DIV id="?(.*?)\|([0-9]+)_img"? class="?inline_img.*?<\/DIV>/ig,'<<<image:$1>>>');     
   var return_string = return_string.replace(/<DIV id="?(.*?)"? class="?inline_img.*?<\/DIV>/ig,'<<<image:$1>>>');        
   var return_string = return_string.replace(/<SPAN style="DISPLAY: none;?"><<<(FILE:.*?)>>><\/SPAN>/ig,'<<<$1>>>');  

   var return_string = return_string.replace(/<\/div>/ig,'[/QUOTE]');  //todo: make this better
   
   return return_string;
}

function join_thread(thread_id){
   var pars = 'thread_id=' + thread_id;
   var myAjax = new Ajax.Request('join_pt.php', {method: 'get', parameters: pars, onComplete: join_pt_response});
}

function join_pt_response(orginalRequest){
   get_thread_page(globals.thread_watching,0,0,0);
}

function leave_pt(thread_id){
   var pars = 'thread_id=' + thread_id;
   var myAjax = new Ajax.Request('leave_pt.php', {method: 'get', parameters: pars, onComplete: leave_pt_response});
}

function leave_pt_response(orginalRequest){
   get_thread_page(globals.thread_watching,globals.current_page_of_thread,0,0);
}

function invite_to_pt() {
   $("invite_area").innerHTML = '<br><textarea class="theinputbox" cols=50 rows=2 style="height: 36px" id=\'private_user_list\'></textarea>' +
      '&nbsp;<input class="rtbutton" type="button" onClick="javascript:invite_to_thread()" value="'+intext('Invite')+'">' 

	jQuery().ready(function() {
		function log(event, data, formatted) {
			$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
		}
		function formatItem(row) {
			return row[0] + " (<strong>id: " + row[1] + "</strong>)";
		}
		function formatResult(row) {
			return row[0].replace(/(<.+?>)/gi, '');
		}

		jQuery("#private_user_list").autocomplete('search_users.php', {
			width: 300,
			multiple: true,
			matchContains: true,
			formatItem: formatItem,
			formatResult: formatResult
		});
	});
}

function invite_to_thread() {
   if ($("private_user_list").value == "") {
      $("invite_area").innerHTML = "<a onclick='javascript:invite_to_pt()'>"+intext("invite users")+"</a>";
	  return;
   }

   var pars = 'user_id=' + account_info.user_id + "&members=" + encodeURIComponent($("private_user_list").value) + "&thread_id=" + globals.thread_watching; 
   var myAjax = new Ajax.Request('invite.php', {method: 'get', parameters: pars, onComplete: invite_response});
}

function invite_response(originalRequest) {
    var temp_string = originalRequest.responseText;
    var temp_array = temp_string.split("^?");    

	if (error_code(temp_array)) {
	   alert(temp_array[1]);
	}

    get_thread_page(globals.thread_watching,globals.current_page_of_thread,0,0);
}

function watch_thread(thread_id,total_posts){
   var url = 'watch_thread.php';
   var pars = 'thread_id=' + thread_id + '&total_posts=' + total_posts;

   var myAjax = new Ajax.Request(url, {method: 'post',parameters: pars, onComplete: watch_thread_response });     
}

function watch_thread_response(originalRequest){
    var temp_string = originalRequest.responseText;

    var temp_array = temp_string.split("^?");    

	if (error_code(temp_array)) {
	   alert(temp_array[1]);
	   return;
	}
	
   mythreads_hash.set(thread_id,total_posts);
   
   temp_string = $('midrow').innerHTML; 
   var temp_string2 = temp_string.replace(/inline/,"foobar");
   var temp_string3 = temp_string2.replace(/none/,"inline");
   var temp_string4 = temp_string3.replace(/foobar/,"none");
   $('midrow').innerHTML = temp_string4;
   
   jQuery().ready(function() {
		jQuery("#thread_mod_box").fancybox({
			'width'				: 400,
			'height'			: 230,
			'autoScale'			: false,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'type'				: 'iframe'
		});		
	});	
}

function unwatch_thread(thread_id){
   var url = 'unwatch_thread.php';
   var pars = 'thread_id=' + thread_id;

   var myAjax = new Ajax.Request(url, {method: 'post',parameters: pars, onComplete: unwatch_thread_response });     
}

function unwatch_thread_response(originalRequest){
    var temp_string = originalRequest.responseText;

    var temp_array = temp_string.split("^?");    

	if (error_code(temp_array)) {
	   alert(temp_array[1]);
	   return;
	}

   mythreads_hash.unset(thread_id);
   
   temp_string = $('midrow').innerHTML; 
   temp_string2 = temp_string.replace(/inline/,"foobar");
   temp_string3 = temp_string2.replace(/none/,"inline");
   temp_string4 = temp_string3.replace(/foobar/,"none");
   $('midrow').innerHTML = temp_string4;
   
   jQuery().ready(function() {
		jQuery("#thread_mod_box").fancybox({
			'width'				: 400,
			'height'			: 230,
			'autoScale'			: false,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'type'				: 'iframe'
		});		
	});	
}

function display_file_upload(thread_id){
    var file_types = settings.allowed_file_types.toString()
    file_types = file_types.replace(/,/g,", ");
	
    $('uploadbutton').innerHTML = '<div id="fileblurb">'+intext("Upload file")+':<br>'+intext("allowed file types")+': '+ file_types + '</div>'
		+ ' <div id="iframe">'
		+ ' <iframe id="foo_frame_0" src="attach_file.php?thread_id='+thread_id+'" height="50px" frameborder="" scrolling="0"></iframe>'
		+ ' </div>'
		+ ' <div id = "list2"></div>';
}

//this is called from attach_file.php, after a file has been uploaded
function cleanup(return_number,file_name){
	if (return_number < 0){	
 	    $("upload_info").innerHTML += '<br>' + intext("File not uploaded, error: ") + return_number + " " + file_name;
    } else { 
		if ((file_name.match(/(jpg$|jpeg$|gif$|png$|bmp$)/i)) && (settings.thumbnail_uploaded_images)) {
		   var temp_string = $("theinputbox").value;
		   $("theinputbox").value = temp_string + "\n<<<image:" + file_name + ">>>";	  
		   $("upload_info").innerHTML += '<br>' + file_name + intext(" has been attached");				   
		}  else {
		   $("upload_info").innerHTML += '<br>' + file_name + intext(" has been attached");	
		}
	}

	if (globals.thread_watching) {
	   $("uploadbutton").innerHTML = '<input type="button" class="rtbutton" onClick="javascript:display_file_upload('+globals.thread_watching+')" name="diu" value="'+intext("Attach File")+'"/>';
	} else {
	   $("uploadbutton").innerHTML = '<input type="button" class="rtbutton" onClick="javascript:display_file_upload2('+globals.current_forum+')" name="diu" value="'+intext("Attach File")+'"/>';
	}
}

//this is called from upload_avatar.php, and only when an error has occurred
function cleanup2(return_number,message){
	alert(intext("File not uploaded, error: ") + return_number + " " + message);
}

function post_msg(thread_id) {
    if (globals.post_mutex) {
       return;
    }
    globals.post_mutex = 1;
    globals.thread_watching = thread_id
	
    var url = 'post.php';
    var input = $("theinputbox").value;
	var postcaptcha = $("postcaptcha").value;
		
    var pars = 'user_id=' + account_info.user_id + '&input=' + encodeURIComponent(input) + '&thread_id=' + thread_id + '&postcaptcha=' + postcaptcha;
	
	$('thepostbutton').disabled = true;
    var myAjax = new Ajax.Request(url, {method: 'post',parameters: pars, onComplete: post_response });  
}

function post_response(originalRequest) {
    globals.post_mutex = 0;
	$('thepostbutton').disabled = false;
	if ($("uploadbutton")) {$("uploadbutton").innerHTML = '<input type="button" class="rtbutton" onClick="javascript:display_file_upload('+globals.thread_watching+')" name="diu" value="'+intext("Attach File")+'"/>';}

    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");    
		
	if (error_code(temp_array)) {
		if ((settings.new_user_post_captcha ) && (account_info.status == 0)) {
		   reset_captcha();
		}  	
	    alert(temp_array[1]);
	    return;
	}

    $("theinputbox").value = "";

	//why not set mc_num_posts, too?
    mythreads_hash.set(globals.thread_watching,temp_array[2]);
    updater();

	if ((settings.new_user_post_captcha) && (account_info.status == 0)) {
	   reset_captcha();
    }  
}

function reset_captcha() {
   document.getElementById('postsiimage').src = 'captcha/securimage_show.php?sid=' + Math.random();
   $('postcaptcha').value = "";
}

function auto_login()
{
    globals.attempting_auto_login = true;
    var url = 'auto_login.php';	
    var myAjax = new Ajax.Request(url, {method: 'post', parameters: "", onComplete: autologinResponse});
}

function login()
{
    if (globals.login_mutex) {
       return;
    }
    globals.login_mutex = 1;  

	var d = document.getElementById("login_iframe");
	var name = d.contentWindow.document.getElementById("username").value
	var pass = d.contentWindow.document.getElementById("password").value
	var rem = d.contentWindow.document.getElementById("rem").checked	
    var pars = 'username=' + encodeURIComponent(name) + '&password=' + encodeURIComponent(pass) + '&rem=' + rem;

	globals.temp_string = $('message_center').innerHTML;
	$('message_center').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
	
    var myAjax = new Ajax.Request('login.php', {method: 'post', parameters: pars, onComplete: loginResponse});
}

function makenewuser()
{
    if (globals.login_mutex) {
       return;
    }
    globals.login_mutex = 1;  
	
    var url = 'makenewuser.php';
    var newuser = $("newuser").value;
    var newpassword0 = $("newpassword0").value;
    var newpassword1 = $("newpassword1").value;
    var captcha = $("captcha").value;	
    var pars = 'newuser=' + encodeURIComponent(newuser) + '&newpassword0=' + encodeURIComponent(newpassword0) + '&newpassword1=' + encodeURIComponent(newpassword1) + '&captcha=' + captcha;

    var myAjax = new Ajax.Request(url, {method: 'post', parameters: pars, onComplete: makenewuserResponse});
}

function autologinResponse(originalRequest) {
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;

    var temp_array = temp_string.split("^?");
	
    if (!error_code(temp_array)) {
       process_login(temp_array);
    }
	globals.attempting_auto_login = false;
}

function loginResponse(originalRequest)
{
	globals.login_mutex = 0;
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;

    var temp_array = temp_string.split("^?");
	
    if (!error_code(temp_array)) {
       process_login(temp_array);
    } else {
	   alert(temp_array[1]);
	   $('message_center').innerHTML = globals.temp_string; 
	   document.getElementById('siimage').src = 'captcha/securimage_show.php?sid=' + Math.random();
    }
}

function makenewuserResponse(originalRequest)
{
	globals.login_mutex = 0;
	
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;

    var temp_array = temp_string.split("^?");
	
    if (!error_code(temp_array)) {
        process_login(temp_array);
		if (settings.first_tab_enabled) {
		  show_frame(settings.first_tab_location,settings.first_tab_is_div);
		} else if (settings.second_tab_enabled) {
		  show_frame(settings.second_tab_location,settings.second_tab_is_div);
		} else if (settings.enable_forums) {
		  get_tab("forum"+ (6 - Math.ceil((settings.total_forums / settings.forums_per_tab)))); 
		} else if (settings.enable_articles) {
		  get_tab("articles");
		} else if (settings.enable_private_threads) {
		  get_tab("pt");  
		} else if (settings.second_last_tab_enabled) {
		  show_frame(settings.second_last_tab_location,settings.second_last_tab_is_div);
		} else if (settings.last_tab_enabled) {
		  show_frame(settings.last_tab_location,settings.last_tab_is_div);
		}		
    } else {
	   alert(temp_array[1]);
	   document.getElementById('siimage').src = 'captcha/securimage_show.php?sid=' + Math.random();
	}
}

function process_login(login_array) {
	account_info.username = login_array[1];
	account_info.user_id = parseInt(login_array[2]);
	account_info.status = parseInt(login_array[5]);  
	account_info.theme = login_array[6];
	account_info.avatar_number = parseInt(login_array[7]);
	account_info.total_avatars = parseInt(login_array[8]);
	account_info.my_threads = login_array[9];

	$('Panel').innerHTML = '<div id="myavatar" class="obbimage"></div>' +
'<div class="obbtitle">'+settings.website_title+'</div>' +
'<div class="obbblurb">'+settings.website_blurb+'</div>' +  
'<br><br><br><div id="message_center" class="message_center1"></div>';

	set_my_threads(account_info.my_threads); 
	set_theme(account_info.theme);  
	
	if (!settings.persistent_logo && (settings.avatars_allowed) && !(account_info.avatar_number == 0 && settings.new_user_avatar == 0)){
	   set_corner_avatar(account_info.user_id,account_info.avatar_number);
	} else {
	   $('myavatar').innerHTML = '<img src="' +settings.logo_image+ '">';
	}
	
	var extra = "";
	if (account_info.status > 2) { extra = '<li><a onclick="javascript:gen_mod_panel()">'+intext('Admin')+'</a></li>';}
	
	if (settings.enable_helpmenu) {
	   helpbutton = '<li><a href="#" onmouseover="mopen(\'m4\')" onmouseout="mclosetime()">'+settings.helpmenu_name+'</a>' +
	      '<div id="m4" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">' + help_submenu() + '</div></li>';
	} else {
	   helpbutton = '';
	}
	
	$('menu').setAttribute("style","");  
	$('menu').innerHTML = '<ul id="sddm">' + extra +
		'<li><a onclick="javascript:gen_user_list()">'+intext('Users')+'</a>' +
		'</li>' +
		'<li><a onclick="javascript:gen_profile()">'+intext('Profile')+'</a>' +
		'</li>' +
		'<li><a onclick="javascript:gen_settings()">'+intext('Settings')+'</a></li>' +
		'</li>' + helpbutton +
		'<li><a onclick="javascript:logout()">'+intext('Sign Out')+'</a>' +
		'</li>' +
	'</ul>';
	
	globals.is_connected = true;		

	if ((settings.site_down == false) || (!settings.enable_private_threads && !settings.enable_forums && !settings.enable_articles)) { 
	   globals.pe = new PeriodicalExecuter(updater, settings.update_frequency);  
	}
	
	if (cache.forum_is_set && !settings.enable_private_threads) { updater(); }	
	if (!cache.forum_is_set && (settings.enable_forums || settings.enable_articles) && !globals.popforum_mutex) {globals.popforum_mutex = 1; globals.hitf_semaphore++; var myAjax = new Ajax.Request('popforum.php', {method: 'get', parameters: "", onComplete: populate_forum});}  
	if (settings.enable_private_threads && !globals.poppt_mutex) {globals.poppt_mutex = 1; globals.hitf_semaphore++; var myAjax2 = new Ajax.Request('poppt.php', {method: 'get', parameters: "", onComplete: populate_pt});}  

    resize_site();
}

function show_frame(filename,is_div){
	set_display("top_area:none","midrow:none","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
	globals.current_forum_tab = "None";	
	globals.content_height = "84%";
	$('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 
	hide_footer();
    set_class();
	
	//if the filename shows we're loading one of the 4 horizontal tabs, set the css to highlight that tab
	var horizontal_tabs = ["first_tab","second_tab","second_last_tab","last_tab"];
	for (var i = 0; i < horizontal_tabs.length; i++) {
	   if (filename == eval("settings." + horizontal_tabs[i] + "_location")) {
	      set_class(horizontal_tabs[i]);
	   }
	}

	if (is_div) {
		$('content_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
		var myAjax = new Ajax.Request("get_frame.php", {method: 'post', parameters: "page=" + filename, onComplete: show_frame_response}); 
	} else {
		var iframewidth = 1030;
		if (globals.current_width < 1075) {
		   iframewidth = 1030 + (globals.current_width - 1075);
		}		
		$('content_area').setAttribute("style","display:block;width:"+iframewidth+"px;max-width:"+iframewidth+"px;");	
		$('content_area').innerHTML = '<iframe id="frontpageiframe" frameborder="" style="height: 600px;width: 100%;" src="'+filename+'"></iframe>';	
	}
}

function show_frame_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");

   error_code(temp_array);

   $('content_area').setAttribute("style","display:block;max-width:1000px;");
   $('content_area').innerHTML = temp_array[1];
}   

function gen_emotes() {
	set_display("top_area:none","midrow:none","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
	globals.current_forum_tab = "None";
	set_class("");   
	hide_footer();
	$('content_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
    var myAjax = new Ajax.Request("get_emotes_list.php", {method: 'post', parameters: "", onComplete: emotes_list_response});
}

function emotes_list_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   temp_array = temp_string.split("^?");
	
   var emote_list = "";

   if (error_code(temp_array)) {
      alert(temp_array[1]);
	  return;
   }	  
   
   var emote_count = parseInt(temp_array[1]);
   
   for (i = 0; i < emote_count; i++) {
      var name = temp_array[i+2];
      name = name.replace(/\..*/ig,'');   
      name = ':' + name + ':';
      emote_list += '<tr> <td class="coluserlistone">' + name + 
       '</td> <td class="coluserlisttwo">' + '<img title="'+name+'" src="emotes/' +  temp_array[i+2] + '"></td></tr>';
   }
   
   globals.content_height = "";
   $('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 

   $("content_area").innerHTML = '<h3>Emote List</h3><table cellspacing="0" id="bigtable" style="width:600px;"> <tbody>' + emote_list + '</tbody> </table>';

   set_footer();
}

function set_my_threads(my_threads){
   if (my_threads == "") {return;}
   var myt_array = my_threads.split(",");

   for (i = 1; i < myt_array.length; i++) {
      var temp = myt_array[i];
      var myArray = temp.split(":");
      mythreads_hash.set(parseInt(myArray[0]),parseInt(myArray[1]));         
   }
}

function set_corner_avatar(id,avatar) {
    if (avatar == 0) {
	   var display = '<img src="'+settings.default_avatar+'" width="40" height="40" >';
	} else {
       var display = '<img src="file.php?uid=' +id+ '&avatar_number=' +avatar+ '" width="40" height="40" >';
    }
	$('myavatar').innerHTML = display;
}

//code for the Help drop-down button
//Copyright 2006-2007 javascript-array.com
var ddtimeout       = 500;
var ddclosetimer	= 0;
var ddmenuitem      = 0;

//open hidden layer
function mopen(id)
{	
	// cancel close timer
	mcancelclosetime();

	// close old layer
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';

	// get new layer and show it
	ddmenuitem = document.getElementById(id);
	ddmenuitem.style.visibility = 'visible';
}

// close showed layer
function mclose()
{
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
}

// go close timer
function mclosetime()
{
	ddclosetimer = window.setTimeout(mclose, ddtimeout);
}

// cancel close timer
function mcancelclosetime()
{
	if(ddclosetimer)
	{
		window.clearTimeout(ddclosetimer);
		ddclosetimer = null;
	}
}

function gen_mod_panel() {
    set_display("top_area:none","midrow:inline","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
	set_class();
    globals.current_forum_tab = "None";
    globals.pagenumbar_expanded = 0;
	
	$('midrow').innerHTML = '<br><table cellspacing="0" style="max-width:850px;"> <tbody> <tr>' 
	+ '<td id="showsitesettingsbutton" class="modbutton"><a onclick=\"show_site_settings()\">'+intext('Site Settings')+'</a></td>' 
	+ '<td id="showlogbutton" class="modbutton"><a onclick=\"show_log(0)\">'+intext('System Log')+'</a></td>' 
	+ '<td id="showfilesbutton" class="modbutton"><a onclick=\"show_files(0)\">'+intext('Uploaded Files')+'</a></td>'
	+ '<td id="showlockdownbutton" class="modbutton"><a onclick=\"show_lockdown_button()\">'+intext('Lockdown Button')+'</a></td>';
	
	hide_footer();
	
	show_site_settings();
}

function show_site_settings() {
    set_display("top_area:none","midrow:inline","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
	
	$("showsitesettingsbutton").className = "modbuttonselected";
	$("showlogbutton").className = "modbutton";
	$("showfilesbutton").className = "modbutton";
	$("showlockdownbutton").className = "modbutton";
	
	$('content_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
    var myAjax = new Ajax.Request("get_site_settings.php", {method: 'post', parameters: '', onComplete: get_site_settings_response});
}

function get_site_settings_response(originalRequest) {
	var temp_string = originalRequest.responseText;
	var temp_array = temp_string.split("^?");

	if (error_code(temp_array)) {
	   alert(temp_array[1]);
	   return;
	}

	var helpline = new Hash();
	helpline.set("website_title",intext("Title of website"));
    helpline.set("website_blurb",intext("The text that appears at the bottom of the webpage title, may be blank"));
	helpline.set("website_url",intext("URL of your website"));
	helpline.set("welcome_thread",intext("All new users will automatically watch the thread specified here.  Set to 0 if you don't want one"));
	helpline.set("default_theme",intext("Default theme of your site.  For other choices, see the css directory"));
    helpline.set("language",intext("Language.  See lang/README for info on translations"));
	helpline.set("time_zone",intext("Example: America/Los_Angeles, Europe/London.  See http://www.php.net/manual/en/timezones.php for complete list"));
	helpline.set("logo_image",intext("Image file of the logo that appears in the top left corner"));
	helpline.set("footer_text",intext("The text that appears at the bottom of the webpage, may be blank"));
	helpline.set("banner_space",intext("Amount of vertical space given for the area between the top horizontal menu and the tabs.  After assigning more space here, you can insert html in the banner_area div in index.php and thread.php"));
    helpline.set("connect_with_username",intext("Set to allow users to connect by entering a username and password"));
    helpline.set("connect_with_fb",intext("Set to allow users to connect using their Facebook account.  See the file README.facebook"));
	helpline.set("fb_appId",intext("Facebook App Id.  See the file README.facebook for instructions on how to get one"));
	helpline.set("fb_secret",intext("Facebook Secret.  See the file README.facebook for instructions on how to get one"));
	helpline.set("fb_references_needed",intext("This can be set to make your site more exclusive.  In order for a new member to join, they'll need this many local members who are Facebook friends with them"));
	helpline.set("minimum_status_of_fb_reference",intext("In order to be considered a reference, a local member must have a status level at least this high"));
    helpline.set("connect_with_linkedin",intext("Set to allow users to connect using their LinkedIn account.  See the file README.linkedin"));
	helpline.set("linkedin_api_key",intext("LinkedIn API key.  See the file README.linkedin for instructions on how to get one"));
	helpline.set("linkedin_secret_key",intext("LinkedIn secret key.  See the file README.linkedin for instructions on how to get one"));
	helpline.set("linkedin_references_needed",intext("This can be set to make your site more exclusive.  In order for a new member to join, they'll need this many local members who are LinkedIn contacts with them"));
	helpline.set("minimum_status_of_linkedin_reference",intext("In order to be considered a reference, a local member must have a status level at least this high"));
	helpline.set("enable_articles",intext("Enable wiki articles"));
	helpline.set("enable_forums",intext("Enable forums"));
	helpline.set("must_login_to_see_forum",intext("If set, you'll need to sign in to view the forums and articles"));	
	helpline.set("enable_private_threads",intext("Private threads are used as private discussion between two or more members"));
	helpline.set("allow_rich_text",intext("If set, a poster may use <b>, <i>, and <u> in posts"));
	helpline.set("image_linking_allowed",intext("If set, a user may put an embedded, off-site image into their post, ex: [IMG]http://www.foo.com/foo.jpg[/IMG]"));
	helpline.set("youtube_linking_allowed",intext("If set, a user may embed a youtube video, ex: [YOUTUBE]DlkdtS8OFlA[/YOUTUBE]"));	
	helpline.set("emotes_allowed",intext("If set, a user can enter :lol: and it'll replace it with an image.  See the directory ./emotes"));
	helpline.set("user_block_list",intext("If set, a user may specify users that can never post in threads they start"));
	helpline.set("word_filter",intext("If set, a word filter is active, see word_filter.php for the list"));		
	helpline.set("avatars_allowed",intext("If set, avatars are allowed on user profiles and show up next to their postings"));
	helpline.set("animated_avatars",intext("If set, animated gifs may be used as avatars, if false, they won't animate"));
	helpline.set("allow_username_change",intext("If set, a user may change their username"));
	helpline.set("allow_avatar_change",intext("If set, a user may change their avatar"));
	helpline.set("file_upload_allowed",intext("If set, a user may upload a file attachment with their post"));
	helpline.set("file_upload_in_pt_allowed",intext("If set, a user may upload a file attachment with their post in a private thread"));
	helpline.set("allowed_file_types",intext("Allowed file types for upload"));
	helpline.set("thumbnail_uploaded_images",intext("If set, attached images will be thumbnailed"));
	helpline.set("permalinks_enabled",intext("If set, each public thread and article will have a permalink for easy retrieval, ex: http://www.foo.com/thread.php?id=889.  Must be enabled if you've allowed your forum to be indexable"));	
	helpline.set("new_accounts_allowed",intext("If you don't want to allow new accounts to be made, set this to false"));
	helpline.set("may_undelete",intext("If set, a moderator may undelete soft-deleted posts or threads"));
	helpline.set("status_to_start_threads",intext("Users must have a status at least this high to start new threads"));
	helpline.set("status_to_create_articles",intext("Users must have a status at least this high to create articles"));
	helpline.set("status_to_upload_file",intext("Status needed to upload a file in your post"));
	helpline.set("status_to_embed",intext("Status needed to be able to post [IMG][/IMG] and [YOUTUBE][/YOUTUBE]"));
	helpline.set("status_to_have_block_list",intext("Status needed to be able to use a block list"));	
	helpline.set("status_to_start_pt",intext("Status needed to start a private thread.  Note: anyone can be invited to a private thread, this only determines who may start one"));
	helpline.set("status_to_have_avatar",intext("Status needed to upload an avatar"));
	helpline.set("status_to_see_fb_li_profile",intext("Status needed to view a user's Facebook or LinkedIn profile link on their local profile"));
	helpline.set("status_to_hard_delete",intext("Deletions of posts or threads from moderators with this status level will be permanent deletions.  Set to either 3 "
	+"(all moderators), 5 (admin only) or 6 (everyone soft deletes).  Soft deletions cause the post or thread to remain in the database until it's been pruned after about "
	+ "2 weeks.  Note: file attachments are always hard deleted."));
	helpline.set("forum_tab_names",intext("Names of the tabs used by the forum.  How many forum tabs are displayed depends on how many forums you have set, and how many "
	+ "forums per tab (see Miscellaneous Settings).  Even if you're using less than six, they all need to be here, just edit the ones relevant to your configuration"));
	helpline.set("forum_topic_names",intext("Names of the forums.  Even if you've configured less than 11 forums, they all need to be here, just edit the ones relevant to your configuration"));
	helpline.set("name_of_status_2",intext("This is the name of the status with a rank above 'regular user' (1) but below 'moderator' (3). "));
	helpline.set("avatars_same_size",intext("If set, all avatars will have the dimensions of max_avatar_dimensions"));
	helpline.set("max_avatar_dimensions",intext("Uploaded avatars will be resized if they're larger than these dimensions"));
	helpline.set("default_avatar",intext("Default user avatar"));
	helpline.set("system_avatar",intext("Avatar for the System account"));
	helpline.set("narrow_width",intext("When the browser width is less than this, the side panel becomes a horizontal bar (if you prefer to always have the side panel be horizontal, set this to a large number)"));
	helpline.set("persistent_logo",intext("If set, the corner logo will remain after a user logs in.  Otherwise the corner logo is replaced with the user's avatar after they sign in"));
	helpline.set("update_frequency",intext("Number of seconds between the update poll to the server"));
	helpline.set("thumb_width",intext("Images with a width greater than this will be thumbnailed"));
	helpline.set("thumb_height",intext("Images with a height greater than this will be thumbnailed"));
	helpline.set("max_uploaded_file_size",intext("Maximum size of a file attached to a posting, in bytes"));
	helpline.set("max_post_length",intext("Maximum number of characters a post can have."));
	helpline.set("new_account_captcha",intext("A user will have to enter a captcha when creating a new account"));
	helpline.set("new_user_post_captcha",intext("A new user will have to enter a captcha at each post"));
	helpline.set("weak_captcha",intext("If set, the captcha will use a list of the 500 most common English words, if set to 0 it will be a random string"));
	helpline.set("captcha_distortion",intext("How distorted the captcha appears, 1 = slight, 2 = moderate, 3 = extreme"));
	helpline.set("new_account_limit",intext("This to the maximum number of accounts that can be created from one IP address.  If you don't want a limit, set it to -1"));
	helpline.set("flood_time",intext("These two settings control flood attacks.  If more than $flood_num_posts posts happen in $flood_time seconds, then the post is rejected"));
	helpline.set("flood_num_posts",intext("These two settings control flood attacks.  If more than $flood_num_posts posts happen in $flood_time seconds, then the post is rejected"));
	helpline.set("total_forums",intext("Total forums, between 1 and 11"));
	helpline.set("forums_per_tab",intext("Number of forums that appear on one tab, must be between 1 and 4"));	
	helpline.set("edit_time",intext("Number of minutes during which you can edit a post, set to 0 to never allow editing, set to -1 for an unlimited amount of time to edit"));
	helpline.set("max_file_attachments",intext("Maximum number of file attachments in a post"));
	helpline.set("max_username_length",intext("Maximum number of characters a username can be"));
	helpline.set("posts_per_page",intext("Number of posts displayed on one thread page"));
	helpline.set("img_url_whitelist",intext("If not empty, only urls from the domain names listed here will turn into inline images using [IMG][/IMG].  Seperate each domain name with a comma"));
	helpline.set("img_url_blacklist",intext("If not empty, urls from the domain names listed here will not turn into inline images using [IMG][/IMG].  Seperate each domain name with a comma"));	
	helpline.set("user_info_permanentness",intext("If set, when a user makes a posting, that post will always contain whatever their username and avatar were at that time, even if they "
    + "change it at a later time.  If set to 0, old postings will be updated if the user changes their name or avatar"));
	helpline.set("profile_text_limit",intext("Maximum number of characters allowed in a user's profile.  Set to 0 to not allow text in user's profiles."));
	helpline.set("auto_close_thread",intext("This is the option to cause old or inactive threads to automatically close.  The first number is the number of days after the thread was created, " 
	+ "the second number is number of days the thread has been inactive.  Examples: Close thread 20 days after creation = 20,0 "    
	+ "Close thread after 3 days of inactivity = 0,3  Close thread after 3 days of inactivity and at least 20 days after creation = 20,3 "
	+ "To never auto-close threads, set this to 0,0")); 
	helpline.set("prune_watchlist",intext("If set, threads that have been closed or deleted will be removed from a user's watchlist after at least a week"));
	helpline.set("prune_deleted_threads",intext("If set, threads that have been set to state 'deleted' will be removed from the database after at least 2 weeks, along with all of its posts and file attachments"));
	helpline.set("prune_deleted_posts",intext("If set, posts that have been set to state 'deleted' will be removed from the database after at least 2 weeks"));
	helpline.set("datetime_format",intext("Format of date and time, see http://php.net/manual/en/datetime.formats.date.php  There must always be a space after the date portion and no space before it"));
	helpline.set("allow_hotlinking",intext("To prevent other sites from hotlinking uploaded content, set this to false"));
	helpline.set("strip_exif",intext("Digital cameras often insert extra information into the image, known as exif data, if set, this will strip out all exif data from uploaded jpeg images"));
    helpline.set("truncate_name",intext("Name privacy when signing in using Facebook or LinkedIn"));
	helpline.set("must_login_to_see_profile",intext("If set, you must sign in to view a member's profile"));	
    helpline.set("new_user_avatar",intext("For new users who connect using a username, what their first avatar is, if any"));	
	helpline.set("fb_li_welcome_pt",intext("If set, when a user first connects with either LinkedIn or Facebook, a private thread containing local members who are also contacts with "
    + "with them on linkedin/facebook will be generated for them"));
    helpline.set("linkedin_request_connections",intext("If set, during LinkedIn authentication the user will be asked to give their connections information in addition to their basic information.  "
    + "This only needs to be set if you're using linkedin_references_needed or fb_li_welcome_pt"));
	helpline.set("prune_session_table",intext("Session rows that have been inactive for over a month will be deleted"));	
    helpline.set("prune_closed_threads",intext("Closed threads will be deleted from the database after the number of days specified.  To never auto-delete "
	+ "closed threads set it to -1.  Note: the system does this check once a week, so deletions won't happen exactly at the time specified"));
	helpline.set("first_tab_enabled",intext("Enable the first tab") );
	helpline.set("first_tab_name",intext("Name of first tab"));
	helpline.set("first_tab_location",intext("File that the first tab will load"));
	helpline.set("first_tab_is_div",intext("Displays the contents in a div or in a iframe"));
	helpline.set("first_tab_indexable",intext("Content can be indexed by web search bots"));
	helpline.set("second_tab_enabled",intext("Enable the second tab") );
	helpline.set("second_tab_name",intext("Name of second tab"));
	helpline.set("second_tab_location",intext("File that the first tab will load"));
	helpline.set("second_tab_is_div",intext("Displays the contents in a div or in a iframe"));
	helpline.set("second_tab_indexable",intext("Content can be indexed by web search bots"));
	helpline.set("enable_articles",intext("Enable articles"));
	helpline.set("articles_tab_name",intext("Name of the tab that displays the articles"));
	helpline.set("articles_indexable",intext("Allow articles to be be indexed by web search bots"));	
	helpline.set("forums_indexable",intext("Allow forum to be be indexed by web search bots"));	
	helpline.set("pt_tab_name",intext("Name of private threads tab"));
	helpline.set("second_last_tab_enabled",intext("Enable the second-last tab") );
	helpline.set("second_last_tab_name",intext("Name of second-last tab"));
	helpline.set("second_last_tab_location",intext("File that the second-last tab will load"));
	helpline.set("second_last_tab_is_div",intext("Displays the contents in a div or in an iframe"));
	helpline.set("second_last_tab_indexable",intext("Content can be indexed by web search bots"));
	helpline.set("last_tab_enabled",intext("Enable the last tab") );
	helpline.set("last_tab_name",intext("Name of last tab"));
	helpline.set("last_tab_location",intext("File that the last tab will load"));
	helpline.set("last_tab_is_div",intext("Displays the contents in a div or in an iframe"));
	helpline.set("last_tab_indexable",intext("Content can be indexed by web search bots"));
	helpline.set("enable_helpmenu",intext("Enable corner menu"));
	helpline.set("helpmenu_name",intext("Name of corner menu"));
	helpline.set("helpmenu1_enabled",intext("Enable") );
	helpline.set("helpmenu1_name",intext("Name of menu item"));
	helpline.set("helpmenu1_location",intext("File that menu item will load"));
	helpline.set("helpmenu1_is_div",intext("Displays the contents in a div or in an iframe"));
	helpline.set("helpmenu1_indexable",intext("Content can be indexed by web search bots"));
	helpline.set("helpmenu2_enabled",intext("Enable") );
	helpline.set("helpmenu2_name",intext("Name of menu item"));
	helpline.set("helpmenu2_location",intext("File that menu item will load"));
	helpline.set("helpmenu2_is_div",intext("Displays the contents in a div or in an iframe"));
	helpline.set("helpmenu2_indexable",intext("Content can be indexed by web search bots"));
	helpline.set("helpmenu3_enabled",intext("Enable") );
	helpline.set("helpmenu3_name",intext("Name of menu item"));
	helpline.set("helpmenu3_location",intext("File that menu item will load"));
	helpline.set("helpmenu3_is_div",intext("Displays the contents in a div or in an iframe"));
	helpline.set("helpmenu3_indexable",intext("Content can be indexed by web search bots"));
	helpline.set("helpmenu4_enabled",intext("Enable") );
	helpline.set("helpmenu4_name",intext("Name of menu item"));
	helpline.set("helpmenu4_location",intext("File that menu item will load"));
	helpline.set("helpmenu4_is_div",intext("Displays the contents in a div or in an iframe"));
	helpline.set("helpmenu4_indexable",intext("Content can be indexed by web search bots"));
	helpline.set("helpmenu5_enabled",intext("Enable") );
	helpline.set("helpmenu5_name",intext("Name of menu item"));
	helpline.set("helpmenu5_location",intext("File that menu item will load"));
	helpline.set("helpmenu5_is_div",intext("Displays the contents in a div or in an iframe"));
	helpline.set("helpmenu5_indexable",intext("Content can be indexed by web search bots"));
	helpline.set("helpmenu6_enabled",intext("Enable") );
	helpline.set("helpmenu6_name",intext("Name of menu item"));
	helpline.set("helpmenu6_location",intext("File that menu item will load"));
	helpline.set("helpmenu6_is_div",intext("Displays the contents in a div or in an iframe"));
	helpline.set("helpmenu6_indexable",intext("Content can be indexed by web search bots"));
	helpline.set("articles_topic_name",intext("Topic title that appears directly above the articles"));
	helpline.set("pt_topic_name",intext("Topic title that appears directly above the private threads"));
	
	var count = parseInt(temp_array[0]);
	var html = "";		
	var inputfield = "";
	
	if (account_info.status == 5) {
		for (var i = 0; i < count; i++) {
			var offset = 1+(i*3);

            if (temp_array[offset] == "first_tab_enabled") { //start of a block of configuration variables that need special formatting
				html += special_formatting(temp_array,offset,helpline,false);
			    i += 61;
                continue;				
			} else if (temp_array[offset+1] == "boolean") {
				if (temp_array[offset+2] == "true") {
				   extra = "selected";
				   extra2 = "";
				} else {
				   extra = "";
				   extra2 = "selected";
				}
				inputfield = '<select id="'+temp_array[offset]+'">'
				+ '<option '+extra+' value="true">'+intext('Yes')+'</option> '				
				+ '<option '+extra2+' value="false">'+intext('No')+'</option> '
				+ '</select>';	
			} else if (temp_array[offset] == "truncate_name") {
				var trunc_opt = new Array();
				trunc_opt[0] = intext("Do not truncate names");
				trunc_opt[1] = intext("Abbreviate last name");
				trunc_opt[2] = intext("Keep first name only");
				var content = "";
				for (j = 0; j < 3; j++) {
				   if (temp_array[offset+2] == j) {
					  content += '<option selected value="'+j+'">'+trunc_opt[j]+'</option>';
				   } else {
					  content += '<option value="'+j+'">'+trunc_opt[j]+'</option>';
				   }
				}
				inputfield = '<select id="'+temp_array[offset]+'">' + content + '</select>';	
			} else if (temp_array[offset] == "new_user_avatar") {
				var nua_opt = new Array();
				nua_opt[0] = intext("No avatar");
				nua_opt[1] = intext("Set to default avatar");
				nua_opt[2] = intext("Set to an identicon");
				var content = "";
				for (j = 0; j < 3; j++) {
				   if (temp_array[offset+2] == j) {
					  content += '<option selected value="'+j+'">'+nua_opt[j]+'</option>';
				   } else {
					  content += '<option value="'+j+'">'+nua_opt[j]+'</option>';
				   }
				}
				inputfield = '<select id="'+temp_array[offset]+'">' + content + '</select>';	
			} else if (temp_array[offset] == "forum_topic_names") { 
			    inputfield = '<input style="overflow:hidden;width:800px" type="text" id="'+temp_array[offset]+'" maxlength="1024" class="theinputbox" value="'+temp_array[offset+2]+'">';
			} else {
			    inputfield = '<input style="overflow:hidden;width:350px" type="text" id="'+temp_array[offset]+'" maxlength="1024" class="theinputbox" value="'+temp_array[offset+2]+'">';
			}
			if (temp_array[offset] == "website_title") {html += '<tr><td colspan=2><br><b>'+intext('Basic Settings')+'</b></td></tr>';}
			if (temp_array[offset] == "show_main") {html += '<tr><td colspan=2><br><b>'+intext('Site Layout')+'</b></td></tr>';}
			if (temp_array[offset] == "connect_with_username") {html += '<tr><td colspan=2><br><b>'+intext('How users connect to your site')+'</b></td></tr>';}
			if (temp_array[offset] == "allow_rich_text") {html += '<tr> <td colspan=2><br><b>'+intext('Enabled Features')+'</b></td></tr>';}
			if (temp_array[offset] == "status_to_start_threads") {html += '<tr><td colspan=2><br><b><acronym title="'+intext('0 = new user, 1 = regular user, 2 = star member, 3 = moderator, 5 = administrator')+'">'+intext('Status Settings')+'</b></acronym></td></tr>';}
			if (temp_array[offset] == "avatars_allowed") {html += '<tr><td colspan=2><br><b>'+intext('Vanity Settings')+'</b></td></tr>';}					
			if (temp_array[offset] == "must_login_to_see_forum") {html += '<tr><td colspan=2><br><b>'+intext('Privacy Settings')+'</b></td></tr>';}			
			if (temp_array[offset] == "auto_close_thread") {html += '<tr><td colspan=2><br><b>'+intext('Cron Settings')+'</b></td></tr>';}				
			if (temp_array[offset] == "name_of_status_2") {html += '<tr><td colspan=2><br><b>'+intext('Miscellaneous Settings')+'</b></td></tr>';}		
			html += '<tr> <td style="text-align:left;width:15%;" class="coltwo"><acronym title="'+helpline.get(temp_array[offset])+'">' + temp_array[offset] + '</acronym></td> <td class="colthree">' + inputfield + '</td> </tr>';
			globals.temp_settings.set(temp_array[offset],temp_array[offset+2]);
		}
		var savebutton = '<br><input class="postbutton" type="button" onClick="javascript:save_site_settings()" value="'+intext('Save')+'"><br><br><br>';
	} else {
		for (i = 0; i < count; i++) {
			offset = 1+(i*3);
			if (temp_array[offset] == "first_tab_enabled") { //start of a block of configuration variables that need special formatting
				html += special_formatting(temp_array,offset,helpline,true);
			    i += 61;
                continue;				
			}
		    inputfield = temp_array[offset+2];
			inputfield = inputfield.replace(/>/ig,'&gt;');        
            inputfield = inputfield.replace(/</ig,'&lt;');      
		    if (temp_array[offset] == "website_title") {html += '<tr><td colspan=2><br><b>'+intext('Basic Settings')+'</b></td></tr>';}
		 	if (temp_array[offset] == "show_main") {html += '<tr><td colspan=2><br><b>'+intext('Site Layout')+'</b></td></tr>';}
			if (temp_array[offset] == "connect_with_username") {html += '<tr><td colspan=2><br><b>'+intext('How users connect to your site')+'</b></td></tr>';}
			if (temp_array[offset] == "allow_rich_text") {html += '<tr> <td colspan=2><br><b>'+intext('Enabled Features')+'</b></td></tr>';}
			if (temp_array[offset] == "status_to_start_threads") {html += '<tr><td colspan=2><br><b><acronym title="'+intext('0 = new user, 1 = regular user, 2 = star member, 3 = moderator, 5 = administrator')+'">'+intext('Status Settings')+'</b></acronym></td></tr>';}
			if (temp_array[offset] == "avatars_allowed") {html += '<tr><td colspan=2><br><b>'+intext('Vanity Settings')+'</b></td></tr>';}		
			if (temp_array[offset] == "must_login_to_see_forum") {html += '<tr><td colspan=2><br><b>'+intext('Privacy Settings')+'</b></td></tr>';}			
			if (temp_array[offset] == "auto_close_thread") {html += '<tr><td colspan=2><br><b>'+intext('Cron Settings')+'</b></td></tr>';}						
			if (temp_array[offset] == "name_of_status_2") {html += '<tr><td colspan=2><br><b>'+intext('Miscellaneous Settings')+'</b></td></tr>';}			
			html += '<tr> <td style="text-align:left;width:15%;" class="coltwo"><acronym title="'+helpline.get(temp_array[offset])+'">'+ temp_array[offset] + '</acronym></td> <td class="colthree">' + inputfield + '</td> </tr>';
		}
		var savebutton = '<br><br><br>';
	}
	
    $("content_area").innerHTML = '<br><br><div id="width_kludge2"><table cellspacing="0" id="logtable"> <tbody>' + intext("Place your cursor over the variable name for more information") + html + '</tbody> </table>' + savebutton + '</div>';	
}  

function special_formatting(temp_array,offset,helpline,readonly) {
    var extra;
	
	if (readonly) {var extra2 = "disabled";} else {var extra2 = "";}

	for (i = 0; i < 62; i++) {
	   globals.temp_settings.set(temp_array[offset+(i*3)],temp_array[offset+(i*3)+2]);	
	}

	var return_value = '<tr><td colspan=2><br><b>'+intext('Site Layout')+'</b></td></tr><tr><td colspan=2>';

	return_value += standard_row_layout(intext("First Tab"),offset,temp_array,helpline,23,readonly); 
	offset += 15;
	return_value += standard_row_layout(intext("Second Tab"),offset,temp_array,helpline,0,readonly); 
	offset += 15;

	//Articles
	if (temp_array[offset+2] == "true") {
	   extra = "checked=\"checked\"";
	} else {
	   extra = "";
	}
	return_value += '&nbsp;&nbsp;&nbsp;<input '+extra2+' type="checkbox" '+extra+' id="'+temp_array[offset]+'"><acronym title="'+helpline.get(temp_array[offset])+'">Articles</acronym>';
	offset += 3;
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'"><input '+extra2+' type="text" id="'+temp_array[offset]+'" maxlength="50" style="overflow:hidden;width:100px" class="theinputbox" value="'+temp_array[offset+2]+'"></acronym>';
	offset += 3;
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'"><input '+extra2+' type="text" id="'+temp_array[offset]+'" maxlength="100" style="overflow:hidden;width:100px" class="theinputbox" value="'+temp_array[offset+2]+'"></acronym>';
	offset += 3;
	if (temp_array[offset+2] == "true") {
	   extra = "checked=\"checked\"";
	} else {
	   extra = "";
	}
	return_value += '<span style="position:relative;left:183px;"><acronym title="'+helpline.get(temp_array[offset])+'">'+intext("indexable")+'</acronym>:<input '+extra2+' type="checkbox" '+extra+' id="'+temp_array[offset]+'"></span><br>';
	offset += 3;

	//Forums
	if (temp_array[offset+2] == "true") {
	   extra = "checked=\"checked\"";
	} else {
	   extra = "";
	}
	return_value += '&nbsp;&nbsp;&nbsp;<input '+extra2+' type="checkbox" '+extra+' id="'+temp_array[offset]+'"><acronym title="'+helpline.get(temp_array[offset])+'">'+intext("Forums")+'</acronym>';
	offset += 3;
	return_value += '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'">'+temp_array[offset]+'</acronym>&nbsp;<input '+extra2+' type="text" id="'+temp_array[offset]+'" maxlength="5" style="overflow:hidden;width:20px" class="theinputbox" value="'+temp_array[offset+2]+'">';
	offset += 3;
	return_value += ',&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'">'+temp_array[offset]+'</acronym>&nbsp;<input '+extra2+' type="text" id="'+temp_array[offset]+'" maxlength="5" style="overflow:hidden;width:20px" class="theinputbox" value="'+temp_array[offset+2]+'">';
	offset += 3;
	return_value += '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'">'+temp_array[offset]+'</acronym>&nbsp;&nbsp;&nbsp;<input '+extra2+' style="overflow:hidden;width: 450px;" type="text" id="'+temp_array[offset]+'" maxlength="1024" class="theinputbox" value="'+temp_array[offset+2]+'">';
	offset += 3;
	return_value += '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'">'+temp_array[offset]+'</acronym>&nbsp;<input '+extra2+' style="overflow:hidden;width: 700px;" type="text" id="'+temp_array[offset]+'" maxlength="1024" class="theinputbox" value="'+temp_array[offset+2]+'">';
	offset += 3;
	if (temp_array[offset+2] == "true") {
	   extra = "checked=\"checked\"";
	} else {
	   extra = "";
	}
	return_value += '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'">'+intext("indexable")+'</acronym>:<input '+extra2+' type="checkbox" '+extra+' id="'+temp_array[offset]+'">';
    offset += 3;

	//Private Threads
	if (temp_array[offset+2] == "true") {
	   extra = "checked=\"checked\"";
	} else {
	   extra = "";
	}
	return_value += '<br>&nbsp;&nbsp;&nbsp;<input '+extra2+' type="checkbox" '+extra+' id="'+temp_array[offset]+'"><acronym title="'+helpline.get(temp_array[offset])+'">'+intext('Private Threads')+'</acronym>';
	offset += 3;
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="left:-33px;position:relative;"> <acronym title="'+helpline.get(temp_array[offset])+'"><input '+extra2+' type="text" id="'+temp_array[offset]+'" maxlength="50" style="overflow:hidden;width:100px" class="theinputbox" value="'+temp_array[offset+2]+'"></acronym></span>';
    offset += 3;
	return_value += '<acronym title="'+helpline.get(temp_array[offset])+'"><input '+extra2+' type="text" id="'+temp_array[offset]+'" maxlength="100" style="overflow:hidden;width:100px" class="theinputbox" value="'+temp_array[offset+2]+'"></acronym><br>';
	offset += 3;
	
	return_value += standard_row_layout(intext("Second Last Tab"),offset,temp_array,helpline,0,readonly); 
	offset += 15;
	return_value += standard_row_layout(intext("Last Tab"),offset,temp_array,helpline,64,readonly); 
	offset += 15; 

	//Help menu
	if (temp_array[offset+2] == "true") {
	   extra = "checked=\"checked\"";
	} else {
	   extra = "";
	}
	return_value += '<br>&nbsp;&nbsp;&nbsp;<input '+extra2+' type="checkbox" '+extra+' id="'+temp_array[offset]+'"><acronym title="'+helpline.get(temp_array[offset])+'">'+intext('Corner Menu')+'</acronym>';
	offset += 3;
	return_value += '&nbsp;&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'"><input '+extra2+' type="text" id="'+temp_array[offset]+'" maxlength="50" style="overflow:hidden;width:100px" class="theinputbox" value="'+temp_array[offset+2]+'"></acronym><br>';
    offset += 3;
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + standard_row_layout('',offset,temp_array,helpline,-23,readonly); 
	offset += 15; 
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + standard_row_layout('',offset,temp_array,helpline,-23,readonly); 
	offset += 15; 
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + standard_row_layout('',offset,temp_array,helpline,-23,readonly); 
	offset += 15; 
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + standard_row_layout('',offset,temp_array,helpline,-23,readonly); 
	offset += 15; 	
	if (temp_array[offset+2] == "true") {
	   extra = "checked=\"checked\"";
	} else {
	   extra = "";
	}
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input '+extra2+' type="checkbox" '+extra+' id="'+temp_array[offset]+'"><acronym title="'+helpline.get(temp_array[offset])+'"></acronym>';
	offset += 3;
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="left:-28px;position:relative;"> <acronym title="'+helpline.get(temp_array[offset])+'"><input '+extra2+' type="text" id="'+temp_array[offset]+'" maxlength="50" style="overflow:hidden;width:100px" class="theinputbox" value="'+temp_array[offset+2]+'"></acronym></span><br>';
	offset += 3;
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + standard_row_layout('',offset,temp_array,helpline,-23,readonly); 
	offset += 15; 	

    return_value += '</td></tr><br>';
	return return_value;
}

function standard_row_layout(title,offset,temp_array,helpline,nudge,readonly) {
    var extra, extra2;
	
	if (readonly) {var extra3 = "disabled";} else {var extra3 = "";}
	
	if (temp_array[offset+2] == "true") {
	   extra = "checked=\"checked\"";
	} else {
	   extra = "";
	}
	var return_value = '&nbsp;&nbsp;&nbsp;<input '+extra3+' type="checkbox" '+extra+' id="'+temp_array[offset]+'"><acronym title="'+helpline.get(temp_array[offset])+'">'+title+'</acronym>';
	
	offset += 3;
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="left:'+nudge+'px;position:relative;"><acronym title="'+helpline.get(temp_array[offset])+'"><input '+extra3+' type="text" id="'+temp_array[offset]+'" maxlength="50" style="overflow:hidden;width:100px" class="theinputbox" value="'+temp_array[offset+2]+'"></acronym>';
	offset += 3;
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'"><input '+extra3+' style="overflow:hidden;width: 100px;" type="text" id="'+temp_array[offset]+'" maxlength="256" class="theinputbox" value="'+temp_array[offset+2]+'"></acronym>';

	offset += 3;
	if (temp_array[offset+2] == "true") {
	   extra = "checked=\"checked\"";
	   extra2 = "";
	} else {
	   extra = "";
	   extra2 = "checked=\"checked\"";
	}
	return_value += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'">div:<input '+extra3+' type="radio" name="'+temp_array[offset]+'" id="'+temp_array[offset]+'" '+extra+'>iframe:<input '+extra3+' type="radio" name="'+temp_array[offset]+'" id="'+temp_array[offset]+'2" '+extra2+'></acronym>';

	offset += 3;
	if (temp_array[offset+2] == "true") {
	   extra = "checked=\"checked\"";
	} else {
	   extra = "";
	}
	return_value += '&nbsp;&nbsp;&nbsp;<acronym title="'+helpline.get(temp_array[offset])+'">'+intext("indexable")+'</acronym>:<input '+extra3+' type="checkbox" '+extra+' id="'+temp_array[offset]+'"></span><br>';
	offset += 3;

	return return_value;
}

function save_site_settings() {
    var pars = "";
    globals.temp_settings.each(function(pair) {
	   if  ((pair.key == "first_tab_enabled") || (pair.key == "first_tab_indexable") || (pair.key == "second_tab_enabled") || (pair.key == "second_tab_indexable") 
		|| (pair.key == "articles_indexable") || (pair.key == "forums_indexable")  || (pair.key == "first_tab_is_div")  || (pair.key == "second_tab_is_div")  
		|| (pair.key == "last_tab_is_div") || (pair.key == "second_last_tab_is_div") || (pair.key == "second_last_tab_enabled") || (pair.key == "second_last_tab_indexable")  
		|| (pair.key == "last_tab_enabled") || (pair.key == "last_tab_indexable") || (pair.key == "enable_helpmenu") || (pair.key == "helpmenu1_enabled")  
		|| (pair.key == "helpmenu1_is_div")  || (pair.key == "helpmenu1_indexable") || (pair.key == "helpmenu2_enabled") || (pair.key == "helpmenu2_is_div")  
		|| (pair.key == "helpmenu2_indexable") || (pair.key == "helpmenu3_enabled") || (pair.key == "helpmenu3_is_div") || (pair.key == "helpmenu3_indexable")  
		|| (pair.key == "helpmenu4_enabled") || (pair.key == "helpmenu4_is_div") || (pair.key == "helpmenu4_indexable") || (pair.key == "helpmenu5_enabled")  
		|| (pair.key == "helpmenu6_enabled") || (pair.key == "helpmenu6_is_div") || (pair.key == "helpmenu6_indexable") || (pair.key == "enable_forums") 
		|| (pair.key == "enable_articles") || (pair.key == "enable_private_threads")) {
			if ($(pair.key).checked.toString() != pair.value) {pars += "&" + pair.key + "=" + encodeURIComponent($(pair.key).checked);}
	    } else {
			if ($(pair.key).value != pair.value) {pars += "&" + pair.key + "=" + encodeURIComponent($(pair.key).value);}
	    }
    }); 
	var myAjax = new Ajax.Request("save_site_settings.php", {method: 'post', parameters: pars, onComplete: save_site_settings_response});
}

function save_site_settings_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");
   
   if (error_code(temp_array)) {
      alert(temp_array[1]);
	  gen_mod_panel();
	  return;
   }	  

   alert(intext("Settings saved.  Reloading your page now."));
   window.location.reload();
}

function show_log(page) {
    set_display("top_area:none","midrow:inline","content_area:inline","inputdiv:none","topbar:inline","bottombar:none");
	
	$("showsitesettingsbutton").className = "modbutton";
	$("showlogbutton").className = "modbuttonselected";
	$("showfilesbutton").className = "modbutton";
	$("showlockdownbutton").className = "modbutton";
	
	$('topbar').innerHTML = '';
	$('content_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';

    var myAjax = new Ajax.Request("get_system_log.php", {method: 'post', parameters: 'page='+page, onComplete: get_log_response});
    var myAjax2 = new Ajax.Request("get_site_info.php", {method: 'post', parameters: '', onComplete: get_site_info_response});
}

function get_site_info_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");

	if (error_code(temp_array)) {
		alert(temp_array[1]);
		return;
	}   

	if (window.parent.account_info.status == 5) {
		var extra = ' <input class="rtbutton" type="button" onClick="javascript:check_for_updates()" value="'+intext('Check for updates')+'">';
	} else {
		var extra = '';
	}
	var info = '<br><p class="pageclass">OmegaBB '+intext('version')+': '+temp_array[1]+extra+'<br>PHP '+intext('version')+': '+temp_array[2]+'<br>MySQL '+intext('version')+': '+temp_array[3]+'</p>';

	$('topbar').innerHTML = info;
}  

function check_for_updates() {
   var myAjax = new Ajax.Request("check_for_updates.php", {method: 'post', parameters: '', onComplete: check_for_updates_response});
}

function check_for_updates_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");

   error_code(temp_array);
   alert(temp_array[1]);
}   

function get_log_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");

   var log = "";

   if (error_code(temp_array)) {
      alert(temp_array[1]);
	  return;
   }	  

   var current_page = parseInt(temp_array[1]);
   var total_pages = parseInt(temp_array[2]);
     
   var page_bar = intext("Page")+": ";

   for (i = 1; i < total_pages+1; i++) {
      if (i % 40 == 0) {
	     if (globals.pagenumbar_expanded) {
	        page_bar += "<br><br>";
		 } else {
		    page_bar += "<a onclick='globals.pagenumbar_expanded=1;show_log("+current_page+")'>("+intext("more")+")</a>";
			break;
		 }
	  }
      if (i-1 == current_page) {
         page_bar += " <a class='pageclassselected' onclick=\"show_log("+(i-1)+")\">" + i + "</a>";
	  } else {
         page_bar += " <a class='pageclass' onclick=\"show_log("+(i-1)+")\">" + i + "</a>";
      }
   }

   var count = parseInt(temp_array[3]);
   var log = '<tr> <td style="font-size:16px;text-align:left;width:15%;" class="coltwo"><b>'+intext('Time')+'</b></td> <td class="colsix" style="padding-left:20px;"><b>'+intext('Mod')+'</b></td> <td class="colthree"><b>'+intext('Event')+'</b></td></tr>';
       
   var j = new Array();
   for (var i = 0; i < count; i++) {
       var offset = 4 + (i * 3);
	   var ztemp_array = temp_array[offset+0].split(":");
	   if (ztemp_array[1] == "" ) {ztemp_array[1] = ztemp_array[0];}
	   var userfield = '<a href="profile.php?user_id='+ ztemp_array[0]  +'" id="profile_box'+i+'">' + ztemp_array[1] + '</a>'; 	  
	   j[i] = 0;	   
	   var more_to_replace = true;
	   var eventfield = temp_array[offset+1];
	   while (more_to_replace) {
	      eventfield = eventfield.replace(/%%u([0-9]+):(.*?);/,"<a id=\"mprofile_box"+i+"_"+j[i]+"\" href=profile.php?user_id=$1>$2</a>");
	      eventfield = eventfield.replace(/%%t([0-9]+):(.*?);/,"<a onclick='javascript:get_thread_page($1,0,0,0);'>$2</a>");
		  if (eventfield.match(/%%u([0-9]+):(.*?);/)) {j[i]++;} else {more_to_replace = false;}
	   }
	   log += '<tr> <td style="text-align:left;width:15%;" class="coltwo">' + temp_array[offset+2] + '</td> <td class="colsix">' + userfield + '</td> <td class="colthree">' + eventfield + '</td> </tr>';
   }

    $("content_area").innerHTML = '<h3>'+intext('Event Log')+'</h3>' +page_bar +'<br><br><div id="width_kludge2"><table cellspacing="0" id="logtable"> <tbody>' + log + '</tbody> </table></div><br>' + page_bar + '<br><br>';
	
	jQuery().ready(function() {
		for (i = 0; i < count; i++) {
			jQuery("#profile_box"+i).fancybox({
				'width'				: 800,
				'height'			: 400,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
			});		
			for (k = 0; k <= j[i]; k++) {
				if ($("mprofile_box"+i+"_"+k)) {
					jQuery("#mprofile_box"+i+"_"+k).fancybox({
						'width'				: 800,
						'height'			: 400,
						'autoScale'			: false,
						'transitionIn'		: 'none',
						'transitionOut'		: 'none',
						'type'				: 'iframe'
					});
				} 	
			}
		}
	});	     
}

function show_files(page) {
    set_display("top_area:none","midrow:inline","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
	
	$("showsitesettingsbutton").className = "modbutton";
	$("showlogbutton").className = "modbutton";
	$("showfilesbutton").className = "modbuttonselected";
	$("showlockdownbutton").className = "modbutton";
	
	$('content_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
	var pars = "page="+page;	
    var myAjax = new Ajax.Request("get_file_log.php", {method: 'post', parameters: pars, onComplete: get_file_log_response});  
}

function get_file_log_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");

   if (error_code(temp_array)) {
      alert(temp_array[1]);
	  return;
   }	  

   var current_page = parseInt(temp_array[1]);
   var total_pages = parseInt(temp_array[2]);
     
   var page_bar = "<br>"+intext("Page")+": ";

   for (i = 1; i < total_pages+1; i++) {
      if (i % 40 == 0) {
	     if (globals.pagenumbar_expanded) {
	        page_bar += "<br><br>";
		 } else {
		    page_bar += "<a onclick='globals.pagenumbar_expanded=1;show_files("+current_page+")'>("+intext("more")+")</a>";
			break;
		 }
	  }
      if (i-1 == current_page) {
         page_bar += " <a class='pageclassselected' onclick=\"show_files("+(i-1)+")\">" + i + "</a>";
	  } else {
         page_bar += " <a class='pageclass' onclick=\"show_files("+(i-1)+")\">" + i + "</a>";
      }
   }

	var count = parseInt(temp_array[3]);

	var log = '<tr> <td style="font-size:16px;" style="text-align:left;width:20%;min-width:150px;" class="coltwo"><b>'+intext('Time')+'</b></td> <td class="colsix" style="width:25%;"><b>'+intext('User')+'</b></td> <td style="width:33%;" class="colthree"><b>'+intext('File')+'</b></td>  <td style="font-size:16px;width:33%;" class="colthree"><b>'+intext('Location')+'</b></td> <td style="font-size:16px;" class="colfour"><b>'+intext('Status')+'</b></td> <td style="font-size:16px;" class="colfour"><b>'+intext('Action')+'</b></td> </tr>';
    var offset, temp_array2, userfield, thread_id, thread_title, locationfield, extra;
	
    for (i = 0; i < count; i++) {
		offset = 4 + (i * 8);
		temp_array2 = temp_array[offset+2].split(":");
		userfield = '<a href="profile.php?user_id='+ temp_array2[0]  +'" id="profile_box'+i+'">' + temp_array2[1] + '</a>'; 
		if (temp_array[offset+5].match(/^%%t/)) {
			thread_id = temp_array[offset+5].replace(/%%t([0-9]+):.*/,'$1');   
			thread_title = temp_array[offset+5].replace(/%%t[0-9]+:(.*);/,'$1');   
			locationfield = '<a onclick="javascript:get_thread_page('+thread_id+',0,0,0)">'+thread_title+'</a>';
		} else {
			locationfield = temp_array[offset+5];
		}
		if (temp_array[offset+6] == "none") {
			log += '<tr> <td style="text-align:left;width:20%;min-width:150px;" class="coltwo">' + temp_array[offset+1] + '</td> <td style="width:25%;" class="colsix">' + userfield + '</td> <td style="width:33%;" class="colthree">'+ temp_array[offset+3] + '</a></td> <td style="width:33%;" class="colthree">' + locationfield + '</td> <td class="colfour">'+temp_array[offset+7]+'</td> <td class="colfour"></td> </tr>';
		} else {
			if (temp_array[offset+4].match(/^image/)) { extra = 'target=\'_blank\'';} else {extra = '';}
			log += '<tr> <td style="text-align:left;width:20%;min-width:150px;" class="coltwo">' + temp_array[offset+1] + '</td> <td style="width:25%;" class="colsix">' + userfield + '</td> <td style="width:33%;" class="colthree"><a '+extra+' href=\"' +  temp_array[offset+6] + "\">" + temp_array[offset+3] + '</a></td> <td style="width:33%;" class="colthree">' + locationfield + '</td> <td class="colfour">'+temp_array[offset+7]+'</td> <td class="colfour"><a onclick="deletefile('+temp_array[offset+0]+')">'+intext('Delete')+'</a></td></tr>';
		}
    }

    $("content_area").innerHTML = page_bar +'<br><br><div id="width_kludge2"><table cellspacing="0"  id="logtable"> <tbody>' + log + '</tbody> </table></div>' + page_bar;

	jQuery().ready(function() {
		for (i = 0; i < count; i++) {
			jQuery("#profile_box"+i).fancybox({
				'width'				: 800,
				'height'			: 400,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
			});		
		}
	});	     
}

function show_lockdown_button() {
    set_display("top_area:none","midrow:inline","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
	
	$("showsitesettingsbutton").className = "modbutton";
	$("showlogbutton").className = "modbutton";
	$("showfilesbutton").className = "modbutton";
	$("showlockdownbutton").className = "modbuttonselected";
	
	$('content_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
    var myAjax = new Ajax.Request("get_lockdown_button.php", {method: 'post', parameters: '', onComplete: get_lockdown_button_response});
}

function get_lockdown_button_response(originalRequest) {
	var temp_string = originalRequest.responseText;
	var temp_array = temp_string.split("^?");

	if (error_code(temp_array)) {
		alert(temp_array[1]);
		return;
	}	  

	if (!parseInt(temp_array[1])) {
		var fragment = "";
		var fragment2 = "";
		var fragment3 = "";
		var fragment4 = '<input type="text" id="system_message" maxlength="255" style="display:none;" class="theinputbox" value="">';	  
		var fragment5 = "";
		var fragment6 = "";

		if (settings.new_user_post_captcha == false && (settings.enable_forums || settings.enable_articles || settings.enable_private_threads)) {fragment += '<br><input type="checkbox" id="lockdown0" />'+intext('Captchas for new users when posting');} else {fragment += '<input style="display:none;" type="checkbox" id="lockdown0" />';}
		if (settings.new_accounts_allowed) {fragment += '<br><input type="checkbox" id="lockdown1" />'+intext('Disable new account creation');} else {fragment += '<input style="display:none;" type="checkbox" id="lockdown1" />';}
		if (settings.must_login_to_see_forum == false && (settings.enable_forums || settings.enable_articles)) {fragment += '<br><input type="checkbox" id="lockdown2" />'+intext('Must login to see forum and articles');} else {fragment += '<input style="display:none;" type="checkbox" id="lockdown2" />';}

		if (account_info.status == 5) {
		   if (settings.enable_forums || settings.enable_articles) {fragment2 += '<br><input type="checkbox" id="lockdown3" />'+intext('Take forum and articles offline');} else {fragment2 += '<input style="display:none;" type="checkbox" id="lockdown3" />';}
		   if (settings.enable_private_threads) {fragment2 += '<br><input type="checkbox" id="lockdown4" />'+intext('Take private threads offline');} else {fragment2 += '<input style="display:none;" type="checkbox" id="lockdown4" />';}
		   fragment2 += '<br><input onclick="check_rest()" type="checkbox" id="lockdown5" />'+intext('Take site offline');	 
		   
		   fragment3 = '<br><input type="radio" name="group1" id="indefinite"> '+intext('Indefinitely');		  
		   fragment4 = intext('System message')+": "+'<input type="text" id="system_message" maxlength="255" style="width: 600px" class="theinputbox" value=""><br><br>';	  
		} 

		if (settings.new_user_post_captcha == false || settings.new_accounts_allowed || settings.must_login_to_see_forum == false || account_info.status == 5) {
		   fragment5 = '<input class="postbutton" type="button" onClick="javascript:set_lockdown(1)" value="'+intext('Turn on')+'">';
		   fragment6 = '<br><br>For<br><input type="radio" name="group1" id="timed" checked> <input type="text" id="lockdown_time" maxlength="2" style="width: 20px" class="theinputbox" value=""> '+intext('hours (1 to 24)');
		}

		var html = "<br>"+intext("Lockdown button is OFF")+"<br>"  
			+ fragment
			+ fragment2
			+ fragment6
			+ fragment3
			+ '<br><br>'
			+ fragment4
			+ fragment5;
	} else { 
		var fragment = "<ul>";
		var statusarray=["<li>"+intext("Captchas for new users when posting")+"</li>", 
		"<li>"+intext("New account creation is disabled")+"</li>", 
		"<li>"+intext("Must login to see forum and articles")+"</li>",
		"<li>"+intext("Forum and articles are offline")+"</li>",
		"<li>"+intext("Private threads are offline")+"</li>", 
		"<li>"+intext("Site is offline")+"</li>"]

		for (i = 0; i < 6; i++) {
		   if (parseInt(temp_array[1] & Math.pow(2,i))) {
		      fragment += statusarray[i];
		   }
		}		
		fragment += "</ul>"+intext("Until")+": " + temp_array[2];
		if (temp_array[3]) {
			fragment += "<br>"+intext("Message")+": " + temp_array[3];
		}
		var html = "<br>"+intext("Lockdown button is ON")+'<br>'+fragment+'<br><br><input class="postbutton" type="button" onClick="javascript:set_lockdown(0)" value="'+intext('Turn off')+'">';
	}

	$("content_area").innerHTML = html;
}

function check_rest() {
   if ($('lockdown5').checked) {
       for (var i = 0; i < 5; i++) {
	   	   $('lockdown'+i).checked = false;
	       $('lockdown'+i).disabled = true;
	   }
   } else {
       for (var i = 0; i < 5; i++) {
           $('lockdown'+i).disabled = false;
	   }
   }
}

function set_lockdown(on_off) {
    if (on_off) {
		var code = 0;
		
		if (account_info.status == 5) {var limit = 6;} else {var limit = 3;}
		for (var i = 0; i < limit; i++) {
		   if ($('lockdown'+i).checked) {code += Math.pow(2,i);}
		}
		if ($('timed').checked) {expire_time = $('lockdown_time').value;} else {expire_time = "i";}
		var pars = "code="+code+"&expire_time="+expire_time;
		if ($('system_message').value) {
		   pars += "&msg="+encodeURIComponent($('system_message').value);
		}
	} else {
	   var pars = "turn_off=1";
	}
	var myAjax = new Ajax.Request("set_lockdown_button.php", {method: 'post', parameters: pars, onComplete: set_lockdown_response});  
}

function set_lockdown_response(originalRequest) {
	var temp_string = originalRequest.responseText;
	var temp_array = temp_string.split("^?");

	if (error_code(temp_array)) {
	   alert(temp_array[1]);
	}
	if ((temp_array[1]) == "1") {
	   alert(intext("Lockdown button set, reloading your page now."));
	   window.location.reload();
	} else {
	   show_lockdown_button();
	}
}

function gen_user_list()
{
    set_display("top_area:inline","midrow:none","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
	set_class();
    globals.current_forum_tab = "None";
    
	$('top_area').innerHTML = '<br><h3>'+intext('User List')+'</h3><br>' +
	'<form action="">'
		+ '<select id=userfilter name="themes" onchange="javascript:show_user_list(0,$(\'userfilter\').value)">'
		+ '<option value="all">'+intext('All users')+'</option> '
		+ '<option value="online">'+intext('Users online')+'</option> '		
		+ '<option value="new_users">'+intext('New Users')+'</option> '		
		+ '<option value="regular_users">'+intext('Regular Users')+'</option> '				
		+ '<option value="editors">'+settings.name_of_status_2+'s</option> '			
		+ '<option value="moderators">'+intext('Moderators')+'</option> '		
		+ '<option value="banned">'+intext('Banned')+'</option> '				
		+ '</select> </form>'
		
	show_user_list(0,"all")
}

function show_user_list(page,filter) {
    hide_footer();
	$('content_area').innerHTML = '<div style="width:100%;max-width:1250px;padding-top:10%;font-size:22px;font-weight:bold;color:#bbb;text-align:center;"><img border="0" src="img/indicator.gif"> </div>';
	var pars = "page=" + page + "&filter=" + filter;	
    var myAjax = new Ajax.Request("get_user_list.php", {method: 'post', parameters: pars, onComplete: get_user_list_response});
}

function get_user_list_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");
    
   var user_list = "";

   if (error_code(temp_array)) {
      alert(temp_array[1]);
	  return;
   }	  

   var current_page = parseInt(temp_array[1]);
   var total_pages = parseInt(temp_array[2]);
     
   var page_bar = "<br>"+intext("Page")+": ";
   
   for (i = 1; i < total_pages+1; i++) {
      if (i % 20 == 0) {
	     page_bar += "<br><br>";
	  }
      if (i-1 == current_page) {
         page_bar += " <a class='pageclassselected' onclick=\"show_user_list("+(i-1)+",$(\'userfilter\').value)\">" + i + "</a>";
	  } else {
         page_bar += " <a class='pageclass' onclick=\"show_user_list("+(i-1)+",$(\'userfilter\').value)\">" + i + "</a>";
      }
   }
   
   var count = parseInt(temp_array[3]);
   
   for (var i = 0; i < count; i++) {
      offset = 4 + (i * 2);
	  if (temp_array[offset+0] == -1) {
		  user_list += '<tr> <td class="coluserlistone">' + temp_array[offset+0] + 
		   '</td> <td class="coluserlisttwo">' + '<a href="profile.php?ip='+ temp_array[offset+1]  +'" id="profile_box'+i+'">' + temp_array[offset+1] + '</a></td></tr>';
	  } else if (temp_array[offset+0] == -2) {
		  user_list += '<tr> <td class="coluserlistone">' + temp_array[offset+0] + 
		   '</td> <td class="coluserlisttwo">' + '<a href="profile.php?fb='+ temp_array[offset+1]  +'" id="profile_box'+i+'">' + temp_array[offset+1] + '</a></td></tr>';		   
	  } else {
		  user_list += '<tr> <td class="coluserlistone">' + temp_array[offset+0] + 
		   '</td> <td class="coluserlisttwo">' + '<a href="profile.php?user_id='+ temp_array[offset+0]  +'" id="profile_box'+i+'">' + temp_array[offset+1] + '</a></td></tr>';
	  }
   }
   
	globals.content_height = "";
	$('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 

    $("content_area").innerHTML = page_bar +'<br><br><table cellspacing="0" id="bigtable" style="width:600px;"> <tbody>' + user_list + '</tbody> </table>' + page_bar;

	jQuery().ready(function() {
		for (i = 0; i < count; i++) {
			jQuery("#profile_box"+i).fancybox({
				'width'				: 800,
				'height'			: 400,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
			});		
		}
	});	      
}

function gen_profile()
{
    var part1, part2, part3, part4, part5, extra, extra2;

    set_display("top_area:none","midrow:none","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
	set_class();
    globals.current_forum_tab = "None";
	globals.content_height = "";
	$('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 
	
	part1 = '<br><h3>'+intext('Profile')+'</h3>'
	+ '<span class="rtbutton"><a id="profile_box" href="profile.php?user_id=' + account_info.user_id + '">'+intext('check profile')+'</a></span><br>';
	
	part5 = '<h3>'+intext('Edit profile text')+':</h3>'
	+ '    <textarea class="theinputbox" id="profiletextbox" cols=90 rows=5 ></textarea><br> '
	+ '    <input class="rtbutton" type="button" onClick="javascript:change_profile_text()" value="'+intext('Change')+'">';

	part2 = '<h3>'+intext('Change user name')+':</h3>'
	+ '    <input type="text" size="17" name="newuser" MAXLENGTH='+settings.max_username_length+' id="newname" value=""><br> '
	+ '    <input class="rtbutton" type="button" onClick="javascript:change_name()" value="'+intext('Change')+'">';

	if (settings.avatars_same_size) {extra = 'width="'+settings.max_avatar_dimensions[0]+'" height="'+settings.max_avatar_dimensions[1]+'"';} else { extra = '';}
	
	extra2 = '';
	if ((account_info.avatar_number == 0) && (settings.new_user_avatar == 1)) {
	   extra2 = settings.default_avatar;
	} else if (account_info.avatar_number > 0) {
	   extra2 = '"file.php?uid='+account_info.user_id+'&avatar_number='+account_info.avatar_number+'"';
	}
	
	part3 = '<br><br><h3>'+intext('Change avatar')+':</h3>'
	+ '<table>'
	+ '<tr>'
	+ '<td><span id="current_avatar"><img '+extra+' src='+extra2+'></span></td>'	
	+ '    <td>'
	+ '        <br><span id="iframe">'
	+ '            <iframe src="upload_avatar.php" frameborder="" height="50px" scrolling="0"></iframe>'
	+ '        </span>'
	+ '       <div id="list"></div>'
	+ '       <div id="new_avatar_number"></div>'
	+ '    </td>'
	+ '</tr>'
	+ '</table>';

	part4 = '<br><br>'+intext('Use previous avatars')+':&nbsp<input class="rtbutton" type="button" onClick="javascript:show_avatars(0)" value="'+intext('Show')+'">'
	+ '<br><div id="avatar_list"></div><br>';
		
	if (!settings.allow_username_change) {part2 = "";}
	if ((settings.status_to_have_avatar > account_info.status) || (settings.avatars_allowed == false) || !settings.allow_avatar_change){ part3 = ""; part4 = "";}
	if (!settings.user_info_permanentness || (account_info.avatar_number < 1)) {part4 = "";}
	if (settings.profile_text_limit == 0) {part5 = "";}
	$('content_area').innerHTML = part1 + part5 + part2 + part3 + part4;
	
    var pars = "user_id=" + account_info.user_id ;

    var myAjax = new Ajax.Request("get_profile_info.php", {method: 'post', parameters: pars, onComplete: view_profile_response});
		
	jQuery().ready(function() {
		jQuery("#profile_box").fancybox({
			'width'				: 800,
			'height'			: 400,
			'autoScale'			: false,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'type'				: 'iframe'
		});		
	});		
}

function view_profile_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");

   if (error_code(temp_array)) {alert(temp_array[1]); return;}   

   var profile_text = temp_array[5];
   profile_text = profile_text.replace(/<br>/gi, '\n');
   profile_text = profile_text.replace(/&nbsp;/gi, ' ');
   profile_text = profile_text.replace(/<a.*?>/gi, '');
   profile_text = profile_text.replace(/<\/a>/gi, '');
   profile_text = profile_text.replace(/&gt;/gi, '>');
   profile_text = profile_text.replace(/&lt;/gi, '<');	  
   profile_text = convert_to_input(profile_text);

   if ($("profiletextbox")) {$("profiletextbox").value = profile_text;}
   if ($("newname")) {$("newname").value = temp_array[1];}
}

function change_profile_text() {
    var pars = "user_id=" + account_info.user_id + "&text=" + encodeURIComponent($("profiletextbox").value);
    var myAjax = new Ajax.Request("set_profile_text.php", {method: 'post', parameters: pars, onComplete: set_profile_text_response});
}

function set_profile_text_response(originalRequest) {
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
           
    alert(temp_array[1]);
}

function gen_settings()
{
    set_display("top_area:none","midrow:none","content_area:inline","inputdiv:none","topbar:none","bottombar:none");
    set_class();
	globals.current_forum_tab = "None";
	
	globals.content_height = "";
	$('Content').setAttribute("style","height:"+globals.content_height+";left:"+globals.content_left+";top:"+globals.content_top+";"); 

	if (!settings.user_block_list || (settings.status_to_have_block_list > account_info.status)) {
	   var html = '<textarea class="theinputbox" style="display:none;" cols=70 rows=2 style="height: 36px" id=\'blocked_user_list\'></textarea>'; 
	} else {
	   var html = '<br><br><h3>'+intext('Block List')+':</h3>'
        + intext('Users blocked from posting in threads you create')+':'		
	    + '<br><textarea class="theinputbox" cols=70 rows=2 style="height: 36px" id=\'blocked_user_list\'></textarea>'
		+ '<br><input type="checkbox" id="blocknewusers" />'+intext('Block new users')
		
		+ '<br><br>' + intext('Users blocked from inviting you to private threads and sending you private messages')+':'		
	    + '<br><textarea class="theinputbox" cols=70 rows=2 style="height: 36px" id=\'ptblocked_user_list\'></textarea>'
		+ '<br><input type="checkbox" id="ptblocknewusers" />'+intext('Block new users')
		+ '<br><input type="checkbox" id="ptblockallusers" />'+intext('Block all users')
		
	    + '<br><br><input class="rtbutton" type="button" onClick="javascript:save_block_list()" value="'+intext('Save')+'">';
	}

	$('content_area').innerHTML = '<br><h3>'+intext('Settings')+'</h3><br><h3>'+intext('Change Theme')+':</h3><form action="">'
		+ '<select id=themesetter name="themes" onchange="javascript:set_theme($(\'themesetter\').value)">'
		+ '<option>'+intext('Click to choose')+'</option>'		
        + '<option value="css/facebook.css">'+intext('Facebook')+'</option> '				
		+ '<option value="css/darkblue.css">'+intext('Dark Blue')+'</option> '
		+ '<option value="css/darkred.css" >'+intext('Dark Red')+'</option>'		
		+ '<option value="css/green.css">'+intext('Green')+'</option> '		
		+ '<option value="css/black-and-white.css">'+intext('Black and White')+'</option>'		
		+ '<option value="css/new-darkblue.css">'+intext('New Dark Blue')+'</option> '			
		+ '<option value="css/new-darkred.css">'+intext('New Dark Red')+'</option> '			
		+ '<option value="css/new-green.css">'+intext('New Green')+'</option> '			
		+ '<option value="css/black-and-green.css">'+intext('Black and Green')+'</option> '
		+ '</select> </form><input class="rtbutton" type="button" onClick="javascript:save_theme()" name="savetheme" value="'+intext('Save')+'">'
		
		+ '<br><br><h3>'+intext('Change password')+':</h3>'
		+ intext('Old password')+':<br>'
		+ '    <input type="password" size="17" name="oldpass" MAXLENGTH=20 id="oldpass" value=""><br> '
		+ intext('New password')+':<br>'
		+ '    <input type="password" size="17" name="newpass0" MAXLENGTH=20 id="newpass0" value=""><br> '			
		+ intext('New password again')+':<br>'
		+ '    <input type="password" size="17" name="newpass1" MAXLENGTH=20 id="newpass1" value=""><br> '
		+ '    <input class="rtbutton" type="button" onClick="javascript:change_password()" value="'+intext('Change')+'">'
        
        + html
		
		+ '<br><br><input type="checkbox" id="hideonlinestatus" />' + intext('Hide my online status')	
		+ '<br><input type="checkbox" name="autowatchname" id="autowatch" />' + intext('Automatically watch all new threads')
		+ '<br><input class="rtbutton" type="button" onClick="javascript:save_settings()" value="'+intext('Save')+'">';
		

    var pars = "user_id=" + account_info.user_id ;
    var myAjax = new Ajax.Request("get_settings_info.php", {method: 'post', parameters: pars, onComplete: get_settings_info_response});
		
	jQuery().ready(function() {
		function log(event, data, formatted) {
			$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
		}
		function formatItem(row) {
			return row[0] + " (<strong>id: " + row[1] + "</strong>)";
		}
		function formatResult(row) {
			return row[0].replace(/(<.+?>)/gi, '');
		}

		jQuery("#blocked_user_list").autocomplete('search_users.php', {
			width: 300,
			multiple: true,
			matchContains: true,
			formatItem: formatItem,
			formatResult: formatResult
		});
		jQuery("#ptblocked_user_list").autocomplete('search_users.php', {
			width: 300,
			multiple: true,
			matchContains: true,
			formatItem: formatItem,
			formatResult: formatResult
		});
	});
}

function get_settings_info_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");
  
   if (error_code(temp_array)) {alert(temp_array[1]); return;}   
   
   var block_list_text = temp_array[1];
   var ptblock_list_text = temp_array[2];

   if (block_list_text.match(/,newusers/)) {
      block_list_text = block_list_text.replace(/,newusers/, '');
	  if (!$("blocknewusers").checked) { $("blocknewusers").click(); }
   }
	  
   block_list_text = block_list_text.replace(/^,/, '');
   block_list_text = block_list_text.replace(/,/gi, ', ');  
   
   if (block_list_text != "") {$("blocked_user_list").value = block_list_text + ", ";}
   
   if (ptblock_list_text.match(/,newusers/)) {
      ptblock_list_text = ptblock_list_text.replace(/,newusers/, '');
	  if (!$("ptblocknewusers").checked) { $("ptblocknewusers").click(); }
   }
   if (ptblock_list_text.match(/,allusers/)) {
      ptblock_list_text = ptblock_list_text.replace(/,allusers/, '');
	  if (!$("ptblockallusers").checked) { $("ptblockallusers").click(); }
   }
	  
   ptblock_list_text = ptblock_list_text.replace(/^,/, '');
   ptblock_list_text = ptblock_list_text.replace(/,/gi, ', ');  
   
   if (ptblock_list_text != "") {$("ptblocked_user_list").value = ptblock_list_text + ", ";}   
      
   var hideonlinestatus = (parseInt(temp_array[3]) & 1);
   if (hideonlinestatus) {
      if (!$("hideonlinestatus").checked) { $("hideonlinestatus").click(); }
   }
   
   var autowatch = (parseInt(temp_array[3]) & 2);
   if (autowatch) {
      if (!$("autowatch").checked) { $("autowatch").click(); }
   }
}

function save_settings() {
   var pars = 'user_id=' + account_info.user_id + "&hideonlinestatus=" + $("hideonlinestatus").checked + "&autowatch=" + $("autowatch").checked;
   var myAjax = new Ajax.Request('save_settings.php', {method: 'get', parameters: pars, onComplete: save_settings_response});
}

function save_settings_response(originalRequest) {
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
           
    alert(temp_array[1]);
}

function save_block_list(){
   var pars = 'user_id=' + account_info.user_id + "&blocknewusers=" + $("blocknewusers").checked + "&blocked_user_list=" + encodeURIComponent($("blocked_user_list").value) + "&ptblockallusers=" + $("ptblockallusers").checked + "&ptblocknewusers=" + $("ptblocknewusers").checked + "&ptblocked_user_list=" + encodeURIComponent($("ptblocked_user_list").value);
   var myAjax = new Ajax.Request('saveblocklist.php', {method: 'get', parameters: pars, onComplete: saveblocklist_response});
}

function saveblocklist_response(originalRequest) {
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
           
    alert(temp_array[1]);

	var pars = "user_id=" + account_info.user_id ;
    var myAjax = new Ajax.Request("get_settings_info.php", {method: 'post', parameters: pars, onComplete: get_settings_info_response});
}

function show_avatars(offset){
   var display = "";
   
   if (settings.avatars_same_size) { extra = 'width="'+settings.max_avatar_dimensions[0]+'" height="'+settings.max_avatar_dimensions[1]+'"';} else { extra = '';}
			   
   var display = "<table> <tr>";   
   for (var i = account_info.total_avatars; i >= 1; i--) {
	  display += '<td> <a onclick="javascript:select_avatar('+i+')"> <img '+extra+' src="file.php?uid=' +account_info.user_id+ '&avatar_number=' +i+ '"></a></td>';
      if (i % 11 == 0) {
         display += "</tr><tr>"; 
      }
   }

   display += "</tr> </table>";
   $('avatar_list').innerHTML = display;
}

function select_avatar(avatar_number){   
    var url = 'update_avatar.php';
    var pars = "avatar_number=" + avatar_number;

    account_info.avatar_number = avatar_number;

    var myAjax = new Ajax.Request(url, {method: 'post', parameters: pars, onComplete: update_avatar_response});
}

function update_avatar_response(originalRequest) {
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
    
	if (!error_code(temp_array) && !settings.persistent_logo){
       set_corner_avatar(account_info.user_id,account_info.avatar_number);
    }
	
	if (settings.avatars_same_size) { extra = 'width="'+settings.max_avatar_dimensions[0]+'" height="'+settings.max_avatar_dimensions[1]+'"';} else { extra = '';}
	
	$('current_avatar').innerHTML = '<img '+extra+' src="file.php?uid=' +account_info.user_id+ '&avatar_number=' +account_info.avatar_number+ '">';
	
    alert(temp_array[1]);
}

function change_password() {
    var url = 'changepassword.php';
    var pars = "oldpass=" + encodeURIComponent($('oldpass').value);
    pars += "&newpass0=" + encodeURIComponent($('newpass0').value);
    pars += "&newpass1=" + encodeURIComponent($('newpass1').value);
		
    var myAjax = new Ajax.Request(url, {method: 'post', parameters: pars, onComplete: changepassword_response});
}

function changepassword_response(originalRequest) {
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
    
    alert(temp_array[1]);

	$('oldpass').value = "";
    $('newpass0').value = "";
    $('newpass1').value = "";
}

function change_name() {
    var url = 'changename.php';
    var pars = "newusername=" + encodeURIComponent($('newname').value);

    var myAjax = new Ajax.Request(url, {method: 'post', parameters: pars, onComplete: changename_response});
}

function changename_response(originalRequest) {
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
    
    alert(temp_array[1]);
	
	if (!error_code(temp_array)) {
	   account_info.username = temp_array[3];
	}
}

function save_theme() {
    if (($('themesetter').value == "") || ($('themesetter').value == intext("Click to choose"))) { return; }

    var pars = 'user_id=' + account_info.user_id + '&theme=' + $('themesetter').value;
    var myAjax = new Ajax.Request( 'savetheme.php', {method: 'get', parameters: pars, onComplete: save_themeResponse});
}

function save_themeResponse(originalRequest)	{
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;
    var temp_array = temp_string.split("^?");
    
    alert(temp_array[1]);
	
	account_info.theme = $('themesetter').value
}

function set_theme(file_name)
{
   if ((file_name == "") || (file_name == intext("Click to choose"))) { return; }  
   document.getElementById('main_css').href = file_name; 
}

function logout()
{   
	var url = 'logout.php';
    var myAjax = new Ajax.Request(url, {method: 'get', parameters: "", onComplete: kick_screen});
}

function kick_screen(originalRequest)
{
    var temp_string = originalRequest.responseText;
    $('result').value = temp_string;

	if (settings.site_down) {
	   window.location.reload();
	} else {
       globals.pe.stop();
       window.location.href = '.';	
	}
}

function updater()
{
    if(globals.updater_mutex) {
	   return;
    }
    globals.updater_mutex = 1;

    var url = 'update.php';
    var pars = '&hitf=' + globals.highest_thread_in_forums + '&thread_watching=' + globals.thread_watching + '&last_update=' + globals.highest_post_id + "&hipt=" +globals.highest_post_in_thread ;
    if (globals.newly_read_threads != "") {
       pars += "&nrt=" + globals.newly_read_threads;
    }
	if ((globals.current_forum == 13)  && (globals.current_page_of_thread == 0)) {
       pars += "&current_forum=" + globals.current_forum + "&last_wiki_revision=" + globals.last_wiki_revision;
    }
    var myAjax = new Ajax.Request(url, {method: 'get', parameters: pars, onComplete: updaterResponse});
}

function updaterResponse(originalRequest)	{
    globals.updater_mutex = 0;
    var temp_string = originalRequest.responseText;

    $('result').value = temp_string;

    var updates = temp_string.split("^*");

    var return_code = parseInt(updates[0]);
	
    if (return_code > 0) {
		globals.newly_read_threads = "";
		globals.is_connected = true;
		update_forum(updates[2]);   
		update_message_center(updates[1]);    
	} else if (return_code == 0) {
		globals.pe.stop();
		globals.is_connected = false;
		globals.highest_post_id = 0;
		for (var key in account_info) {account_info[key] = "";}
		mythreads_hash.each(function(pair) {mythreads_hash.unset(pair.key); mc_num_posts_hash.unset(pair.key); mc_title_hash.unset(pair.key);});
		show_login_panel(intext("Error: you are no longer signed in"));
	} else if (temp_string.substr(0,4) == "-1^?") {
		globals.is_connected = false;
		$('message_center').innerHTML = temp_string.substr(4);
    } else {
		globals.is_connected = false;
		update_message_center(updates[1]);       
	}

    append_to_thread(updates[3]);
    process_signals(updates[4]);
}

function process_signals(in_string) {
   if (in_string == 0) {return; }
   get_thread_page(globals.thread_watching,globals.current_page_of_thread,0,0);
}

function update_message_center(in_string) {
    var display_string = "";
    var new_string = "";

	if (in_string != undefined ) {
	
       var temp_array = in_string.split("^?");
       if (parseInt(temp_array[0]) > globals.highest_post_id) {globals.highest_post_id = parseInt(temp_array[0]);}
       var total_threads = parseInt(temp_array[1]);
       
	   for (i = 0; i < total_threads; i++) {       
          var thread_id = parseInt(temp_array[2+(i*4)]);
          mc_title_hash.set(thread_id, temp_array[3+(i*4)]);
          mc_num_posts_hash.set(thread_id, parseInt(temp_array[4+(i*4)]));
       }
	}
	
	var total_unread_threads = 0;
	
    mc_title_hash.each(function(pair) {
	   if (mythreads_hash.get(pair.key) == undefined) { 
	      //When you're added to a PT, or are auto-watching threads, it won't exist in mythreads_hash, so this adds it there
	      mythreads_hash.set(pair.key,0);

		  var found = false;
		  for (var i = 0; i < 12; i++) {
		  	  var x = 0;
			  while ((cache.thread_id[i][x] != 0) && (cache.thread_id[i][x] != undefined)) {
				 if (cache.thread_id[i][x] == pair.key) {found = true;}
				 x++;
			  }
		  }
		  
		  //if the thread can't be found in the forums, then it's a PT you were just invited to, add thread title to the PT forum
		  if (found == false) {
		     update_forum("0^?1^?12^?"+pair.key+"^?"+mc_title_hash.get(pair.key)+"^?0");
		  }
	   }  
	   
       var unread = mc_num_posts_hash.get(pair.key) - mythreads_hash.get(pair.key);   
       if (unread > 0) {
	      if (mythreads_hash.get(pair.key) == 0) {extra = "0";} else {extra = "'last'";}
	      if (globals.narrow_width) {
             display_string += "<a onclick=\"javascript:get_thread_page("+pair.key+","+extra+",0,0)\">&#9679 " + pair.value + "(" + unread + ")</a>&nbsp;&nbsp;&nbsp;" ;		  
		  } else {
             display_string += "<a onclick=\"javascript:get_thread_page("+pair.key+","+extra+",0,0)\">&#9679 " + pair.value + "(" + unread + ")</a><br> " ;
	      }
		  total_unread_threads++;
	   }
    });
	
	if (total_unread_threads > 0) {
	   document.title = settings.website_title + " (" + total_unread_threads + ")";
    } else {
	   document.title = settings.website_title;
	}
	
	if (display_string == "") {display_string = "(no new updates)";}
	
	if (globals.is_connected == false) {
		if (globals.narrow_width) {
	      $('message_center').innerHTML = intext("NEW")+": "+intext("WARNING: You appear to be not connected")+" " + display_string;
	    } else {
	      $('message_center').innerHTML = intext("NEW")+":<br><br>"+intext("WARNING: You appear to be not connected")+"<br><br>" + display_string;
	    }
	} else {
		if (globals.narrow_width) {
		   $('message_center').innerHTML = intext("NEW")+": " + display_string;
		} else {	
		   $('message_center').innerHTML = intext("NEW")+": <br>" + display_string;
		}
    }
}

function append_to_thread(in_string){
   var temp_array = in_string.split("^?");
   if (parseInt(temp_array[0]) == 0) { return; }

   var num_posts = parseInt(temp_array[0]);  //number of posts the list is giving you
   var hi_reply_num = parseInt(temp_array[1]); //highest reply number, used to set nrt
   var hi_post_num = parseInt(temp_array[2]);

   var ddisplay_string = '';
   var temp_img_src, temp_coltwo_src, temp_colone_src, edit_button, file_attachments;
	
   for (i = 0; i < num_posts; i++) {
		offset = 3 + (7 * i);
		globals.number_of_posts_displayed++;

		if (temp_array[offset+0] == 0) {
		   temp_img_src = '<img border=0 src="'+settings.system_avatar+'">'; 
		} else {
			if (parseInt(temp_array[offset+2]) == 0) {
			   if (settings.avatars_same_size) {
				  temp_img_src = '<img border=0 width="'+settings.max_avatar_dimensions[0]+'" height="'+settings.max_avatar_dimensions[1]+'" src="'+settings.default_avatar+'">';
			   } else {
                  temp_img_src = '<img border=0 src="'+settings.default_avatar+'">';
			   }			
			} else {
			   if (settings.avatars_same_size) {
				  temp_img_src = '<img border=0 width="'+settings.max_avatar_dimensions[0]+'" height="'+settings.max_avatar_dimensions[1]+'" src="file.php?uid=' +temp_array[offset+0]+ '&avatar_number=' +temp_array[offset+2]+ '">';
			   } else {
				  temp_img_src = '<img border=0 src="file.php?uid=' +temp_array[offset+0]+ '&avatar_number=' +temp_array[offset+2]+ '">';
			   }
			}
		}

		if ((settings.avatars_allowed == false) || (parseInt(temp_array[offset+2]) == -1) || (parseInt(temp_array[offset+2]) == 0 && settings.new_user_avatar == 0)) {
		   temp_img_src = "";
		}

		temp_coltwo_src =  '<a id="profile_box'+(globals.number_of_posts_displayed - 1)+'" href="profile.php?user_id=' + temp_array[offset+0] + '">' + temp_array[offset+1] + '<br>' +temp_img_src+ '</a>';

		if (account_info.status > 2) {
		   temp_colone_src = '<a id="post_mod_box'+(globals.number_of_posts_displayed-1)+'" href="popup_user_id.php?user_id=' + temp_array[offset+0] + '&page=' + globals.current_page_of_thread + '&post_position=' + (globals.number_of_posts_displayed-1) + '&thread_id=' + globals.thread_watching + '">'  + temp_array[offset+0] + '</a>'; 
		} else {
		   temp_colone_src = temp_array[offset+0];
		}

		if (temp_array[offset+5] == 1) {
		   edit_button = '<span id=edit_button'+temp_array[offset+6]+' class="plink" style="margin-left:0px;position:relative;top:-4px;padding-top:6px;"><a onclick="javascript:edit_post('+temp_array[offset+6]+')">'+intext("edit")+'</a></span><br>';
		} else {
		   edit_button = '';
		}

		file_attachments = '';
		tmp_result = temp_array[offset+3].match(/&lt;&lt;&lt;FILE:(.*?)&gt;&gt;&gt;/g);
		if (tmp_result) {
			for (x = 0; x < tmp_result.length; x++) {
				tmp_filename = tmp_result[x].replace(/&lt;&lt;&lt;FILE:(.*?)\|.*?&gt;&gt;&gt;/,'$1');      
                tmp_fileid = tmp_result[x].replace(/&lt;&lt;&lt;FILE:.*?\|(.*?)&gt;&gt;&gt;/,'$1');  				
				file_attachments += '<a href="file.php?id=' +tmp_fileid+ '">' + tmp_filename + '</a><br>';
			}
			file_attachments = '<tr id="attachment_row"> <td class="colone"></td><td class="coltwo"> </td><td id="attachments" class="colthree">File attachments:<br>'+file_attachments+'</td><td class="colfour"></td></tr>';
			temp_array[offset+3] = temp_array[offset+3].replace(/(&lt;&lt;&lt;FILE:.*?&gt;&gt;&gt;)/g,'<span style="display:none;">$1</span>');
		}

		ddisplay_string = ddisplay_string + '<tr> <td class="colone">' + temp_colone_src + '</td>' +
			'<td class="coltwo">' + temp_coltwo_src + '</td>' +
			'<td class="colthree">' +edit_button+ '<div id=msg'+temp_array[offset+6]+' >'+  temp_array[offset+3] + '</div></td>' +
			'<td class="colfour">' +  temp_array[offset+4] + '<br><a class="quote_button" onclick="quote_post('+temp_array[offset+6]+',\''+temp_array[offset+1]+'\','+(globals.current_page_of_thread+1)+')">'+intext("Quote")+'</a>' +'</td>' +
			'</tr>'+file_attachments;	 	  
    }
	  
    $('bigtable').insert({bottom:ddisplay_string});
	  
    globals.highest_post_in_thread = hi_post_num;
	      
	jQuery().ready(function() {
		for (i = 0; i < globals.number_of_posts_displayed; i++) {
			jQuery("#profile_box"+i).fancybox({
				'width'				: 800,
				'height'			: 400,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
			});		
			jQuery("#post_mod_box"+i).fancybox({
				'width'				: 400,
				'height'			: 245,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe'
			});			
		}
	});	  
	  
	globals.newly_read_threads += "," + globals.thread_watching + ":" + hi_reply_num;
    mythreads_hash.set(globals.thread_watching,hi_reply_num);  
	$("upload_info").innerHTML = "";
} 

function update_forum(in_string){
   var temp_array = in_string.split("^?");

   if (temp_array[0] > 0) {
      globals.highest_thread_in_forums = temp_array[0];
   }
   
   if (parseInt(temp_array[1]) == 0) {
      return;
   }
 
   for (x = 0; x < parseInt(temp_array[1]); x++) {   
	    var done = false;
	    var total_threads = 0;
	    var total_stickies = 0;
	   
	    var forum_id = parseInt(temp_array[2 + x * 4]);
	    var thread_id = parseInt(temp_array[3 + x * 4]);
	    var title = temp_array[4 + x * 4];
		var state = parseInt(temp_array[5 + x * 4]);

	    if (thread_id > globals.highest_thread_in_forums) { globals.highest_thread_in_forums = thread_id; }
   
        while (done == false) {
		   if ((cache.thread_state[forum_id-1][total_threads] == 3) || (cache.thread_state[forum_id-1][total_threads] == 4)) {
		      total_stickies++;
		   }
		   if ((cache.thread_title[forum_id-1][total_threads] == "&nbsp") || (cache.thread_title[forum_id-1][total_threads] == undefined)) {
			  done = true;
		   }  else {
			  total_threads++;
		   }
		}
		
		for (i = total_threads; i > 0 + total_stickies; i--) {      
		   cache.thread_title[forum_id-1][i+1] = cache.thread_title[forum_id-1][i];
   	       cache.thread_id[forum_id-1][i+1] = cache.thread_id[forum_id-1][i];
		   cache.thread_state[forum_id-1][i+1] = cache.thread_state[forum_id-1][i];
		   
		   cache.thread_title[forum_id-1][i] = cache.thread_title[forum_id-1][i-1];		
		   cache.thread_id[forum_id-1][i] = cache.thread_id[forum_id-1][i-1];		
		   cache.thread_state[forum_id-1][i] = cache.thread_state[forum_id-1][i-1];		
		}

		cache.thread_id[forum_id-1][total_threads+1] = 0;	 
		cache.thread_title[forum_id-1][total_threads+1] = "&nbsp";		

		cache.thread_title[forum_id-1][0 + total_stickies] = title;
		cache.thread_id[forum_id-1][0 + total_stickies] = thread_id;
		cache.thread_state[forum_id-1][0 + total_stickies] = state;
    }
	
	show_forum();

	return; 
}

function enlarge_image(filename,image_id,rand_number) {
   $(filename + "|" +rand_number+"_img").innerHTML = '<a href=javascript:shrink_image("'+filename+'","'+image_id+'",'+rand_number+')> <img src="file.php?id=' + image_id + '&d=inline" title="'+filename+'" alt="'+filename+'">';
}
   
function shrink_image(filename,image_id,rand_number) {
   $(filename + "|" +rand_number+ "_img").innerHTML = '<a href=javascript:enlarge_image("'+filename+'","'+image_id+'",'+rand_number+')> <img src="file.php?id=' + image_id + '&d=inline&t=small" title="'+filename+'" alt="'+filename+'">';
}

function enlarge_offsite_image(image_id) {
   var content = $(image_id + "_eimg").innerHTML;
   content = content.replace(/width="?[0-9]+"?/i,'');   
   content = content.replace(/height="?[0-9]+"?/i,'');      
   content = content.replace(/enlarge_offsite_image/i,'shrink_offsite_image');

   $(image_id + "_eimg").innerHTML = content;
}
   
function shrink_offsite_image(image_id) {
   var content = $(image_id + "_eimg").innerHTML;
   
   if (isIE()) {
	  var stuff = content.match("id=\"?[0-9]+,[0-9]+");
   } else {
      var stuff = content.match("id=\".*?\"");
   }
   
   var temp_string = stuff[0];

   var newwidth = temp_string.replace(/id="?([0-9]+).*/,'$1');
   var newheight = temp_string.replace(/id="?[0-9]+,([0-9]+)"?/,'$1');

   content = content.replace(/shrink_offsite_image/i,'enlarge_offsite_image');
   content = content.replace(/img id/i,'img width="'+newwidth+'" height="'+newheight+'" id');
   
   $(image_id + "_eimg").innerHTML = content;
}

function load_profile_page(user_id) {
    var pars = "user_id=" + user_id;
    var myAjax = new Ajax.Request("get_profile_info.php", {method: 'get', parameters: pars, onComplete: profile_page_response});
}

function profile_page_response(originalRequest)	{
    var part1, part2, part3, part4, part5, part6, mod_stuff, temp_img_src, status_text, send_message_button;
	
    var temp_string = originalRequest.responseText;
    var temp_array = temp_string.split("^?");

	set_theme(window.parent.account_info.theme);	

	if (error_code(temp_array)) {$('profile_title').innerHTML = temp_array[1]; return;}
	var username = temp_array[1];
	var user_id = parseInt(temp_array[3]);
	var profile_text = temp_array[5];
	var avatar_number = parseInt(temp_array[6]);
    var join_date = temp_array[7];
	var num_posts = temp_array[8];
	var last_ip = temp_array[9]
	var status = parseInt(temp_array[10]);
	var is_banned = parseInt(temp_array[11]);
	var ban_expire_time = temp_array[12];
	var last_online = temp_array[13];
	var fb_profile = temp_array[14];
    var li_profile = temp_array[15];
	
	if (window.parent.account_info.status > 2) {
	   if (is_banned == 2) {
	      part1 = '<a onclick="javascript:window.parent.unban('+user_id+',0,0)">'+intext('Unban User')+'</a>';	  
	   }   
	   if (is_banned == 1) {
	      part1 = '<a onclick="javascript:window.parent.unmute('+user_id+',0,0)">'+intext('Unmute User')+'</a>'; 
	   }
	   if (is_banned == 0) {
	      part1 = '<a onclick=\"javascript:mute_user_input('+user_id+',0)">'+intext('Mute User')+'</a><span id="mutebox"></span>'
		  +'<br><a onclick=\"javascript:ban_user_input('+user_id+',0)">'+intext('Ban User')+'</a><span id="banbox"></span>';      
	   }  
	   if (window.parent.account_info.status == 5) {
	      part1 += '<br><a onclick="javascript:window.parent.wipe('+user_id+',-1,1)">'+intext('Wipe Account')+'</a>';
       }

	   part2 = '<br><a onclick="javascript:window.parent.delete_current_avatar('+user_id+')">'+intext('Delete Avatar')+'</a>';

       if (status == 0) { part3 = '<br><a onclick="javascript:window.parent.raise_status('+user_id+')">'+intext('Raise Status')+'</a>';} 
       else if (status == 1) { part3 = '<br><a onclick="javascript:window.parent.lower_status('+user_id+')">'+intext('Lower Status')+'</a>';} 
	   else { part3 = "";}

	   part4 = "";	   
	   if (window.parent.account_info.status == 3) {
	      if (status == 1) {part4 = '<br><a onclick="javascript:window.parent.make_editor('+user_id+')">'+intext('Give ')+settings.name_of_status_2+intext(' Status')+'</a>';}
	      if (status == 2) {part4 = '<br><a onclick="javascript:window.parent.revoke_editor('+user_id+')">'+intext('Revoke ')+settings.name_of_status_2+intext(' Status')+'</a>';}	
	   } else if (window.parent.account_info.status > 4) {
	      if (status == 1) {part4 = '<br><a onclick="javascript:window.parent.make_mod('+user_id+')">'+intext('Give Moderator Status')+'</a>'+
		                    '<br><a onclick="javascript:window.parent.make_editor('+user_id+')">'+intext('Give ')+settings.name_of_status_2+intext(' Status')+'</a>';}
	      if (status == 2) {part4 = '<br><a onclick="javascript:window.parent.make_mod('+user_id+')">'+intext('Give Moderator Status')+'</a>'+
		                    '<br><a onclick="javascript:window.parent.revoke_editor('+user_id+')">'+intext('Revoke ')+settings.name_of_status_2+intext(' Status')+'</a>';}						
          if (status == 3) {part4 = '<br><a onclick="javascript:window.parent.revoke_mod('+user_id+')">'+intext('Revoke Moderator Status')+'</a>';}
	   }
	   part5 = '<br><a onclick="javascript:change_username_input2('+user_id+')">'+intext('Change Username')+'</a><span id="changebox"></span>'; 
	   if (last_ip) {
	   	   mod_stuff = "<td class='colmod'> <span class='profile_title'><br></span>"+intext('ip address')+": " +last_ip+ "<br>"  + part1 + part2 + part3 + part4 + part5 +"</td>";
	   } else {
	   	   mod_stuff = "<td class='colmod'> <span class='profile_title'><br></span>" + part1 + part2 + part3 + part4 + part5 +"</td>";
	   }
    } else {
	   mod_stuff = "";
	}

    if (status == -1) {status_text = "&nbsp;&nbsp;&nbsp;"+intext("Status: System");}
    if (status == 0) {status_text = "&nbsp;&nbsp;&nbsp;"+intext("Status: New User");}
    if (status == 1) {status_text = "&nbsp;&nbsp;&nbsp;"+intext("Status: Regular User");}	
    if (status == 2) {status_text = "&nbsp;&nbsp;&nbsp;"+intext("Status: ")+settings.name_of_status_2;}		
    if (status == 3) {status_text = "&nbsp;&nbsp;&nbsp;"+intext("Status: Moderator");}
    if (status == 5) {status_text = "&nbsp;&nbsp;&nbsp;"+intext("Status: Administrator");}
	if (is_banned == 1) {status_text = "&nbsp;&nbsp;&nbsp;"+intext("Status: Muted");}
	if (is_banned == 2) {status_text = "&nbsp;&nbsp;&nbsp;"+intext("Status: Banned");}
    if (ban_expire_time && is_banned) {status_text += intext(", expires:")+"<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+ban_expire_time;}

	if (avatar_number == 0) {
	   if (settings.avatars_same_size) {
		  temp_img_src = '<img border=0 width="'+settings.max_avatar_dimensions[0]+'" height="'+settings.max_avatar_dimensions[1]+'" src="'+settings.default_avatar+'">';
	   } else {
		  temp_img_src = '<img border=0 src="'+settings.default_avatar+'">';
	   }			   
	} else {
		if (settings.avatars_same_size) {
		   temp_img_src = '<img border=0 width="'+settings.max_avatar_dimensions[0]+'" height="'+settings.max_avatar_dimensions[1]+'" src=file.php?uid=' +user_id+ '&avatar_number=' +avatar_number+ '>';
		} else {
		   temp_img_src = '<img border=0 src=file.php?uid=' +user_id+ '&avatar_number=' +avatar_number+ '>';
		}
	}

	if ((settings.avatars_allowed == false) || (avatar_number == -1) || (avatar_number == 0 && settings.new_user_avatar == 0)) {temp_img_src = '';}

	if (fb_profile) {
	   if (fb_profile == "1") {
	      part6 = "<br>&nbsp;&nbsp;&nbsp;"+intext("Facebook Verified");
	   } else {
	      part6 = "<br>&nbsp;&nbsp;&nbsp;<a target='_blank' href='"+fb_profile+"'>"+intext("Facebook Profile")+"</a>";
	   }
	} else if (li_profile) {
	   if (li_profile == "1") {
	      part6 = "<br>&nbsp;&nbsp;&nbsp;"+intext("LinkedIn Verified");
	   } else {
	      part6 = "<br>&nbsp;&nbsp;&nbsp;<a target='_blank' href='"+li_profile+"'>"+intext("LinkedIn Profile")+"</a>";
	   }
	} else {
	   part6 = "";
	}

	if (status == -1) {
		$('profile_title').innerHTML = "<table border=0><tr><td><img src='"+settings.system_avatar+"'></td>" + 
		"<td class='colmod'>&nbsp;<span class='profile_title'>"+username+"</span><br>&nbsp;&nbsp;&nbsp;"+intext("user id")+": "+user_id+"<br>"+
		status_text+"<br>&nbsp;&nbsp;&nbsp;"+"</tr></table>";
	} else {
		$('profile_title').innerHTML = "<table border=0><tr><td>"+temp_img_src+"</td><td class='colmod'>&nbsp;<span class='profile_title'>"+username+"</span>"
		+"<br>&nbsp;&nbsp;&nbsp;"+intext("user id")+": "+user_id+part6+"<br>&nbsp;&nbsp;&nbsp;"+intext("date joined")+": "+join_date+"<br>&nbsp;&nbsp;&nbsp;"+intext("number of posts")+": "+num_posts+"<br>"
		+status_text+"<br>&nbsp;&nbsp;&nbsp;"+intext("Last online: ")+"<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+last_online+"</td> "+mod_stuff+"</tr></table>";
	}

	if (settings.enable_private_threads && status != -1) {
	   send_message_button = '<br><input type="button" onClick="javascript:window.parent.send_user_message(\''+username.replace(/'/g,"\\'") +'\')" name="sumbit" value="'+intext('Send Message')+'">'
	} else {
	   send_message_button = '';
	}

    $('inner_profile_content').innerHTML = '<div class="profiletext">' + profile_text + '</div>' + send_message_button;
}

function load_ipaddr_page(ip_address) {
	if (window.parent.account_info.status > 4) {
		$('profile_title').innerHTML = 	"<table border=0><tr>" + 
		"<td class='colmod'>&nbsp;<span class='profile_title'>"+intext("IP Address")+": "+ip_address+'</span><br><br><a onclick="javascript:window.parent.ipunban(\''+ip_address+'\')">'+intext('Unban IP address')+'</a>'+"</td></tr></table>";
	} else {
		$('profile_title').innerHTML = 	"<table border=0><tr>" + 
		"<td class='colmod'>&nbsp;<span class='profile_title'>"+intext("IP Address")+": "+ip_address+"</span>";
	}
}

function load_fb_page(fb) {
	if (window.parent.account_info.status > 4) {
		$('profile_title').innerHTML = 	"<table border=0><tr>" + 
		"<td class='colmod'>&nbsp;<span class='profile_title'>"+intext("Facebook ID")+": "+fb+'</span><br><br><a onclick="javascript:window.parent.fbunban(\''+fb+'\')">'+intext('Unban Facebook Account')+'</a>'+"</td></tr></table>";
	} else {
		$('profile_title').innerHTML = 	"<table border=0><tr>" + 
		"<td class='colmod'>&nbsp;<span class='profile_title'>"+intext("Facebook ID")+": "+fb+"</span>";
	}
}

function send_user_message(username) {
   get_tab("pt");
   get_forum_info(12);
   jQuery.fancybox.close();

   $("private_user_list").innerHTML = username;
   $("closed_box").click();
   $("thread_title").focus();
}

function load_popup_thread_title_page(thread_id) {
   var pars = 'thread_id=' + thread_id;
   var myAjax = new Ajax.Request("load_thread_mod_options.php", {method: 'get', parameters: pars, onComplete: load_thread_mod_options_response});     
}

function load_thread_mod_options_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");
   set_theme(window.parent.account_info.theme);
   
   if (error_code(temp_array)) {$('small_popup_title').innerHTML = temp_array[1]; return;}   
   
   var state = temp_array[1];
   var forum_id = temp_array[2];   
   var thread_id = temp_array[3];
   var author_id = temp_array[4];  
   var auto_close = temp_array[5];  
   var part1;
   
   $('small_popup_title').innerHTML = intext("Thread Moderation");
   
   if (state == 0) {
      part1 = '<a onclick=\"javascript:window.parent.close_thread('+thread_id+')">'+intext('Close Thread')+'</a>'; 
      part1 += '<br><a onclick=\"javascript:window.parent.delete_thread('+thread_id+')">'+intext('Delete Thread')+'</a>'; 	
	  if (window.parent.account_info.status > 4 && (forum_id != 12) ) {
         part1 += '<br><a onclick=\"javascript:window.parent.sticky_thread('+thread_id+')">'+intext('Sticky Thread')+'</a>'; 	 
      }
      if (auto_close == 1) {part1 += '<br><a onclick=\"javascript:window.parent.prevent_auto_close('+thread_id+')">'+intext('Prevent Thread From Auto-Closing')+'</a>';} 	  	  
	  if (auto_close == 2) {part1 += '<br><a onclick=\"javascript:window.parent.allow_auto_close('+thread_id+')">'+intext('Allow Thread To Auto-Close')+'</a>';} 	  
   }
   if (state == 1) {
      part1 = '<a onclick=\"javascript:window.parent.open_thread('+thread_id+')">'+intext('Open Thread')+'</a>'; 
      part1 += '<br><a onclick=\"javascript:window.parent.delete_thread('+thread_id+')">'+intext('Delete Thread')+'</a>'; 	 
	  if (window.parent.account_info.status > 4) {	 
		part1 += '<br><a onclick=\"javascript:window.parent.sticky_thread('+thread_id+')">'+intext('Sticky Thread')+'</a>'; 	 
	  }	  
   }
   if (state == 2) {
      if (settings.may_undelete) {
         part1 = '<a onclick=\"javascript:window.parent.undelete_thread('+thread_id+')">'+intext('UnDelete Thread')+'</a>'; 
	  } else { 
	     part1 = '';
	  }
   }
   if (state == 3) {
      part1 = '<a onclick=\"javascript:window.parent.close_thread('+thread_id+')">'+intext('Close Thread')+'</a>'; 
      part1 += '<br><a onclick=\"javascript:window.parent.delete_thread('+thread_id+')">'+intext('Delete Thread')+'</a>'; 	  
	  if (window.parent.account_info.status > 4 && (forum_id != 12) ) {
         part1 += '<br><a onclick=\"javascript:window.parent.unsticky_thread('+thread_id+')">'+intext('UnSticky Thread')+'</a>'; 	  	  
	  }
      if (auto_close == 1) {part1 += '<br><a onclick=\"javascript:window.parent.prevent_auto_close('+thread_id+')">'+intext('Prevent Thread From Auto-Closing')+'</a>';} 	  	  
	  if (auto_close == 2) {part1 += '<br><a onclick=\"javascript:window.parent.allow_auto_close('+thread_id+')">'+intext('Allow Thread To Auto-Close')+'</a>';} 	  	  
   }
   if (state == 4) {
      part1 = '<a onclick=\"javascript:window.parent.open_thread('+thread_id+')">'+intext('Open Thread')+'</a>'; 
      part1 += '<br><a onclick=\"javascript:window.parent.delete_thread('+thread_id+')">'+intext('Delete Thread')+'</a>'; 	 
	  if (window.parent.account_info.status > 4) {	 
		 part1 += '<br><a onclick=\"javascript:window.parent.unsticky_thread('+thread_id+')">'+intext('UnSticky Thread')+'</a>'; 	 
	  }	  
   }
   
   if (parseInt(forum_id) != 12) {
      part1 += '<br><a onclick=\"javascript:show_forum_list('+forum_id+')">'+intext('Move Thread')+'</a> <div id="movebox"></div>'; 
   }
   
   $('inner_small_popup_content').innerHTML = '<div class="modtext">'+part1+'<br></div>';
}

function show_forum_list(current_forum_id){
   var pars = "action=get_forum_names&current_forum_id=" + current_forum_id + "&total_forums=" + settings.total_forums;
   var myAjax = new Ajax.Request("info.php", {method: 'get', parameters: pars, onComplete: show_forum_list_response});	  
}   
   
function show_forum_list_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");

   var count = parseInt(temp_array[1]);
   var part1 = "";
   
   for (var i = 0; i < count; i++) {
      var offset = 2 + (i*2);
      part1 += '<option value="'+temp_array[offset+0]+'">'+temp_array[offset+1]+'</option>';
   }

   $("movebox").innerHTML = '<form action="">'
		+ '<select id=forum_picker name="forum_picker">'
		+ part1		
		+ '</select> </form><input type="button" onClick="javascript:window.parent.move_thread($(\'forum_picker\').value)" name="forum_picker" value="'+intext('Move')+'">';
}

function move_thread(new_forum) {  
   var pars = "action=move_thread&thread_id=" + window.parent.globals.thread_watching + "&forum_id=" + new_forum;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	   
}

function sticky_thread(thread_id) {
   var pars = "action=sticky_thread&thread_id=" + thread_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function unsticky_thread(thread_id) {
   var pars = "action=unsticky_thread&thread_id=" + thread_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function undelete_thread(thread_id) {
   var pars = "action=undelete_thread&thread_id=" + thread_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function delete_thread(thread_id) {
   var pars = "action=delete_thread&thread_id=" + thread_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function open_thread(thread_id) {
   var pars = "action=open_thread&thread_id=" + thread_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function close_thread(thread_id) {
   var pars = "action=close_thread&thread_id=" + thread_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function prevent_auto_close(thread_id) {
   var pars = "action=prevent_auto_close&thread_id=" + thread_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function allow_auto_close(thread_id) {
   var pars = "action=allow_auto_close&thread_id=" + thread_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function load_pt_mod_options(user_id,thread_id) {
   set_theme(window.parent.account_info.theme);
   
   $('small_popup_title').innerHTML = intext("Moderation"); 
   $('inner_small_popup_content').innerHTML = '<div class="modtext"> <a onclick="javascript:window.parent.close_and_open_profile('+user_id+');"> '+intext("Show Profile")+'</a>'
   + '<br><a onclick=\"javascript:window.parent.kick_from_pt('+user_id+','+thread_id+')">'+intext('Kick Out Of Thread')+'</a>' + '</div>';
}

function load_wiki_mod_options(user_id,page,post_position,thread_id,type) {
   var pars = 'user_id=' + user_id + '&thread_id=' + thread_id + '&page=' + page + '&post_position=' + post_position + '&type=' + type ;
   var myAjax = new Ajax.Request("load_wiki_mod_options.php", {method: 'get', parameters: pars, onComplete: load_wiki_mod_options_response});     
}

function load_wiki_mod_options_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");
   
   set_theme(window.parent.account_info.theme);
	 
   if (error_code(temp_array)) {$('small_popup_title').innerHTML = temp_array[1]; return;}   	 
   var is_thread_banned = parseInt(temp_array[2]);
   var user_id = parseInt(temp_array[4]); 
   var thread_id = parseInt(temp_array[5]);
   var message_id = parseInt(temp_array[8]);  

   var part1 = '<a onclick="javascript:window.parent.close_and_open_profile('+user_id+');"> '+intext("Show Profile")+' </a>';
   
   $('small_popup_title').innerHTML = intext("Moderation"); 

   if (is_thread_banned == 1) {
	  var part2 = '<br><a onclick=\"javascript:window.parent.thread_unban('+user_id+','+thread_id+','+message_id+')">'+intext('Thread Unban')+'</a>'; 
   } else {
	  var part2 = '<br><a onclick=\"javascript:window.parent.thread_ban('+user_id+','+thread_id+','+message_id+')">'+intext('Thread Ban')+'</a>';
   }
      
   $('inner_small_popup_content').innerHTML = '<div class="modtext"> '+part1+ part2 + '</div>';
}

function close_and_open_profile(user_id) {
	jQuery.fancybox.close();	
	
	jQuery(document).ready(function () {
			jQuery.fancybox({
				'width'				: 800,
				'height'			: 400,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type': 'iframe',
				'href': 'profile.php?user_id=' + (parseInt(user_id)).toString().toUpperCase(),
			});
	});
}

function load_user_id_mod_options(user_id,page,post_position,thread_id) {
   var pars = 'user_id=' + user_id + '&thread_id=' + thread_id + '&page=' + page + '&post_position=' + post_position;
   var myAjax = new Ajax.Request("load_user_id_mod_options.php", {method: 'get', parameters: pars, onComplete: load_user_id_mod_options_response});     
}

function load_user_id_mod_options_response(originalRequest) {
   var temp_string = originalRequest.responseText;
   var temp_array = temp_string.split("^?");
   var part1, part2, part5;

   set_theme(window.parent.account_info.theme);

   if (error_code(temp_array)) {$('small_popup_title').innerHTML = temp_array[1]; return;}     
   var is_deleted = parseInt(temp_array[1]);
   var is_thread_banned = parseInt(temp_array[2]);
   var is_banned = parseInt(temp_array[3]);  
   var user_id = parseInt(temp_array[4]); 
   var thread_id = parseInt(temp_array[5]);
   var page = parseInt(temp_array[6]);
   var post_position = parseInt(temp_array[7]);  
   var message_id = parseInt(temp_array[8]);  
   var status = parseInt(temp_array[9]); 
   var thread_type = parseInt(temp_array[10]);
   var is_wiped = parseInt(temp_array[11]);
   var avatar_id = parseInt(temp_array[12]);
	
   $('small_popup_title').innerHTML = intext("Moderation"); 
   
   if (is_deleted == 1) {
      if (settings.may_undelete) {
         part1 = '<a onclick=\"javascript:window.parent.undelete_post('+message_id+','+user_id+')">'+intext('Undelete Post')+'</a>'; 
	  } else {
	     part1 = '';
	  }
   } else {
      part1 = '<a onclick=\"javascript:window.parent.delete_post('+message_id+','+user_id+')">'+intext('Delete Post')+'</a>';
   }
   
   part2 = "";
   if (thread_type == 0) {
	   if (is_thread_banned == 1) {
		  part2 = '<br><a onclick=\"javascript:window.parent.thread_unban('+user_id+','+thread_id+','+message_id+')">'+intext('Thread Unban')+'</a>'; 
	   } else {
		  part2 = '<br><a onclick=\"javascript:window.parent.thread_ban('+user_id+','+thread_id+','+message_id+')">'+intext('Thread Ban')+'</a>';
	   }
   } else if (thread_type > 0) {
	   if (is_thread_banned == 0) {
		  part2 = '<br><a onclick=\"javascript:window.parent.kick_from_pt('+user_id+','+thread_id+')">'+intext('Kick Out Of Thread')+'</a>';
	   }   
   }

   part5 = '<br><a onclick=\"javascript:window.parent.delete_avatar('+user_id+','+message_id+')">'+intext('Delete Avatar')+'</a>';
   part6 = '<br><a onclick="javascript:change_username_input('+user_id+','+message_id+')">'+intext('Change Username')+'</a><div id="changebox"></div>'; 
   
   if (is_wiped == 1) {part2 = '';}
   if (avatar_id == -1) {part5 = '';}
   
   $('inner_small_popup_content').innerHTML = '<div class="modtext">' +
   part1 + part2 + part5 + part6 + '</div>';
}

function ban_user_input(user_id,thread_id) {
   $("banbox").innerHTML = '<form action="">'
		+ '<select id=ban_time name="ban_time">'
		+' <option value="1">'+intext('1 day')+'</option>'	
		+' <option value="2">'+intext('2 days')+'</option>'			
		+' <option value="4">'+intext('4 days')+'</option>'	
		+' <option value="7">'+intext('1 week')+'</option>'	
		+' <option value="14">'+intext('2 weeks')+'</option>'		
		+' <option value="28">'+intext('4 weeks')+'</option>'			
		+' <option value="permanent">'+intext('Permanent')+'</option>'				
		+ '</select>'
		+ '<input type="button" onClick="javascript:window.parent.ban($(\'ban_time\').value,'+user_id+','+thread_id+',1)" value="'+intext('Ban')+'"></form>';
}

function mute_user_input(user_id,thread_id) {
   $("mutebox").innerHTML = '<form action="">'
		+ '<select id=ban_time name="ban_time">'
		+' <option value="1">'+intext('1 day')+'</option>'	
		+' <option value="2">'+intext('2 days')+'</option>'			
		+' <option value="4">'+intext('4 days')+'</option>'	
		+' <option value="7">'+intext('1 week')+'</option>'	
		+' <option value="14">'+intext('2 weeks')+'</option>'		
		+' <option value="28">'+intext('4 weeks')+'</option>'			
		+' <option value="permanent">'+intext('Permanent')+'</option>'				
		+ '</select>'
		+ '<input type="button" onClick="javascript:window.parent.mute($(\'ban_time\').value,'+user_id+','+thread_id+',1)" value="'+intext('Mute')+'"></form>';
}

function change_username_input(user_id,message_id) {
   $("changebox").innerHTML = '<input type="text" id="username_entry" maxlength="'+settings.max_username_length+'" style="width:100px" class="theinputbox">'+
   '<input type="button" onClick="javascript:window.parent.change_username($(\'username_entry\').value,'+user_id+','+message_id+')" value="'+intext('Change')+'">';
}

function change_username_input2(user_id) {
   $("changebox").innerHTML = '<input type="text" id="username_entry" maxlength="'+settings.max_username_length+'" style="width:100px" class="theinputbox">'+
   '<input type="button" onClick="javascript:window.parent.change_username2($(\'username_entry\').value,'+user_id+')" value="'+intext('Change')+'">';
}

function change_username(new_name,user_id,message_id) {
   pars = "action=change_username&new_name=" + encodeURIComponent(new_name) + "&user_id=" + user_id + "&message_id=" + message_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function change_username2(new_name,user_id) {
   var pars = "action=change_username2&new_name=" + encodeURIComponent(new_name) + "&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function delete_wiki_post(thread_id, revision, user_id) {
   var pars = "action=delete_wiki_post&thread_id=" + thread_id + "&revision=" + revision + "&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function kick_from_pt(user_id,thread_id) {
   var pars = "action=kick_from_pt&user_id=" + user_id + "&thread_id=" + thread_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function delete_post(message_id, user_id) {
   var pars = "action=delete_post&message_id=" + message_id + "&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function undelete_post(message_id, user_id) {
   var pars = "action=undelete_post&message_id=" + message_id + "&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function raise_status(user_id) {
   var pars = "action=raise_status&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function lower_status(user_id) {
   var pars = "action=lower_status&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function make_mod(user_id) {
   var pars = "action=make_mod&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function revoke_mod(user_id) {
   var pars = "action=revoke_mod&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function make_editor(user_id) {
   var pars = "action=make_editor&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function revoke_editor(user_id) {
   var pars = "action=revoke_editor&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function delete_current_avatar(user_id) {
   var pars = "action=delete_current_avatar&user_id=" + user_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function delete_avatar(user_id,message_id) {
   var pars = "action=delete_avatar&user_id=" + user_id + "&message_id=" + message_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function thread_ban(user_id,thread_id,message_id) {
   var pars = "action=thread_ban&user_id=" + user_id + "&thread_id=" + thread_id + "&message_id=" + message_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function thread_unban(user_id,thread_id,message_id) {
   var pars = "action=thread_unban&user_id=" + user_id + "&thread_id=" + thread_id + "&message_id=" + message_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function ban(time,user_id,thread_id,dupe_check) {
   var pars = "action=ban&user_id=" + user_id + "&thread_id=" + thread_id + "&time=" + time + "&dupe_check=" + dupe_check;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function unban(user_id,message_id) {
   var pars = "action=unban&user_id=" + user_id + "&message_id=" + message_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function mute(time,user_id,thread_id,dupe_check) {
   var pars = "action=mute&user_id=" + user_id + "&thread_id=" + thread_id + "&time=" + time + "&dupe_check=" + dupe_check;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function unmute(user_id,message_id) {
   var pars = "action=unmute&user_id=" + user_id + "&message_id=" + message_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function wipe(user_id,wipe_type,dupe_check) {
   if (wipe_type == -1) {
		if (confirm(intext("Wipe account from database?"))) {
		   wipe_type = '1';
		} else {
		   return;
		}
		if (confirm(intext("Ban this account's last known IP address?"))) {
		   wipe_type += '1';
		} else {
		   wipe_type += '0';
		}
		if (confirm(intext("Delete all posts made by this account?"))) {
		   wipe_type += '1';
		} else {
		   wipe_type += '0';
		}		
		if (confirm(intext("Delete all threads made by this account?"))) {
		   wipe_type += '1';
		} else {
		   wipe_type += '0';
		}				
   }
   var pars = "action=wipe&user_id=" + user_id + "&wipe_type=" + wipe_type + "&dupe_check=" + dupe_check;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function ipunban(ip_address) {
   var pars = "action=ipunban&ip=" + encodeURIComponent(ip_address);
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function fbunban(fb) {
   var pars = "action=fbunban&fb=" + encodeURIComponent(fb);
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function deletefile(file_id) {
   var pars = "action=deletefile&file_id=" + file_id;
   var myAjax = new Ajax.Request("mod.php", {method: 'get', parameters: pars, onComplete: mod_response});	
}

function mod_response(originalRequest) {
    var temp_string = originalRequest.responseText;
    var temp_array = temp_string.split("^?");
    var offset, temp_array2;

	if (error_code(temp_array)) {
	   alert(temp_array[1]); 
	}
	
	if (parseInt(temp_array[0]) == 2) {
	   offset = 4;
	   temp_array2 = temp_array;
	   for (h = 0; h < parseInt(temp_array2[2]); h++) {
	       var answer = confirm(temp_array2[offset+1] + " (" + temp_array2[offset] + ") " + intext("has the same IP address as the user you've just banned, ban them as well?"));
		   if (answer) {
		      window.parent.ban(temp_array2[3],parseInt(temp_array2[offset]),0,0); 
		   }
		   offset += 2;
	   }
	}
	if (parseInt(temp_array[0]) == 5) {
	   offset = 4;
	   temp_array2 = temp_array;
	   for (h = 0; h < parseInt(temp_array2[2]); h++) {
	       var answer = confirm(temp_array2[offset+1] + " (" + temp_array2[offset] + ") " + intext("has the same IP address as the user you've just wiped, wipe them as well?"));
		   if (answer) {
		      window.parent.wipe(parseInt(temp_array2[offset]),temp_array2[3],0); 
		   }
		   offset += 2;
	   }
	}	
	if (parseInt(temp_array[0]) == 7) {
	   offset = 4;
	   temp_array2 = temp_array;
	   for (h = 0; h < parseInt(temp_array2[2]); h++) {
	       var answer = confirm(temp_array2[offset+1] + " (" + temp_array2[offset] + ") " + intext("has the same IP address as the user you've just muted, mute them as well?"));
		   if (answer) {
		      window.parent.mute(temp_array2[3],parseInt(temp_array2[offset]),0,0); 
		   }
		   offset += 2;
	   }
	}	
	if (parseInt(temp_array[0]) == 6) {
	   alert(intext("File deleted")); 
    }	
	if ((parseInt(temp_array[0]) != 3) && (parseInt(temp_array[0]) != 4) && (parseInt(temp_array[0]) != -1) && (globals.current_forum_tab != "None")) {
	   get_thread_page(globals.thread_watching,globals.current_page_of_thread,0,0);
    }

	jQuery.fancybox.close();
}
