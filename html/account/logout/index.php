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
	$currentpage = 'account/';
	$do = $_POST['do'];
//


$user = new phnx_user;
$user->logout();

/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Helmar Account - Logout';

print'
		<div class="login">
			<h2>Log out</h2>
			<form action="'.$protocol.'://'.$site.'/account/logout/all/" method="post">
				<input type="hidden" name="username" value="' . $username . '" />
				<div class="register-left">
					<div class="grey-seal">
						<p>You have been logged out on this computer. To log out of <strong>all</strong> computers enter your username and password and click or tap "Invalidate all logins".</p>
						<p>&#187; <a href="'.$protocol.'://'.$site.'/">Go to homepage</a></p>
						<p>&#187; <a href="'.$protocol.'://'.$site.'/account/login/">Log back in</a></p>
					</div>
				</div>
				<div class="register-right">
					<div class="push">
						<label class="nudge" for="username">USERNAME</label>
						<input type="text" name="username" tabindex="1" id="username" />
						<label for="password">Password</label>
						<input type="password" name="pass" tabindex="2" id="password" />
					</div>
					<input class="inval" type="submit" value="Invalidate all logins" tabindex="3" />
				</div>
				<div class="colbreak"></div>
			</form>
		</div>
	';


$db_auth->close();
?>