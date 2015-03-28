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

// check user login status
$user->checklogin(1);


// check if logged in
if($user->login() === 1){
    $user->regen();
    $db_auth->close();
    $db_main->close();
    header('Location: '.$protocol.$site.'/account/',TRUE,303);
    ob_end_flush();
    exit;
}else{




    ob_end_flush();
    /* <HEAD> */ $head=''; // </HEAD>
    /* PAGE TITLE */ $title='Helmar Brewing Co';
    /* HEADER */ require('layout/header0.php');


    /* HEADER */ require('layout/header2.php');
    /* HEADER */ require('layout/header1.php');

    print'
		<section class="recover">
			<div class="boxes">
				<h4>Account</h4>
				<h1>Account Recovery</h1>
				<div class="recover-box">
					<h2>Forgot Username</h2>
					<p>If you have an account, but forgot your username, enter your email address and we will send it to you. Emails are usually sent immediately, but can take up to an hour or more.</p>
				</div>
				<div class="recover-box">
					<label for="recover_uname_email">Email Address</label>
					<input id="recover_uname_email" type="text">
					<button type="button" onclick="recover(\'uname\')">Send Username</button>
				</div>
				<div class="recover-box">
					<h2>Forgot Password</h2>
					<p>If you forgot your password, we will send you a link that will allow you to reset it. Emails are usually sent immediately, but can take up to an hour or more.</p>
				</div>
				<div class="recover-box">
					<label for="recover_pword_email">Email Address</label>
					<input id="recover_pword_email" type="text">
					<button type="button" onclick="recover(\'pword\')">Reset Password</button>
				</div>
			</div>
		</section>
    ';

    /* FOOTER */ require('layout/footer1.php');

    $db_auth->close();
    $db_main->close();
}

?>
