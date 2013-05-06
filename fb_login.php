<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
require 'omegabb.php';
require 'facebook/src/facebook.php';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
	'appId'  => $settings->fb_appId ,
	'secret' => $settings->fb_secret,
));

// Get User ID
$user = $facebook->getUser();

// We may or may not have this data based on whether the user is logged in.
//
// If we have a $user id here, it means we know the user is logged into
// Facebook, but we don't know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.

if ($user) {
	try {
		// Proceed knowing you have a logged in user who's authenticated.
		$user_profile = $facebook->api('/me');
	} catch (FacebookApiException $e) {
		error_log($e);
		$user = null;
	}
}

// Login or logout url will be needed depending on current user state.
if ($user) {
	$logoutUrl = $facebook->getLogoutUrl();
} else {
	$loginUrl = $facebook->getLoginUrl();
}

if ($user) {
	Logout();  

	$first_query = "SELECT * "
		. "\nFROM user "
		. "\nWHERE facebook_id='$user' AND status > -1"; 

	$message = "";
	$error_message = "";

	$row = perform_query($first_query,SELECT);
	if ($row) { //account already exists, just login
		$message = FacebookLogin($user_profile["id"],$user_profile["name"],$user_profile["link"]);
		$temp_array = explode("^?",$message);
		if ($temp_array[0] == "-1") {
			$error_message = $temp_array[1];
		}
	} else { //create account then login
		$message = FacebookNewUser ($user_profile["id"],$user_profile["name"],$user_profile["link"],$_SESSION['fb_'.$settings->fb_appId.'_access_token']); 
		$temp_array = explode("^?",$message);
		if ($temp_array[0] == "-1") {
			$error_message = $temp_array[1];
		} else {
			$message = FacebookLogin($user_profile["id"],$user_profile["name"],$user_profile["link"]);
			$temp_array = explode("^?",$message);
			if ($temp_array[0] == "-1") {
			   $error_message = $temp_array[1];
			}
		}
	}	

	if ($error_message) {
		 echo "<script type=\"text/javascript\">" .
		"alert(\"".$error_message."\"); window.location = \"".$settings->website_url."\"" .
		"</script>"; 
	} else {
		echo "<script type=\"text/javascript\">" .
		"window.location = \"".$settings->website_url."\"" .
		"</script>"; 
	}
} else { 
	echo "<script type=\"text/javascript\">" .
	"window.location = \"".$loginUrl."\"" .
	"</script>"; 
}
?>