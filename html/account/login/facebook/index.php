<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ if($use_https === TRUE){if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;}}

/* SET PROTOCOL FOR REDIRECT */ if($use_https === TRUE){$protocol='https';}else{$protocol='http';}

/* WHICH DATABASES DO WE NEED */
	$db2use = array(
		'db_auth' 	=> TRUE,
		'db_main'	=> TRUE
	);
//

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
	require_once('classes/phnx-user.class.php');
//


session_start();


/* PAGE VARIABLES */
if(isset($_SESSION['redir'])){
	$redir = $_SESSION['redir'];
}elseif(isset($_POST['redir'])){
	$redir = $_POST['redir'];
	$_SESSION['redir'] = $redir;
}else{
	$redir = $_GET['redir'];
	$_SESSION['redir'] = $redir;
}
if($redir == ''){
	unset($redir);
}
$password = $_POST['pass'];
$username =  strtolower($_POST['username']);
$app_id = $apikey['fb_app_id'];
$app_secret = $apikey['fb_app_secret'];
$my_url = 'https://'.$site.'/account/login/facebook/';
//


$user = new phnx_user;
$user->checklogin(1);



if($user->login() === 1){
	session_destroy();	
	$user->regen();
	$db_auth->close();
	$db_main->close();
	if(isset($redir)){
		header("Location: https://$site/$redir",TRUE,303);
		exit;
	}else{
		header("Location: https://$site",TRUE,303);
		exit;
	}
}else{


	// FB.1 GET THE FACEBOOK ACCESS TOKEN
	if(isset($_SESSION['fb_access_token'])){
		$fb_access_token = $_SESSION['fb_access_token'];
	}else{
		$code = $_REQUEST["code"];
		if(empty($code)) {
			$_SESSION['state'] = md5(uniqid(rand(), TRUE)); // CSRF protection
			$dialog_url = 'https://www.facebook.com/dialog/oauth?client_id='.$app_id.'&redirect_uri='.urlencode($my_url).'&state='.$_SESSION['state'].'&scope=';
			header("Location: $dialog_url",TRUE,303);
			exit;
		}
		if($_SESSION['state'] && ($_SESSION['state'] === $_REQUEST['state'])) {
			// GET ACCESS TOKEN
			$token_url = 'https://graph.facebook.com/oauth/access_token?'.'client_id='.$app_id.'&redirect_uri='.urlencode($my_url).'&client_secret='.$app_secret.'&code='.$code;
			$curl = curl_init($token_url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($curl);
			curl_close($curl);
			parse_str($response, $response_arr);
			$fb_access_token = $response_arr['access_token'];
			unset($curl, $response, $response_obj);
			// PUT ACCESS TOKEN INTO SESSION
			$_SESSION['fb_access_token'] = $fb_access_token;
		}else{
			// STOP BECAUSE POSSIBLE CSRF
			print'Possible security issue, we have stopped for your safety. Please start over.';
			exit;
		}
	}
	
	
	// FB.2 GET THE FACEBOOK USER ID
	if(isset($_SESSION['fb_id'])){
		$fb_id = $_SESSION['fb_id'];
	}else{
		$graph_url = 'https://graph.facebook.com/me?access_token='.$fb_access_token;
		$curl = curl_init($graph_url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curl);
		curl_close($curl);
		$response_obj = json_decode($response);
		
		if(isset($response_obj->error)){
			$error = '<p>There was an error accessing facebook, please try again later.</p><p>('.$response_obj->error->message.')</p>';
			$show = FALSE;
			$FBgo = FALSE;
		}else{
			$FBgo = TRUE;
			$fb_id = $response_obj->id;
			unset($curl, $response, $response_obj);
			$_SESSION['fb_id'] = $fb_id;
		}

	}
	
	// FB.3 SEE IF THERE IS A USER ON FILE WITH THAT FB ID
	if($FBgo){
		if($user->check_fb_id($fb_id)){
			$go = TRUE;
			$show = FALSE;

			// 4. CHECK USER LEVEL
	

			// 5. CHECK LAST USED VERSION
	

			// 6. DO LOGIN	
			if($go){	
				$user->newlogin();
				$db_auth->close();
				if(isset($redir)){
					header("Location: https://$site/$redir",TRUE,303);
					exit;
				}else{
					header("Location: https://$site",TRUE,303);
					exit;
				}
			}
		}else{
			// 0. CHECK IF SUBMITTED
			if(empty($_POST)){
				$go = FALSE;
				$show = TRUE;
			}else{
				$go = TRUE;
				$show = TRUE;
			}


			// 1. CHECK FOR BLANKS
			if($go){
				if($username == ''){
					$go = FALSE;
					$db_auth->close();
					$db_main->close();
					unset($username,$password);
					$error = '<p>You did not enter a username.</p>';
					$show = TRUE;
				}elseif($password == ''){
					$go = FALSE;
					$db_auth->close();
					$db_main->close();
					unset($username,$password);
					$error = '<p>You did not enter a password.</p>';
					$show = TRUE;
				}
			}
	
	
			// 2. CHECK USERNAME
			if($go){
				if(!$user->exists($username)){
					$go = FALSE;
					$db_auth->close();
					$db_main->close();
					unset($username,$password);
					$error = '<p>The username you entered does not exist.</p>';
					$show = TRUE;
				}
			}
	
	
			// 3. CHECK PASSWORD
			if($go){
				$user->username = $username;
				if($user->comparepass() === TRUE){
					$go = TRUE;
					// SAVE FACEBOOK ID
					$db_auth->query("UPDATE users SET facebook='$fb_id' WHERE username='$username'");
				}else{
					$go = FALSE;
					$db_auth->close();
					$db_main->close();
					unset($username,$password);
					$error = '<p>You entered an incorrect password.</p>';
					$show = TRUE;
				}
			}
	
	
			// 4. CHECK USER LEVEL
	

			// 5. CHECK LAST USED VERSION
	

			// 6. DO LOGIN	
			if($go){	
				$user->newlogin();
				$user->kill_session();
				$db_auth->close();
				$db_main->close();
				if(isset($redir)){
					header("Location: https://$site/$redir",TRUE,303);
					exit;
				}else{
					header("Location: https://$site",TRUE,303);
					exit;
				}
			}
		}
	}
	
	// 7. SHOW PAGE IF NO LOGIN
	
	/* <HEAD> */ $head=''; // </HEAD>
	/* PAGE TITLE */ $title='Helmar - Log in';
	ob_end_flush();
	/* FOCUS CURSOR */ print'<script type="text/javascript">$(document).ready(function(){$("#username").focus()});</script>';
	print'
		<div class="login">
			<h2>Authorize Facebook</h2>
			<form method="post">
				';if(isset($redir)){print'<input type="hidden" name="redir" value="'.$redir.'" />';}print'
				<div class="register-left">
					<div class="grey-seal">'.$error.'</div>
				</div>
				<div class="register-right">
	';
	if($show){
		print'
					<div class="push">	
						<label class="nudge" for="username">USERNAME</label>
						<input type="text" name="username" tabindex="1" id="username" />
						<label for="password">Password</label>
						<input type="password" name="pass" tabindex="2" id="password" />
					</div>
					<input type="submit" value="Authorize" tabindex="3" />
		';
	}
	print'
				</div>
				<div class="colbreak"></div>
				<div class="login-footer">Link your facebook account to your existing HOA account.</div>
			</form>
		</div>
	';
}
?>