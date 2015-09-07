<?php

/* TEST FOR SUBMISSION */  if(empty($_GET)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

class AuthException extends Exception{}

ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps($use_https);

/* WHICH DATABASES DO WE NEED */
$db2use = array(
	'db_auth' 	=> TRUE,
	'db_main'	=> TRUE
);

/* GET KEYS TO SITE */ require($path_to_keys);


// date formulas
date_default_timezone_set('US/Eastern');

 $currentmonth = date('n');
 $currentyear = date('Y');

if ($currentmonth == 1 ||$currentmonth == 2 ){
	$dateReturn = 'March '.$currentyear;
} elseif ($currentmonth == 12 ){
	$dateReturn = 'March '.$currentyear+1;
} elseif ($currentmonth == 3 ||$currentmonth == 4 ||$currentmonth == 5 ){
	$dateReturn = 'June '.$currentyear;
} elseif ($currentmonth == 6 ||$currentmonth == 7 ||$currentmonth == 8 ){
	$dateReturn = 'September '.$currentyear;
} elseif ($currentmonth == 9 ||$currentmonth == 10 ||$currentmonth == 11 ){
	$dateReturn = 'December '.$currentyear;
} else{
	$dateReturn = 'Error! well, this isn\'t good! there isn\'t a 13th month!';
}

/* LOAD FUNC-CLASS-LIB */
require_once('classes/phnx-user.class.php');
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);

/* PAGE FUNCTIONS */
function downgrade($to = NULL, $from = NULL){
	if( $to === NULL || $from === NULL ){ throw new Exception('no plan set for downgrade.'); }
	global $cust, $user;
	$subscription = $cust->subscriptions->retrieve($user->subscription['sub_id']);
	$meta = $subscription->metadata->__toArray();
	$subscription->plan = $to;
	$subscription->prorate = FALSE;
	if($meta['downgrade'] !== 'yes'){
		$subscription->metadata = array(
			'downgrade' => 'yes',
			'downgrade_from' => $from,
			'downgrade_date' => $user->subscription['current_period_end'],
			'downgrade_paid' => $user->subscription['next_payment']
		);
	}
	$res = $subscription->save();
	$html = array(
		'sub-digital' => '
			<p>You have changed your subscription to <span>Digital Magazine</span>. You will receive these benefits on your next renewal date of '.date("m-d-Y", $res['current_period_end']).'. Your credit card will not be charged at this time.</p>
			<p>You can continue to enjoy your current benefits until '.date("m-d-Y", $res['current_period_end']).'.</p>
		',
		'sub-paper' => '
			<p>You have changed your subscription to <span>Paper Magazine</span>. You will receive these benefits on your next renewal date of '.date("m-d-Y", $res['current_period_end']).'. Your credit card will not be charged at this time.</p>
			<p>You can continue to enjoy your current benefits until '.date("m-d-Y", $res['current_period_end']).'.</p>
		',
	);
	return $html[$to];
}

