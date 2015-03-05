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
if(isset($_POST['redir'])){
	$redir = $_POST['redir'];
}else{
	$redir = $_GET['redir'];
}
$password = $_POST['pass'];
$username =  strtolower($_POST['username']);


// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

if($user->login() === 1){
	$user->regen();
	$db_auth->close();
	$db_main->close();
	if(isset($redir)){
		header("Location: $protocol://$site/$redir",TRUE,303);
        ob_end_flush();
		exit;
	}else{
		header("Location: $protocol://$site",TRUE,303);
        ob_end_flush();
		exit;
	}
}else{

    $i=0;
    switch($i){
        case 0:

            // Check if submitted
            if(empty($_POST)){
                break;
            }

            // Check for blanks
            if($username == ''){
                $msg = 'You did not enter a username.';
                break;
            }
            if($_POST['pass'] == ''){
                $msg = 'You did not enter a password.';
                break;
            }

            // Check username
            if($user->exists($username) !== TRUE){
                $msg = 'The username you entered does not exist.';
                break;
            }

            // Check password
            $user->username = $username;
            if($user->comparepass() !== TRUE){
                $msg = 'You entered an incorrect password.';
                break;
            }

            // Do the login
            $user->newlogin();
            $db_auth->close();
            $db_main->close();
            if(isset($redir)){
                header('Location: '.$protocol.$site.'/'.$redir,TRUE,303);
                ob_end_flush();
                exit;
                break;
            }else{
                header('Location: '.$protocol.$site,TRUE,303);
                ob_end_flush();
                exit;
                break;
            }

        // What Happened?
        default:
            $msg = 'You were not logged in. Please try again.';
    }



    ob_end_flush();
    /* <HEAD> */ $head=''; // </HEAD>
    /* PAGE TITLE */ $title='Helmar Brewing Co';
    /* HEADER */ require('layout/header0.php');


    /* HEADER */ require('layout/header2.php');
    /* HEADER */ require('layout/header1.php');

    /* FOCUS CURSOR */ print'<script type="text/javascript">$(document).ready(function(){$("#username").focus()});</script>';

    print'
        <h1>Log in</h1>
        <form method="post">
    ';
    if(isset($redir)){
        print'
            <input type="hidden" name="redir" value="'.$redir.'" />
        ';
    }
    print'
            <p>'.$msg.'</p>
            <label for="username">Username</label>
            <input type="text" name="username" tabindex="1" id="username" />
            <label for="password">Password</label>
            <input type="password" name="pass" tabindex="2" id="password" />
            <input type="submit" value="Login" tabindex="3" />
            <div>
                Need an account? <a href="'.$protocol.$site.'/account/register/">Register</a> | Having trouble logging in? <a href="'.$protocol.$site.'/account/recover/">Account Recovery</a>
            </div>
        </form>
    ';

    /* FOOTER */ require('layout/footer1.php');

    $db_auth->close();
    $db_main->close();

}

?>
