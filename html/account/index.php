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
$user->checklogin(2);

/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Account';

include 'layout/header.php';


if($user->login() === 0){
	$db_auth->close();
	$db_main->close();
	header("Location: $protocol://$site/account/login/?redir=$currentpage",TRUE,303);
	ob_end_flush();
	exit;
}elseif($user->login() === 1){
	$user->regen();
	$db_auth->close();
	$db_main->close();
	header("Location: $protocol://$site/account/verify/?redir=$currentpage",TRUE,303);
	ob_end_flush();
	exit;
}elseif($user->login() === 2){
	$user->regen();
	ob_end_flush();

	print'
	<div class="account">
		<h1 class="pagetitle">Account</h1>
		
		<div class="yourinfo">
			<h2>Your Info</h2>
			<dl>
				<dt>Username</dt>
				<dd>'.$user->username.'</dd>
				<dt>First Name</dt>
				<dd>'.$user->firstname.'</dd>
				<dt>Last Name</dt>
				<dd>'.$user->lastname.'</dd>
				<input type="button" value="Update Info" />
				<hr />
				<dt>Email</dt>
				<dd>'.$user->email.'</dd>
				<input type="button" value="Change Email" />
			</dl>
		</div>
		
		<div class="active-logins">
			<h2>Active Logins</h2>
			<ul>
	';
	
	foreach($user->get_active_logins() as $login){
		print'
				<li>
					Last accessed on <span>'.date("M j Y",$login['logintime']).'</span> at <span>'.date("g:ia",$login['logintime']).'</span><br />from IP address <span>'.$login['IP'].'</span> with <span>'.$login['browser']['parent'].'</span> on <span>'.$login['browser']['platform'].'</span>
					<input type="button" value="Log out device" />
				</li>
		';
	}

	print'
			</ul>
			<form action="logout/all/" method="post">
				<input type="submit" value="Invalidate all logins" />
			</form>
		</div>
	</div>	
	';
	
	
}else{
	ob_end_flush();
	$db_auth->close();
	$db_main->close();
	print'You created a tear in the space time continuum.';
	exit;
}

$db_auth->close();
$db_main->close();

include 'layout/footer.php';

?>