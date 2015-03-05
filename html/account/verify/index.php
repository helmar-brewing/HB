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
$redir = $_GET['redir'];


// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(2);

switch($user->login()){
    case 0:
        $db_auth->close();
        $db_main->close();
        header('Location: '.$protocol.$site.'/account/login/?redir='.$currentpage,TRUE,303);
        ob_end_flush();
        exit;
        break;
    case 1:
        $user->regen();
        break;
    case 2:
        // they already have an elevated login, how did they get here? let's make them verify anyways, to avoid a loop.
        $user->regen();
        break;
    default:
        $db_auth->close();
        $db_main->close();
        ob_end_flush();
        print'You created a tear in the space time continuum.';
        exit;
        break;
}





    ob_end_flush();
    /* <HEAD> */ $head=''; // </HEAD>
    /* PAGE TITLE */ $title='Helmar Brewing Co';
    /* HEADER */ require('layout/header0.php');


    /* HEADER */ require('layout/header2.php');
    /* HEADER */ require('layout/header1.php');

    /* FOCUS CURSOR */ print'<script type="text/javascript">$(document).ready(function(){$("#password").focus()});</script>';

    print'
        <h1>Verify</h1>
        <form action="'.$protocol.$site.'/'.$redir.'" method="post">
    ';
    if(isset($redir)){
        print'
            <input type="hidden" name="redir" value="'.$redir.'" />
        ';
    }
    print'
            <p><strong>' . $user->username . '</strong>, for security purposes, please verify your password.</p>
            <label for="password">Password</label>
            <input type="password" name="pass" tabindex="1" id="password" />
            <input type="submit" value="Verify" tabindex="2" />
            <div>
                Need an account? <a href="'.$protocol.$site.'/account/register/">Register</a> | Having trouble logging in? <a href="'.$protocol.$site.'/account/recover/">Account Recovery</a>
            </div>
        </form>
    ';

    /* FOOTER */ require('layout/footer1.php');

    $db_auth->close();
    $db_main->close();

?>
