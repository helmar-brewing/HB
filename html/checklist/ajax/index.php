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
$ebayID = $_GET['ebayID'];


try{

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries


    // make sure we are logged in
	if($user->login() !== 1){
		throw new Exception('You need to be logged in to update your checklist');
	}

	// check subscription - On March 31, 2019 - commented out this code. I should check where this comes from though. maybe in Stripe?
//	if($user->subscription['status'] !== 'active' && $user->subscription['status'] !== 'trialing'){
//		throw new Exception('You must have an active subscription to update your checklist');
//	}

	// check varibles
	if(empty($_GET['ebayID'])){
		throw new Exception('No ebay ID supplied!');
	}


	// LOOP THROUGH ALL EBAY CARDS
	$x = 0;
	// grab ebay auctions for ebay user
	$R_cards2 = $db_main->query("
	SELECT *
	FROM completed_auctions
	WHERE ebayID ='$ebayID' and cardNum > 0
		"
	);
	$R_cards2->data_seek(0);
	while($card = $R_cards2->fetch_object()){

		$series = $card->series_tag;
		$cardnum = $card->cardNum;



				// get current qty
				$quantity = db1($db_main, "SELECT quantity FROM userCardChecklist WHERE userid='".$user->id."' AND series='$series' and cardnum='$cardnum'");

				if($quantity === false){
					$quantity = 'none';
				}


				// update the qty

				if($quantity === '0'){
					// if the qty is 0 set it to 1
					$db_main->query("UPDATE userCardChecklist SET quantity=1 WHERE userid='".$user->id."' AND series='".$series."' and cardnum='".$cardnum."' LIMIT 1");
					$x++;
					$msg = "You have added $x card(s) to your checklist";
				}elseif($quantity === '1'){
					// if it is checked already, leave it alone!
				//	$db_main->query("UPDATE userCardChecklist SET quantity=0 WHERE userid='".$user->id."' AND series='".$series."' and cardnum='".$cardnum."' LIMIT 1");
					$msg = "You have added $x card(s) to your checklist";
				}elseif($quantity === 'none'){
					$db_main->query("INSERT INTO userCardChecklist (userid, series, cardnum, quantity) VALUES ('".$user->id."', '$series', '".$cardnum."', 1)");
					$x++;
					$msg = "You have added $x card(s) to your checklist";
				}else{
					throw new Exception('There was an error updating the card. [0x1]');
				}


				// get the new qty and set response
				// we do this in a seperate step for when we have option of other quantities
			/* DO WE NEED THIS???
				$qty = db1($db_main, "SELECT quantity FROM userCardChecklist WHERE userid='".$user->id."' AND series='".$series."' AND cardnum='".$cardnum."' LIMIT 1");

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

				} */

			}
			$R_cards2->free();

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
