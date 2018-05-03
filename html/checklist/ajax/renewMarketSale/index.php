<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ if($use_https === TRUE){if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;}}

/* WHICH DATABASES DO WE NEED */
	$db2use = array(
		'db_auth' => TRUE,
		'db_main'	=> TRUE
	);
//

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
require_once('classes/phnx-user.class.php');
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

// check user subscription status
$user->checksub();



try{

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries

    // make sure we are logged in
	if($user->login() !== 1){
		throw new Exception('You need to be logged in to update your checklist');
	}



	$now = time();
	$expire = $now + 180*24*60*60;
	// 180 days in future



		$db_main->query("UPDATE marketSale SET lastBumpDate=$now WHERE userid='".$user->id."' AND expired = 'N'");
		$db_main->query("UPDATE marketSale SET endDate=$expire WHERE userid='".$user->id."' AND expired = 'N'");


    mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries

}catch(Exception $e){
    $error = 1;
    $msg = $e->getMessage();
}


$json = array(
    'error'     => $error,
    'msg'       => $msg,
	'qty'		=> $qty
);

$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();
?>
