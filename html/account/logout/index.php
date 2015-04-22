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

// logout
$user->logout();



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
                <p>You have been logged out on this computer.</p>
                <p><a class="link-button" href="'.$protocol.$site.'/account/login/">Log back in</a></p>
                <p>To log out of <strong>all</strong> computers enter your username and password and click or tap "Invalidate all logins".</p>
		        <form action="'.$protocol.$site.'/account/logout/all/" method="post">
		            <label for="username">Username</label>
		            <input type="text" name="username" tabindex="1" id="username" />
		            <label for="password">Password</label>
		            <input type="password" name="pass" tabindex="2" id="password" />
		            <input type="submit" value="Invalidate all logins" tabindex="3" />
		            <div class="login-footer">
		                Need an account? <a href="'.$protocol.$site.'/account/register/">Register</a> | Having trouble logging in? <a href="'.$protocol.$site.'/account/recover/">Account Recovery</a>
		            </div>
		        </form>
			</div>
		</div>
    ';

    /* FOOTER */ require('layout/footer1.php');

    $db_auth->close();
    $db_main->close();

?>
