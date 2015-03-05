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
        $db_auth->close();
        $db_main->close();
        header('Location: '.$protocol.$site.'/account/verify/?redir='.$currentpage,TRUE,303);
        ob_end_flush();
        exit;
        break;
    case 2:
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
/* <HEAD> */ $head='
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script type="text/javascript">
        Stripe.setPublishableKey(\''.$apikey['stripe']['public'].'\');
    </script>
'; // </HEAD>
/* PAGE TITLE */ $title='Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');


/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');



print'
 hi!
';

/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
