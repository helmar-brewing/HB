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

/* PAGE VARIABLES */
if(isset($_POST['redir'])){
	$redir = $_POST['redir'];
}else{
	$redir = $_GET['redir'];
}
$password = $_POST['pass'];
$username =  strtolower($_POST['username']);
//


$user = new phnx_user;
$user->checklogin(2);


if($user->login() === 1 || $user->login() === 2){
	$user->regen();
	$db_auth->close();
	$db_main->close();
	if(isset($redir)){
		header("Location: $protocol://$site/$redir",TRUE,303);
		exit;
	}else{
		header("Location: $protocol://$site",TRUE,303);
		exit;
	}
}else{

	
	// 0. CHECK IF SUBMITTED
	if(empty($_POST)){
		$go = FALSE;
		$show = TRUE;
	}else{
		$go = TRUE;
		$show = FALSE;
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
		}else{
			$go = FALSE;
			$db_auth->close();
			$db_main->close();
			unset($username,$password);
			$error = '<p>You entered an incorrect password.</p>';
			$show = TRUE;
		}
	}
	

	// 6. DO THE LOGIN
	if($go){
		$user->newlogin();
		$db_auth->close();
		if(isset($redir)){
			header("Location: $protocol://$site/$redir",TRUE,303);
			exit;
		}else{
			header("Location: $protocol://$site",TRUE,303);
			exit;
		}
	}
	

	// 7. SHOW PAGE IF NO LOGIN
	if($show){
		/* <HEAD> */ $head=''; // </HEAD>
		/* PAGE TITLE */ $title='Helmar - Log in';
		include 'layout/header.php';
		/* FOCUS CURSOR */ print'<script type="text/javascript">$(document).ready(function(){$("#username").focus()});</script>';
		ob_end_flush();
		print'
			<div class="page-content">
				<div class="login">
					<h2>Log in</h2>
					<form method="post">
						';if(isset($redir)){print'<input type="hidden" name="redir" value="'.$redir.'" />';}print'
						<div class="register-left">
							<div class="grey-seal">';if($error == ''){print'<div class="push"><p>You can use your facebook account to log in with a single click.</p></div><div class="facebook-button"><a href="https://'.$site.'/account/login/facebook/';if(isset($redir)){print'?redir='.$redir;}print'">Log in w/ Facebook</a></div>';}print''.$error.'</div>
						</div>
						<div class="register-right">
							<div class="push">
								<label class="nudge" for="username">USERNAME</label>
								<input type="text" name="username" tabindex="1" id="username" />
								<label for="password">Password</label>
								<input type="password" name="pass" tabindex="2" id="password" />
							</div>
							<input type="submit" value="Login" tabindex="3" />
						</div>
						<div class="login-footer">Need an account? <a href="'.$protocol.'://'.$site.'/account/register/">Register</a> | Having trouble logging in? <a href="'.$protocol.'://'.$site.'/account/recover/">Account Recovery</a></div>
					</form>
				</div>
			</div>
		';
		include 'layout/footer.php';
	}
}
?>