function upgrade($to = NULL){
	if( $to === NULL ){ throw new Exception('no plan set for upgrade.'); }
	global $cust, $user;
	if( date("L", $user->subscription['current_period_end']) === '1' || date("L", time()) === '1' ){
		$year = 60*60*24*366;
	}else{
		$year = 60*60*24*365;
	}
	$diff = ( $user->subscription['current_period_end'] - time() ) / ($year);
	$bal = floor( 0 - ( $diff * $user->subscription['last_paid'] ) );
	$cust->subscriptions->retrieve($user->subscription['sub_id'])->cancel();
	$cust->account_balance = $bal;
	$cust->save();
	$res = $cust->subscriptions->create(array("plan" => $to));
	$charge = $res['plan']['amount'] + $bal;
	$html = array(
		'sub-paper' => '
			<p>Thank you for upgrading to Paper Magazine. Your benefits will take effect immediately. Based on today\'s
			date, you will receive your new subscription\'s paper magazine in the next quarter, '.$dateReturn.'.</p>
			<p>Your credit card was charged $'.substr($charge,0,-2).'.'.substr($charge,-2).'</p>
			<p>Your subscription will renew on '.date("m/d/Y",$res['current_period_end']).'.</p>
		',
		'sub-digital+paper' => '
			<p>Thank you for upgrading to Digital + Paper Magazine. Your benefits will take effect immediately. Based on today\'s
			date, you will receive your new subscription\'s paper magazine in the next quarter, '.$dateReturn.'.</p>
			<p>Your credit card was charged $'.substr($charge,0,-2).'.'.substr($charge,-2).'</p>
			<p>Your subscription will renew on '.date("m/d/Y",$res['current_period_end']).'.</p>
		'
	);
	return $html[$to];
}
function cancel(){
	global $cust, $user;
	$res = $cust->subscriptions->retrieve($user->subscription['sub_id'])->cancel(array('at_period_end' => true));
	$html = '
		<p>Auto re-new has been disabled for you subscription.</p>
		<p>You can continue to enjoy your benefits until '.date("m-d-Y", $res['current_period_end']).'.</p>
	';
	return $html;
}
function renew($to){
	global $cust, $user;
	$subscription = $cust->subscriptions->retrieve($user->subscription['sub_id']);
	if($user->subscription['cancel_at_period_end'] === true){
		$subscription->plan = $to;
		$subscription->prorate = FALSE;
		$subscription->save();
		$h .= '<p>Your subscription will renew.</p>';
		$h .= '<p>Your card has not been charged.</p>';
	}else{
		$meta = $subscription->metadata->__toArray();
		if($meta['downgrade'] === 'yes'){
			$subscription->plan = $to;
			$subscription->prorate = FALSE;
			if($meta['downgrade'] === 'yes'){
				$subscription->metadata = array(
					'downgrade' => null,
					'downgrade_from' => null,
					'downgrade_date' => null,
					'downgrade_paid' => null
				);
			}
			$subscription->save();
			$h .= '<p>Your subscription will renew.</p>';
			$h .= '<p>Your card has not been charged.</p>';
		}else{
			$h .= '<p>This is your current subscription.</p>';
		}
	}
	$html = array(
		'sub-digital' => $h,
		'sub-paper' => $h,
		'sub-digital+paper' => $h
	);
	return $html[$to];
}











mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
try{

    // get the user and test for login
	$user = new phnx_user;
	$user->checklogin(1);
	if($user->login() !== 1){
        throw new AuthException('');
    }

	// test action
    $allowed_actions = array('none', 'paper','digital','digitalpaper');
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
	if($user->address['address'] == '' && $action != 'none'){

		$error = '4';
		$h1 = 'Please Add Your Address';
		$html = '
			<div id="modal-address-form" class="modal-address">
				<label for="sub-address">Address</label>
				<input type="text" id="sub-address" value="'.$data1['address'].'" >
				<label for="sub-city">City</label>
				<input type="text" id="sub-city" value="'.$data1['city'].'" >
				<label for="sub-state">State</label>
				<input type="text" id="sub-state" value="'.$data1['state'].'" >
				<fieldset class="zip">
					<label for="sub-zip5">ZIP Code</label>
					<input type="text" id="sub-zip5" placeholder="zip code" maxlength="5" value="'.$data1['zip5'].'" > - <input type="text" id="sub-zip4" placeholder="+4" maxlength="4" value="'.$data1['zip4'].'" >
				</fieldset>
				<button onclick="address2()">Update Address</button>
			</div>
		';
		switch($action){

			case 'paper':
				$html .= '
					<div id="modal-add-card-success">
						<p>Thank your for updating your address. Click button to complete your subscription.</p>
						<button id="modal-add-card-button" data-action="paper" >Subscribe to Paper Magazine</button>
					</div>
				';
				break;

			case 'digital':
				$html .= '
					<div id="modal-add-card-success">
						<p>Thank your for updating your address. Click button to complete your subscription.</p>
						<button id="modal-add-card-button" data-action="digital" >Subscribe to Digital Magazine</button>
					</div>
				';
				break;

			case 'digitalpaper':
				$html .= '
					<div id="modal-add-card-success">
						<p>Thank your for updating your address. Click button to complete your subscription.</p>
						<button id="modal-add-card-button" data-action="digitalpaper" >Subscribe to Digital + Paper Magazine</button>
					</div>
				';
				break;

			default:
				$html .= '
					<div id="modal-add-card-success">
						<p>Thank your for updating your address.</p>
						<p>Please close this window and try your subscription again. (ref:invalid action)</p>
					</div>
				';
				break;

		}

	}elseif($cust['sources']['total_count'] === 0 && $action !== 'none'){

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
		';

		switch($action){

			case 'paper':
				$html .= '
					<div id="modal-add-card-success">
						<p>Thank your for adding your credit card. Click button to complete your subscription.</p>
						<button id="modal-add-card-button" data-action="paper" >Subscribe to Paper Magazine</button>
					</div>
				';
				break;

			case 'digital':
				$html .= '
					<div id="modal-add-card-success">
						<p>Thank your for adding your credit card. Click button to complete your subscription.</p>
						<button id="modal-add-card-button" data-action="digital" >Subscribe to Digital Magazine</button>
					</div>
				';
				break;

			case 'digitalpaper':
				$html .= '
					<div id="modal-add-card-success">
						<p>Thank your for adding your credit card. Click button to complete your subscription.</p>
						<button id="modal-add-card-button" data-action="digitalpaper" >Subscribe to Digital + Paper Magazine</button>
					</div>
				';
				break;

			default:
				$html .= '
					<div id="modal-add-card-success">
						<p>Thank your for adding your credit card.</p>
						<p>Please close this window and try your subscription again. (ref:invalid action)</p>
					</div>
				';
				break;

		}


	}else{

		if($user->subscription['status'] === 'none'){

			switch($action){

				case 'none':
					$error = '0';
					$h1 = 'Subscription Status';
					$html = '
						<p>This is your current subscription.</p>
					';
					break;

				case 'digital':
					$res = $cust->subscriptions->create(array("plan" => "sub-digital"));
					$error = '0';
					$h1 = 'Subscription';
					$html ='
						<p>Thank you for subscribing to Digital Magazine</p>
						<p>Your credit card was charged $'.substr($res['plan']['amount'],0,-2).'.'.substr($res['plan']['amount'],-2).'</p>
						<p>Your subscription will renew on '.date("m/d/Y",$res['current_period_end']).'.</p>
					';
					break;

				case 'paper':
					$res = $cust->subscriptions->create(array("plan" => "sub-paper"));
					$error = '0';
					$h1 = 'Subscription';
					$html ='
						<p>Thank you for subscribing to Paper Magazine</p>
						<p>Your credit card was charged $'.substr($res['plan']['amount'],0,-2).'.'.substr($res['plan']['amount'],-2).'</p>
						<p>Your subscription will renew on '.date("m/d/Y",$res['current_period_end']).'.</p>
					';
					break;

				case 'digitalpaper':
					$res = $cust->subscriptions->create(array("plan" => "sub-digital+paper"));
					$error = '0';
					$h1 = 'Subscription';
					$html ='
						<p>Thank you for subscribing to Digital + Paper Magazine</p>
						<p>Your credit card was charged $'.substr($res['plan']['amount'],0,-2).'.'.substr($res['plan']['amount'],-2).'</p>
						<p>Your subscription will renew on '.date("m/d/Y",$res['current_period_end']).'.</p>
					';
					break;

				default:
					throw new Exception('invalid action x02');
					break;

			}

		}elseif($user->subscription['status'] === 'active' && $user->subscription['plan_type'] === 'sub-digital'){
			switch($action){
				case 'none':
					$html = cancel();
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				case 'digital':
					$html = renew('sub-digital');
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				case 'paper':
					$html = upgrade('sub-paper');
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				case 'digitalpaper':
					$html = upgrade('sub-digital+paper');
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				default:
					throw new Exception('invalid action x02');
					break;
			}
		}elseif($user->subscription['status'] === 'active' && $user->subscription['plan_type'] === 'sub-paper'){
			switch($action){
				case 'none':
					$html = cancel();
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				case 'digital':
					$html = downgrade('sub-digital', 'sub-paper');
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				case 'paper':
					$html = renew('sub-paper');
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				case 'digitalpaper':
					$html = upgrade('sub-digital+paper');
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				default:
					throw new Exception('invalid action x02');
					break;
			}
		}elseif($user->subscription['status'] === 'active' && $user->subscription['plan_type'] === 'sub-digital+paper'){
			switch($action){
				case 'none':
					$html = cancel();
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				case 'digital':
					$html = downgrade('sub-digital', 'sub-digital+paper');
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				case 'paper':
					$html = downgrade('sub-paper', 'sub-digital+paper');
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				case 'digitalpaper':
					$html = renew('sub-digital+paper');
					$error = '0';
					$h1 = 'Subscription Status';
					break;
				default:
					throw new Exception('invalid action x02');
					break;
			}
		}else{
			throw new Exception('no plan type');
		}

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
