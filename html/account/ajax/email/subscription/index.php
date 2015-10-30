<?php

/* TEST FOR SUBMISSION */  if(empty($_GET)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

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
require_once('classes/mailchimp.class.php');
$chimp = new \DrewM\MailChimp\MailChimp($apikey['mailchimp']);



$user = new phnx_user;
$user->checklogin(1);
$user->checksub();
if($user->login() === 1){




}else{
    $error = '1';
    $h1 = 'Error';
    $content = '<p>There was an error updating your account. Please refresh the page and try again.</p><p>(ref. auth fail)</p>';
}

$json = array(
	'error'     	=> $error,
	'h1'        	=> $h1,
	'content'   	=> $content,
	'return'		=> $return
);

$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();

?>
