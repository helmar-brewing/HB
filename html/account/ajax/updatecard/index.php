<?php

/* TEST FOR SUBMISSION */  if(empty($_POST)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

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
require_once('libraries/stripe/Stripe.php');

/* PAGE VARIABLES */
$token = $_POST['t'];


$user = new phnx_user;
$user->checklogin(2);
if($user->login() === 2){



	$R_userdeets = $db_main->query("SELECT * FROM users WHERE userid = ".$user->id." LIMIT 1");
	if($R_userdeets !== FALSE){
		$userdeets = $R_userdeets->fetch_assoc();
		$R_userdeets->free();

		Stripe::setApiKey($apikey['stripe']['secret']);

		try {

			$cust = Stripe_Customer::retrieve($userdeets['stripeID']);

			if($cust['cards']['total_count'] === 0){
				$card_info = $cust->cards->create(array("card" => $token));
				$json = array(
					'error' => '0',
					'msg' => 'Your card has been successfully added.',
					'last4' => $card_info['last4'],
					'brand' => $card_info['brand'],
					'exp_month' => $card_info['exp_month'],
					'exp_year' => $card_info['exp_year']
				);
			}else{
				$card_id_array = $cust->cards->data;
				$card_id = $card_id_array[0]['id'];
				$card_info = $cust->cards->create(array("card" => $token));
				$cust->cards->retrieve($card_id)->delete();
				$json = array(
					'error' => '0',
					'msg' => 'Your card has been successfully updated.',
					'last4' => $card_info['last4'],
					'brand' => $card_info['brand'],
					'exp_month' => $card_info['exp_month'],
					'exp_year' => $card_info['exp_year']
				);
			}

		} catch(Stripe_CardError $e) {
			// Since it's a decline, Stripe_CardError will be caught
			$json = array(
				'error' => '1',
				'msg' =>  'There was an error updating your card. (ref: stripe card error exception)'
			);
		} catch (Stripe_InvalidRequestError $e) {
			// Invalid parameters were supplied to Stripe's API
			$json = array(
				'error' => '3',
				'json' => $e->getJsonBody()
			);
		} catch (Stripe_AuthenticationError $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			$json = array(
				'error' => '3',
				'json' => $e->getJsonBody()
			);
		} catch (Stripe_ApiConnectionError $e) {
			// Network communication with Stripe failed
			$json = array(
				'error' => '3',
				'json' => $e->getJsonBody()
			);
		} catch (Stripe_Error $e) {
			// Display a very generic error to the user, and maybe send yourself an email
			$json = array(
				'error' => '3',
				'json' => $e->getJsonBody()
			);
		} catch (Exception $e) {
			// Something else happened, completely unrelated to Stripe
			$json = array(
				'error' => '3',
				'json' => $e->getJsonBody()
			);
		}

	}else{
		$json = array(
			'error' => '1',
			'msg' => 'There was an error updating your card. (ref: user pull fail)'
		);
	}


}else{
	$json = array(
		'error' => '2',
		'msg' => 'You must be logged in to make changes to your card.  Please refresh the page and try again.'
	);
}


$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();

?>
