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
//

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

				$json = array(
					'error' => '1',
					'msg' => 'Could not find a card on file. You must have an active card to subscribe.',
				);
			}else{
				$sub_response = $cust->subscriptions->all();
				$subs = $sub_response->data;

				if(empty($subs)){

					$new_sub = $cust->subscriptions->create(array("plan" => "sub-gold"));
					$status = $new_sub['status'];
					$sub_button_text = 'Cancel';

				}else{

					if($subs[0]['cancel_at_period_end'] === true){
						$sub_id = $subs[0]['id'];

						$subscription = $cust->subscriptions->retrieve($sub_id);
						$subscription->plan = "sub-gold";
						$subscription->save();

						$status = 'active';
						$sub_button_text = 'Cancel';
					}else{

						$sub_id = $subs[0]['id'];
						$cust->subscriptions->retrieve($sub_id)->cancel([at_period_end=>true]);

						$sub_button_text = 'Resume Subscription';

						$new_sub_response = $cust->subscriptions->all();
						$new_subs = $sub_response->data;
						$status = 'active - Your subscription is paid in full until '.date('M j Y', $new_subs[0]['current_period_end']).' at which point it will be canceled.';

					}

				}
				$json = array(
					'error' => '0',
					'status' => $status,
					'button' => $sub_button_text,
					'msg' => ''
				);

			}

		} catch(Stripe_CardError $e) {
			// Since it's a decline, Stripe_CardError will be caught
			$json = array(
				'error' => '1',
				'msg' =>  'There was an error updating your subscription. (ref: stripe card error exception)'
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
			'msg' => 'There was an error updating your subscription. (ref: user pull fail)'
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
