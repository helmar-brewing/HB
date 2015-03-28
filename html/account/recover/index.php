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
		<section>
			<h4>Account</h4>
			<h1>Register</h1>
			<div></div>
		</section>
    ';

    /* FOOTER */ require('layout/footer1.php');

    $db_auth->close();
    $db_main->close();
}

?>
