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
$password1 = $_POST['password1'];
$password2 = $_POST['password2'];

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);


// start the session and find out what step we left off on
session_start();
if(isset($_SESSION['step'])){
	$step = $_SESSION['step'];
}else{
    $step = 1;
}

do{
    switch($step){

        // check if logged in
        case 1:
            if($user->login() === 1){
                $step = 0;
                $msg .= '<li>You cannot use this form to reset your password while you are logged in. Please log out and try again.</li>';
            }else{
                $step = 2;
            }
            break;

        // check for token
        case 2:
            if(isset($_GET['token'])){
                $token = explode('_', $_GET['token']);
                if(count($token, COUNT_RECURSIVE)!=2){
                    $step = 0;
                    $msg .= '<li>This link is invalid. Please return to <a href="'.$protocol.$site.'/account/recover/">account recovery</a> and try again.</li>';
                }else{
                    $step = 3;
                }
            }else{
                $step = 0;
                $msg .= '<li>This link is invalid. Please return to <a href="'.$protocol.$site.'/account/recover/">account recovery</a> and try again.</li>';
            }
            break;

        // make sure the token is real
        case 3:
            $uname_via_ID = db1($db_main,"SELECT username FROM users WHERE id='".base_convert($token[0], 36, 10)."' LIMIT 1");
            $uname_via_token = db1($db_main,"SELECT username FROM users WHERE token='".$token[1]."' LIMIT 1");
            if($uname_via_ID == FALSE || $uname_via_token == FALSE || $uname_via_ID != $uname_via_token){
                $step = 0;
                $msg .= '<li>This link is invalid. Please return to <a href="'.$protocol.$site.'/account/recover/">account recovery</a> and try again.</li>';
            }else{
                $new_token = substr(md5(uniqid(rand(),true)), 0, 25);
                $db_main->query("UPDATE users SET token='$new_token' WHERE username='$uname_via_ID'");
                $_SESSION['token'] = $new_token;
                $_SESSION['username'] = $uname_via_ID;
                $_SESSION['attemp'] = 0;
                $form = 'username';
                $_SESSION['step'] = 4;
                $step = 4;
            }
            break;

        // check for blank username
        case 4:
            if($_POST['username'] == ''){
                $form = 'username';
                $_SESSION['step'] = 4;
                $step = 0;
                $msg .= '<li>You did not enter a username.</li>';
            }else{
                $step = 5;
                $_SESSION['step'] = 5;
            }
            break;

        // check username
        case 6:
            $token_from_db = db1($db_main,"SELECT token FROM users WHERE username='".$_SESSION['uname']."' LIMIT 1");
            if($token_from_db === $_SESSION['token']){
                if($_POST['username'] === $_SESSION['username']){
                    $new_token = substr(md5(uniqid(rand(),true)), 0, 25);
                    $db_main->query("UPDATE users SET token='$new_token' WHERE username='$uname_via_ID'");
                    $_SESSION['token'] = $new_token;
                    $form = 'password';
                    $_SESSION['step'] = 7;
                    $step = 7;
                }else{
                    $step = 0;
                    $msg .= '<li>This link is invalid. Please return to <a href="'.$protocol.$site.'/account/recover/">account recovery</a> and try again.</li>';
                    $user->kill_session();
                }
            }else{
                $step = 0;
                $msg .= '<li>This link is invalid. Please return to <a href="'.$protocol.$site.'/account/recover/">account recovery</a> and try again.</li>';
                $user->kill_session();
            }
            break;

        // check password
        case 7:
        $token_from_db = db1($db_main,"SELECT token FROM users WHERE username='".$_SESSION['uname']."' LIMIT 1");
        if($token_from_db === $_SESSION['token']){
            if($_SESSION['attemp'] < 4){
                if($password1 == ''){
                    $stop = TRUE;
                    $msg .= '<li>You did not enter a password. (passwords, must be at least 8 characters long)</li>';
                }else{
                    if(strlen($password1)<8){
                        $stop = TRUE;
                        $msg .= '<li>Your password must be at least 8 characters long.</li>';
                    }
                    if(preg_match('/[\'\"]+/',$password1)===1){
                        $stop = TRUE;
                        $msg .= '<li>Your password may not contain single or double quotes.</li>';
                    }
                    if($password1 != $password2){
                        $stop = TRUE;
                        $msg .= '<li>Your password did not match.</li>';
                    }
                }
                if($stop){
                    $new_token = substr(md5(uniqid(rand(),true)), 0, 25);
                    $db_main->query("UPDATE users SET token='$new_token' WHERE username='$uname_via_ID'");
                    $_SESSION['token'] = $new_token;
                    $form = 'password';
                    $_SESSION['step'] = 7;
                    $step = 0;
                    $_SESSION['attemp']++;
                }else{
                    $step = 8;
                    $_SESSION['step'] = 8;
                }
            }else{
                $step = 0;
                $msg .= '<li>Too many failed attempts.</li>';
                $msg .= '<li>This link is invalid. Please return to <a href="'.$protocol.$site.'/account/recover/">account recovery</a> and try again.</li>';
                $user->kill_session();
            }
        }else{
            $step = 0;
            $msg .= '<li>This link is invalid. Please return to <a href="'.$protocol.$site.'/account/recover/">account recovery</a> and try again.</li>';
            $user->kill_session();
        }
        break;

        //change the password
        case 8:
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
            try{
                $hash = $user->new_hash($password1);
                $db_main->query("UPDATE users SET token='', saltedHash='$hash' WHERE username='".$_SESSION['uname']."' LIMIT 1");
                $msg = '<li>Your password has been successfully reset.</li>';
            }catch(mysqli_sql_exception $e){
                $msg .= '<li>We are sorry, there was a problem updating your password. (ref: data)</li>';
                $msg .= '<li>This link is invalid. Please return to <a href="'.$protocol.$site.'/account/recover/">account recovery</a> and try again.</li>';
            }catch(Exception $e){
                $msg .= '<li>We are sorry, there was a problem updating your password. (ref: hash)</li>';
                $msg .= '<li>This link is invalid. Please return to <a href="'.$protocol.$site.'/account/recover/">account recovery</a> and try again.</li>';
            }
            mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
            $step = 0;
            $user->kill_session();
            break;

    }
}while($step !== 0);




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
			<h1>Password Reset</h1>
            <ul>'.$msg.'</ul>
';
switch($form){
    case 'username':
        print'
            <form action="'.$protocol.$site.'/account/recover/password/">
                <p>Please enter your username to continue.</p>
                <label for="username">Username</label>
                <input type="text" id="username" name="username">
                <input type="submit" value="Continue">
            <form>
        ';
        break;
    case 'password':
        print'
            <form action="'.$protocol.$site.'/account/recover/password/">
                <p>Please enter your username to continue.</p>
                <label for="password1">Password</label>
                <input type="password" id="password1" name="password1">
                <label for="password2">Confirm Password</label>
                <input type="password" id="password2" name="password2">
                <input type="submit" value="Reset Password">
            <form>
        ';
        break;
}
print'
		</div>
	</section>
';

/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();


?>
