<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps();

/* WHICH DATABASES DO WE NEED */
$db2use = array(
	'db_auth' 	=> TRUE,
	'db_main'	=> TRUE
);

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
require_once('classes/phnx-user.class.php');

/* PAGE VARIABLES */
$currentpage = 'account/';

// create user object
$user = new phnx_user;

$user->checklogin(2);
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
	$message = '<span style="color:red;">There was an error logging you out of all locations. We could not verify your identity.</span><br /><br /><a href="'.$protocol.'://'.$site.'/account/logout/">Log out</a> of just this computer.<br /><br />View all current logins and try again on the <a href="'.$protocol.'://'.$site.'/account/">account page.</a>';
}


    ob_end_flush();
    /* <HEAD> */ $head=''; // </HEAD>
    /* PAGE TITLE */ $title='Helmar Brewing Co';
    /* HEADER */ require('layout/header0.php');


    /* HEADER */ require('layout/header2.php');
    /* HEADER */ require('layout/header1.php');

    /* FOCUS CURSOR */ print'<script type="text/javascript">$(document).ready(function(){$("#username").focus()});</script>';

    print'
		<div class="sideimage login">
			<div class="images-wrapper"></div>
			<div class="side-image-content">
				<h4>Account</h4>
				<h1>Log out</h1>
				<p>'.$message.'</p>
				<p><a class="link-button" href="'.$protocol.'://'.$site.'/">Go to homepage</a></p>
				<p><a class="link-button" href="'.$protocol.'://'.$site.'/account/login/">Log back in</a></p>
            </div>
		</div>
    ';

    /* FOOTER */ require('layout/footer1.php');

    $db_auth->close();
    $db_main->close();

?>
