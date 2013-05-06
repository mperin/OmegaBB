<?php
/*
 (c) Pete Warden <pete@petewarden.com> http://petewarden.typepad.com/ - Mar 21st 2010
 
 Redistribution and use in source and binary forms, with or without modification, are
 permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this 
      list of conditions and the following disclaimer.
   2. Redistributions in binary form must reproduce the above copyright notice, this 
      list of conditions and the following disclaimer in the documentation and/or 
      other materials provided with the distribution.
   3. The name of the author may not be used to endorse or promote products derived 
      from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR 
PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
OF SUCH DAMAGE.
*/

require_once ('./omegabb.php');
require_once ('./oauth/linkedinoauth.php');

// Returns information about the oAuth state for the current user. This includes whether the process
// has been started, if we're waiting for the user to complete the authorization page on the remote
// server, or if the user has authorized us and if so the access keys we need for the API.
// If no oAuth process has been started yet, null is returned and it's up to the client to kick it off
// and set the new information.
// This is all currently stored in session variables, but for real applications you'll probably want
// to move it into your database instead.
//
// The oAuth state is made up of the following members:
//
// request_token: The public part of the token we generated for the authorization request.
// request_token_secret: The secret part of the authorization token we generated.
// access_token: The public part of the token granting us access. Initially ''. 
// access_token_secret: The secret part of the access token. Initially ''.
// state: Where we are in the authorization process. Initially 'start', 'done' once we have access.

function get_linkedin_oauth_state()
{  
    if (empty($_SESSION['linkedinoauthstate']))
        return null;
        
    $result = $_SESSION['linkedinoauthstate'];

    return $result;
}

// Updates the information about the user's progress through the oAuth process.
function set_linkedin_oauth_state($state)
{
    $_SESSION['linkedinoauthstate'] = $state;
}

// Returns an authenticated object you can use to access the LinkedIn API
function get_linkedin_oauth_accessor()
{
    global $settings;
    $oauthstate = get_linkedin_oauth_state();
    if ($oauthstate===null)
        return null;
    
    $accesstoken = $oauthstate['access_token'];
    $accesstokensecret = $oauthstate['access_token_secret'];

    $to = new LinkedInOAuth(
        $settings->linkedin_appId, 
        $settings->linkedin_secret,
        $accesstoken,
        $accesstokensecret
    );

    return $to;
}

