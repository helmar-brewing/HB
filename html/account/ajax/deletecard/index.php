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
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);

/* PAGE VARIABLES */
//

$user = new phnx_user;
$user->checklogin(2);
if($user->login() === 2){





		try {

			$cust = \Stripe\Customer::retrieve($user->stripeID);

			if($cust['sources']['total_count'] === 0){

				$json = array(
					'error' => '1',
					'msg' => 'Could not find a card on file. Please refresh page.',
				);
			}else{
				$card_id_array = $cust->sources->data;
				$card_id = $card_id_array[0]['id'];
				$cust->sources->retrieve($card_id)->delete();
				$json = array(
					'error' => '0',
					'msg' => 'Your card has been successfully deleted.',
				);
			}

		}catch(\Stripe\Error\Card $e) {
			// Since it's a decline, Stripe_CardError will be caught
			$json = array(
				'error' => '1',
				'msg' =>  'There was an error deleting your card. (ref: stripe card error exception)'
			);
		}catch (\Stripe\Error\InvalidRequest $e) {
			// Invalid parameters were supplied to Stripe's API
			$json = array(
				'error' => '3',
				'json' => $e->getJsonBody()
			);
		}catch (\Stripe\Error\Authentication $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			$json = array(
				'error' => '3',
				'json' => $e->getJsonBody()
			);
		}catch (\Stripe\Error\ApiConnection $e) {
			// Network communication with Stripe failed
			$json = array(
				'error' => '3',
				'json' => $e->getJsonBody()
			);
		}catch (\Stripe\Error\Base $e) {
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
