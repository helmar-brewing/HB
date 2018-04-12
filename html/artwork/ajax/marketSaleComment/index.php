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


// clean up the series
$series = str_replace("'", "", stripslashes($_GET['series']));


try{

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries

	// check varibles
	if(empty($_GET['series'])){
		throw new Exception('No series number supplied');
	}
	if(empty($_GET['cardnum'])){
		throw new Exception('No card number supplied');
	}

    // make sure we are logged in
	if($user->login() !== 1){
		throw new Exception('You need to be logged in to update your checklist');
	}


	$db_main->query("UPDATE userCardChecklist SET card_note='".$_GET['comment']."' WHERE userid='".$user->id."' AND series='".$series."' and cardnum='".$_GET['cardnum']."' LIMIT 1");
	$db_main->query("UPDATE marketSale SET card_note='".$_GET['comment']."' WHERE userid='".$user->id."' AND series='".$series."' and cardnum='".$_GET['cardnum']."' LIMIT 1");


    mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries

}catch(Exception $e){
    $error = 1;
    $msg = $e->getMessage();
}


$json = array(
    'error'     => $error,
    'msg'       => $msg,
);

$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();
?>