// Deals with the workflow of oAuth user authorization. At the start, there's no oAuth information and
// so it will display a link to the LinkedIn site. If the user visits that link they can authorize us,
// and then they should be redirected back to this page. There should be some access tokens passed back
// when they're redirected, we extract and store them, and then try to call the LinkedIn API using them.
function handle_linkedin_oauth()
{
    global $settings;
	
	$oauthstate = get_linkedin_oauth_state();
    
    // If there's no oAuth state stored at all, then we need to initialize one with our request
    // information, ready to create a request URL.
	if (!isset($oauthstate)) {
		$to = new LinkedInOAuth($settings->linkedin_api_key, $settings->linkedin_secret_key);
		
        // This call can be unreliable for some providers if their servers are under a heavy load, so
        // retry it with an increasing amount of back-off if there's a problem.
		$maxretrycount = 1;
		$retrycount = 0;
		while ($retrycount<$maxretrycount)
		{		
			$tok = $to->getRequestToken();

			if (isset($tok['oauth_token'])&& isset($tok['oauth_token_secret']))	break;
			
			$retrycount += 1;
			sleep($retrycount*5);
		}
		
		$tokenpublic = $tok['oauth_token'];
		$tokenprivate = $tok['oauth_token_secret'];
		$state = 'start';
		
        // Create a new set of information, initially just containing the keys we need to make
        // the request.
		$oauthstate = array(
			'request_token' => $tokenpublic,
			'request_token_secret' => $tokenprivate,
			'access_token' => '',
			'access_token_secret' => '',
			'state' => $state,
		);

		set_linkedin_oauth_state($oauthstate);
	}

    // If there's an 'oauth_token' in the URL parameters passed into us, and we don't already
    // have access tokens stored, this is the user being returned from the authorization page.
    // Retrieve the access tokens and store them, and set the state to 'done'.
	if (isset($_REQUEST['oauth_token'])&& ($oauthstate['access_token']=='')) {
		$urlaccesstoken = $_REQUEST['oauth_token'];
		$urlaccessverifier = $_REQUEST['oauth_verifier'];

		$requesttoken = $oauthstate['request_token'];
		$requesttokensecret = $oauthstate['request_token_secret'];

		$to = new LinkedInOAuth(
			$settings->linkedin_api_key, 
			$settings->linkedin_secret_key,
			$requesttoken,
			$requesttokensecret
		);
		
		$tok = $to->getAccessToken($urlaccessverifier);
		
		$accesstoken = $tok['oauth_token'];
		$accesstokensecret = $tok['oauth_token_secret'];

		$oauthstate['access_token'] = $accesstoken;
		$oauthstate['access_token_secret'] = $accesstokensecret;
		$oauthstate['state'] = 'done';

		set_linkedin_oauth_state($oauthstate);		
	}

	$state = $oauthstate['state'];
	
	if ($state=='start') {
		$tokenpublic = $oauthstate['request_token'];
		$to = new LinkedInOAuth($settings->linkedin_api_key, $settings->linkedin_secret_key);
		$requestlink = $to->getAuthorizeURL($tokenpublic, "");

		echo '<script type="text/javascript"> window.location = "'.$requestlink.'"</script>'; 
	} else {
		Logout();  
		
		$user_connections = Array();
        $user_data = $to->oAuthRequest('http://api.linkedin.com/v1/people/~:(id,first-name,last-name,picture-url,public-profile-url)');	
		
		if ($settings->linkedin_request_connections) {  
			$i = 0;
			$loop = true;
		    while ($loop) {
			    $startpoint = $i * 500;
				$user_connections[$i] = $to->oAuthRequest('http://api.linkedin.com/v1/people/~/connections:(id)?start='.$startpoint);
				$p = xml_parser_create();
				xml_parse_into_struct($p, $user_connections[$i], $vals, $index);
				xml_parser_free($p);
			    $connection_count = count($index["ID"]);
				if ($connection_count == 500) {
				   $i++;
				} else {
				   $loop = false;
			    }
			}
        }
		
		$p = xml_parser_create();
		xml_parse_into_struct($p, $user_data, $vals, $index);
		xml_parser_free($p);
		$li_id = $vals[$index["ID"][0]]["value"];
			
		$first_query = "SELECT * "
			. "\nFROM user "
			. "\nWHERE linkedin_id='".$li_id."' AND status > -1"; 

		$message = "";
		$error_message = "";

		$row = perform_query($first_query,SELECT); 
		if ($row) { //account already exists, just login
			$message = LinkedInLogin($li_id);
			$temp_array = explode("^?",$message);
			if ($temp_array[0] == "-1") {
				$error_message = $temp_array[1];
			}
		} else { //create account then login
			$message = LinkedInNewUser ($user_data,$user_connections); 
			$temp_array = explode("^?",$message);

			if ($temp_array[0] == "-1") {
				$error_message = $temp_array[1];
			} else {
				$message = LinkedInLogin($li_id);
				$temp_array = explode("^?",$message);
				if ($temp_array[0] == "-1") {
				   $error_message = $temp_array[1];
				}
			}
		}

		session_destroy();
		session_write_close();

		if ($error_message) {
			echo "<script type=\"text/javascript\">" .
			"alert(\"".$error_message."\"); window.location = \"".$settings->website_url."\"" .
			"</script>"; 
		} else {
			echo "<script type=\"text/javascript\">" .
			"window.location = \"".$settings->website_url."\"" .
			"</script>"; 
		}	
	}
}

session_start();
handle_linkedin_oauth();
?>