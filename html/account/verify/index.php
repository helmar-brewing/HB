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
$redir = $_GET['redir'];
//

$user = new phnx_user;
$user->checklogin(1);

/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Helmar Account - Verify';
include 'layout/header.php';

if($user->login() === 1 && isset($redir)){
	$user->regen();
	ob_end_flush();
	/* FOCUS CURSOR */ print'<script type="text/javascript">$(document).ready(function(){$("#password").focus()});</script>';
	print'
		<div class="login">
			<h2>Verify</h2>
			<form action="'.$protocol.'://'.$site.'/' . $redir . '" method="post">
				';if(isset($redir)){print'<input type="hidden" name="redir" value="'.$redir.'" />';}print'
				<div class="register-left">
					<div class="grey-seal"></div>
				</div>
				<div class="register-right">
					<div class="push">
						<p><span style="font-weight:bold">' . $user->username . '</span>, for security purposes, please verify your password.</p>
						<label for="password">Password</label>
						<input type="password" name="pass" tabindex="1" id="password" />
					</div>
					<input type="submit" value="Verify" tabindex="2" />
				</div>
				<div class="colbreak"></div>
			</form>
		</div>
	';
}else{
	$db_auth->close();
	$db_main->close();
	header("Location: $protocol://$site/",TRUE,303);
	ob_end_flush();
	exit;
}


/* BLACKOUT FOR MODAL DIALOGS */ print'<div id="blackout" class="blackout"></div>';

include 'layout/footer.php';

$db_auth->close();
$db_main->close();
?>