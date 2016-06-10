<?php

/* TEST FOR SUBMISSION */  if(empty($_GET)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps($use_https);

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


// next mag date
$currentmonth = date('n');
$currentyear = date('Y');
if ($currentmonth == 1 || $currentmonth == 2 ){
	$dateReturn = 'March '.$currentyear;
} elseif ($currentmonth == 12 ){
	$dateReturn = 'March '.date('Y',strtotime('+1 year'));
} elseif ($currentmonth == 3 ||$currentmonth == 4 ||$currentmonth == 5 ){
	$dateReturn = 'June '.$currentyear;
} elseif ($currentmonth == 6 ||$currentmonth == 7 ||$currentmonth == 8 ){
	$dateReturn = 'September '.$currentyear;
} elseif ($currentmonth == 9 ||$currentmonth == 10 ||$currentmonth == 11 ){
	$dateReturn = 'December '.$currentyear;
} else{
	$dateReturn = 'Error! well, this isn\'t good! there isn\'t a 13th month!';
}









mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
try{

    // get the user and test for login
	$user = new phnx_user;
	$user->checklogin(2);
	if($user->login() !== 2){
        throw new AuthException('');
    }

	// test action
	$allowed_actions = array('subscribe', 'cancel');
	$action = preg_replace('[^a-z]', '', $_GET['action']);
	if(!in_array($action, $allowed_actions, true)){
		throw new Exception('invalid action x01');
	}

	// pull the current subscription
	$user->checksub('no-cache');
	if($user->subscription['status'] === 'error'){
		throw new Exception('sub pull fail');
	}

	// get the stripe customer
	$cust = \Stripe\Customer::retrieve($user->stripeID);


	// check address, then check for payment methods - else do the stuff
	if($user->address['address'] == '' && $action != 'cancel'){

		$error = '4';
		$h1 = 'Please Add Your Address';
		$html = '
			<div id="modal-address-form" class="modal-address">
			</div>
			<div id="modal-add-card-success">
				<p>Thank your for updating your address. Click button to complete your subscription.</p>
				<button id="modal-add-card-button" data-action="subscribe" >Subscribe to Digital + Paper Magazine</button>
			</div>
		';

	}elseif($cust['sources']['total_count'] === 0 && $action !== 'cancel'){

		$error = '3';
		$h1 = 'Please Add a Payment Method';
		$html = '
			<div id="modal-add-card">
				<p>Please add a credit card to your account.</p>
				<div class="credit-card-modal">
					<form action="" method="POST" id="payment-form-modal">
						<div class="payment-errors" id="payment-errors"></div>
						<label>Card Number</label>
						<input type="text" maxlength="30" id="card_number" data-stripe="number" value="" />
						<fieldset class="exp">
							<label>Expiration</label>
							<input type="text" placeholder="MM" maxlength="2" id="exp_month" data-stripe="exp-month" value=""/><span>/</span><input type="text" placeholder="YYYY" maxlength="4" id="exp_year" data-stripe="exp-year" value=""/>
						</fieldset>
						<fieldset class="cvc">
							<label>CVC</label>
							<input type="text" maxlength="4" id="cvc" data-stripe="cvc"/>
						</fieldset>
						<button class="add_update_card" type="submit">Add Card</button>
					</form>
				</div>
			</div>
			<div id="modal-add-card-success">
				<p>Thank your for adding your credit card. Click button to complete your subscription.</p>
				<button id="modal-add-card-button" data-action="subscribe" >Subscribe to Digital + Paper Magazine</button>
			</div>
		';

	}else{

		if($user->subscription['status'] === 'none'){

			switch($action){
				case 'cancel' : {
					$error = '0';
					$h1 = 'Subscription Status';
					$html = '
						<p>This is your current subscription.</p>
					';
					break;
				}
				case 'subscribe' : {
					$res = $cust->subscriptions->create(array("plan" => "helmar16"));
					$error = '0';
					$h1 = 'Subscription';
					$html ='
						<p>Thank you for subscribing to Digital + Paper Magazine. Based on today\'s date, you will receive your first paper magazine starting next quarter ('.$dateReturn.')</p>
						<p>Your credit card was charged $'.substr($res['plan']['amount'],0,-2).'.'.substr($res['plan']['amount'],-2).'</p>
						<p>Your subscription will renew on '.date("m/d/Y",$res['current_period_end']).'.</p>
					';
					break;
				}
				default : {
					throw new Exception('invalid action x01');
					break;
				}
			}

		}elseif(in_array($user->subscription['status'], array('active', 'trialing'))){

			switch($action){
				case 'cancel' : {
					$res   = $cust->subscriptions->retrieve($user->subscription['id'])->cancel(array('at_period_end' => true));
					$error = '0';
					$h1    = 'Subscription Status';
					$html  = '<p>Auto re-new has been disabled for you subscription.</p>';
					$html .= '<p>You can continue to enjoy your benefits until '.date("m-d-Y", $res['current_period_end']).'.</p>';
					break;
				}
				case 'subscribe' : {
					$subscription = $cust->subscriptions->retrieve($user->subscription['id']);
					if($user->subscription['cancel_at_period_end'] === true){
						$subscription->plan = 'helmar16';
						$subscription->prorate = FALSE;
						$subscription->save();
						$error = '0';
						$h1    = 'Subscription Status';
						$html  = '<p>Your subscription will renew.</p>';
						$html .= '<p>Your card has not been charged.</p>';
					}else{
						$error = '0';
						$h1    = 'Subscription Status';
						$html .= '<p>This is your current subscription.</p>';
					}
					break;
				}
				default : {
					throw new Exception('invalid action x02');
					break;
				}
			}

		}else{ throw new Exception('invalid status'); }
	}

}catch(\Stripe\Error\Card $e) {
	// Since it's a decline, \Stripe\Error\Card will be caught
	$body = $e->getJsonBody();
	$s_err  = $body['error'];


	$error  = '1';
    $h1     = 'Error';
	$html   = '<p>There was an error.</p><p>(ref: stripe x00)</p><p>Please try again.</p>';
	$msg	= $e->getMessage();



}catch (\Stripe\Error\InvalidRequest $e) {
	// Invalid parameters were supplied to Stripe's API
	$body = $e->getJsonBody();
	$s_err  = $body['error'];




	$error  = '1';
    $h1     = 'Error';
	$html   = '<p>There was an error.</p><p>(ref: stripe x01)</p><p>Please try again.</p>';
	$msg	= $e->getMessage();



}catch (\Stripe\Error\Authentication $e) {
	// Authentication with Stripe's API failed (maybe you changed API keys recently)
	$error  = '1';
    $h1     = 'Error';
	$html   = '<p>There was an error.</p><p>(ref: stripe x02)</p><p>Please try again.</p>';
	$msg	= $e->getMessage();
}catch (\Stripe\Error\ApiConnection $e) {
	// Network communication with Stripe failed
	$error  = '1';
    $h1     = 'Error';
	$html   = '<p>There was an error.</p><p>(ref: stripe x02)</p><p>Please try again.</p>';
	$msg	= $e->getMessage();
}catch (\Stripe\Error\Base $e) {
	// Display a very generic error to the user, and maybe send yourself an email
	$error  = '1';
    $h1     = 'Error';
	$html   = '<p>There was an error.</p><p>(ref: stripe x04)</p><p>Please try again.</p>';
	$msg	= $e->getMessage();
}catch(mysqli_sql_exception $e){
    $error  = '1';
    $h1     = 'Error';
    $html   = '<p>There was an error.</p><p>(ref: '.$e->getMessage().')</p><p>Please try again.</p>';
}catch(AuthException $e){
    $error = '2';
}catch(Exception $e){
    $error  = '1';
    $h1     = 'Error';
    $html   = '<p>There was an error.</p><p>(ref: '.$e->getMessage().')</p><p>Please try again.</p>';
}
mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries

$json = array(
    'error'     => $error,
    'h1'        => $h1,
    'content'   => $html,
	'msg'		=> $msg,
);

$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();
?>
