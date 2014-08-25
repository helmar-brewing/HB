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
//


$user = new phnx_user;
$user->checklogin(2);

/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Account - Logout';
include 'layout/header.php';


if($user->login() === 2){
	$user->logout('all');
	$logout = TRUE;
}else{
	if(isset($_POST['username'])){
		$user->username = $_POST['username'];
		if($user->comparepass()){
			$user->logout('all');
			$logout = TRUE;
		}else{
			$logout = FALSE;
		}
	}else{
		$logout = FALSE;
	}
}

if($logout){
	$message = 'You have been successfully logged out of all locations.';
}else{
	$message = 'There was an error logging you out of all locations. We could not verify your identity.<br /><br /><a href="'.$protocol.'://'.$site.'/account/logout/">Log out</a> of just this computer.<br /><br />View all current logins and try again on the <a href="'.$protocol.'://'.$site.'/account/">account page.</a>';

}


ob_end_flush();
print'
	<div class="page-content">
		<div class="login">
			<h2>Log out</h2>
			<form action="'.$protocol.'://'.$site.'/account/logout/all/" method="post">
				<input type="hidden" name="username" value="' . $username . '" />
				';if(isset($redir)){print'<input type="hidden" name="redir" value="'.$redir.'" />';}print'
				<div class="register-left">
					<div class="grey-seal"></div>
				</div>
				<div class="register-right">
						<p>'.$message.'</p>
						<p>&#187; <a href="'.$protocol.'://'.$site.'/">Go to homepage</a></p>
						<p>&#187; <a href="'.$protocol.'://'.$site.'/account/login/">Log back in</a></p>
				</div>
				<div class="colbreak"></div>
			</form>
		</div>
	</div>
';

include 'layout/footer.php';


$db_auth->close();
$db_main->close();
?>