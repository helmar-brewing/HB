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

	// check subscription
	//if($user->subscription['status'] !== 'active' && $user->subscription['status'] !== 'trialing'){
	//	throw new Exception('You must have an active subscription to update your checklist');
	//}

	// get current qty
	$quantity = db1($db_main, "SELECT marketSale FROM userCardChecklist WHERE userid='".$user->id."' AND series='$series' and cardnum='".$_GET['cardnum']."' LIMIT 1");
	$quantity2 = db1($db_main, "SELECT quantity FROM marketSale WHERE userid='".$user->id."' AND series='$series' and cardnum='".$_GET['cardnum']."' LIMIT 1");

	if($quantity === false){
		$quantity = 'none';
	}
	if($quantity2 === false){
		$quantity2 = 'none';
	}

	// update the qty

	$now = time();
	$expire = $now + 90*24*60*60;
	// 90 days in future


	if($quantity === '0'){
		// if the qty is 0 set it to 1
		$db_main->query("UPDATE userCardChecklist SET marketSale=1 WHERE userid='".$user->id."' AND series='".$series."' and cardnum='".$_GET['cardnum']."' LIMIT 1");
		if($quantity2==='none'){
			$db_main->query("INSERT INTO marketSale (userid, series, cardnum, quantity, lastBumpDate, endDate, expired) VALUES ('".$user->id."', '$series', '".$_GET['cardnum']."', 1, $now, $expire, 'N')");
		}else{
			$db_main->query("UPDATE marketSale SET quantity=1, endDate=$expire, expired='N' WHERE userid='".$user->id."' AND series='".$series."' and cardnum='".$_GET['cardnum']."' LIMIT 1");
		}
		$db_main->query("UPDATE marketSale SET lastBumpDate=$now WHERE userid='".$user->id."' AND expired = 'N'");
		$msg = 'You have added card '.$_GET['cardnum'].' to your collection';
	}elseif($quantity === '1'){
		// if the qty is 1 set it to 0
		$db_main->query("UPDATE userCardChecklist SET marketSale=0 WHERE userid='".$user->id."' AND series='".$series."' and cardnum='".$_GET['cardnum']."' LIMIT 1");
		if($quantity2==='none'){
			// do nothing, this elseif is removing a value and it doesn't exist so no need to update marketplace table
		}else{
			$db_main->query("UPDATE marketSale SET quantity=0, endDate=$now, expired='Y' WHERE userid='".$user->id."' AND series='".$series."' and cardnum='".$_GET['cardnum']."' LIMIT 1");
		}
		$msg = 'You have removed card '.$_GET['cardnum'].' from your collection';
	}elseif($quantity === 'none'){
		$db_main->query("INSERT INTO userCardChecklist (userid, series, cardnum, marketSale) VALUES ('".$user->id."', '$series', '".$_GET['cardnum']."', 1)");
		
		if($quantity2==='none'){
			$db_main->query("INSERT INTO marketSale (userid, series, cardnum, quantity, lastBumpDate, endDate, expired) VALUES ('".$user->id."', '$series', '".$_GET['cardnum']."', 1, $now, $expire, 'N')");
		}else{
			$db_main->query("UPDATE marketSale SET quantity=1, endDate=$expire, expired='N' WHERE userid='".$user->id."' AND series='".$series."' and cardnum='".$_GET['cardnum']."' LIMIT 1");
		}
		$db_main->query("UPDATE marketSale SET lastBumpDate=$now WHERE userid='".$user->id."' AND expired = 'N'");
		$msg = 'You have added card '.$_GET['cardnum'].' to your collection';
	}else{
		throw new Exception('There was an error updating the card. [0x1]');
	}


	// get the new qty and set response
	// we do this in a seperate step for when we have option of other quantities
	$qty = db1($db_main, "SELECT marketSale FROM userCardChecklist WHERE userid='".$user->id."' AND series='".$series."' AND cardnum='".$_GET['cardnum']."' LIMIT 1");

	if($qty === FALSE){
		throw new Exception('There was an error updating the card.[0x2]');
	}

	switch($qty){
		case 0:
			$error = 0;
			break;
		case 1:
			$error = 0;
			break;
		default:
			throw new Exception('There was an error updating the card.[0x3]');

	}


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
