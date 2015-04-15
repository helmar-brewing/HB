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
require_once('libraries/stripe/Stripe.php');
Stripe::setApiKey($apikey['stripe']['secret']);

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

// check user subscription status
$user->checksub();

ob_end_flush();

?>


<?php

if(isset($user)){


			if($user->login() === 1 && $user->subscription['status'] == 'active'){
				// run checklist code only if user is logged in AND active subscription


									// run a query to see how many quantity user has
									$checkCount = $db_main->query("SELECT * FROM userCardChecklist WHERE userid='".$user->id."' and series='".$_GET['series']."' and cardnum='".$_GET['cardnum']."' LIMIT 1");
									if($checkCount !== FALSE){
											$checkCount->data_seek(0);
											while($checkC = $checkCount->fetch_object()){
													$quantity = $checkC->quantity;
											}
									} else {
											$quantity = 0;
									}
									$checkCount->free();



									if($quantity===0){
																$query = "INSERT INTO userCardChecklist (userid, series, cardnum, quantity) VALUES ($user->id, '".$_GET['series']."', '".$_GET['cardnum']."','1')";

																$stmt = $db_main->prepare($query);

																if ($stmt->execute()) {
															    	$response['status'] = 'success';
																		$response['message'] = 'You have added '.$_POST['series'].' card number '.$_POST['cardnum'].' to your checklist!';
																		$response['qty']= 1;
																} else {
																	$response['status'] = 'fail';
																	$response['message'] = 'Adding card has failed. Please try again or contact web admin';
																}

										}elseif($quantity===1){
															//remove card if quantity is already 1 because user wants to delete it

																$query = "DELETE FROM userCardChecklist WHERE userid='".$user->id."' and series='".$_GET['series']."' and cardnum='".$_GET['cardnum']."'";

																$stmt = $db_main->prepare($query);

																if ($stmt->execute()) {
																		$response['status'] = 'success';
																		$response['message'] = 'You have REMOVED '.$_POST['series'].' card number '.$_POST['cardnum'].' from your checklist. We hope you enjoyed your time with our artwork!';
																		$response['qty']= 0;
																} else {
																	$response['status'] = 'fail';
																	$response['message'] = 'Removing card has failed. Please try again or contact web admin';
																}

										}	else {
											// we shouldn't get quantity that is not 0 or 1 right now...
										}





				}else{
					// run this else statement if user is not logged in or active subscription
					print 'you are not logged in or a subscriber!';
					$response['message'] = 'Adding card has failed. Please try again or contact web admin';
				}



}else{
	print 'you are not logged in or a subscriber!';
	$response['message'] = 'Adding card has failed. Please try again or contact web admin';
}



echo json_encode($response);

	$db_auth->close();
	$db_main->close();

?>
