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

/* PAGE FUNCTIONS */
function subName($sub){
	switch($sub){
		case 'sub-digital':
			return 'Digital Magazine';
			break;
		case 'sub-paper':
			return 'Paper Magazine';
			break;
		case 'sub-digital+paper':
			return 'Digital + Paper Magazine';
			break;
		default: throw new Exception('Cannot get subscription name, invalid plan type.');
	}
}
function downgrade($to = NULL){
	global $user;
	global $action;
	if( $to === NULL ){ throw new Exception('no plan set for downgrade.'); }
	$html = array(
		'sub-digital' => '
			<p>You are changing your subscription from '.subName($user->subscription['plan_type']).' to <span>Digital Magazine</span>.</p>
			<p>You will continue to have your current benefits until <span>'.date('m/d/Y', $user->subscription['current_period_end']).'</span>. Your new subscription benefits will be available on your renewal date of '.date('M j Y', $user->subscription['current_period_end']).'.</p>
			<p>Your credit card will not be charged at this time.</p>
			<button onclick="subUpdate(\''.$action.'\')">Change Subscription</button>
		',
		'sub-paper' => '
			<p>You are changing your subscription from <span>'.subName($user->subscription['plan_type']).'</span> to <span>Paper Magazine</span>.</p>
			<p>You will continue to have your current benefits until <span>'.date('m/d/Y', $user->subscription['current_period_end']).'</span>. Your new subscription benefits will be available on your renewal date of '.date('M j Y', $user->subscription['current_period_end']).'.</p>
			<p>Your credit card will not be charged at this time.</p>
			<button onclick="subUpdate(\''.$action.'\')">Change Subscription</button>
		',
	);
	return $html[$to];
}

function upgrade($to = NULL){
	if( $to === NULL ){ throw new Exception('no plan set for upgrade.'); }
	global $cust, $user;
	global $action;
	if( date("L", $user->subscription['current_period_end']) === '1' || date("L", time()) === '1' ){
		$year = 60*60*24*366;
	}else{
		$year = 60*60*24*365;
	}
	$diff = ( $user->subscription['current_period_end'] - time() ) / ($year);
	$bal = floor( 0 - ( $diff * $user->subscription['last_paid'] ) );
	$plan = \Stripe\Plan::retrieve($to);
	$charge = $plan['amount'] + $bal;
	$html = array(
		'sub-paper' => '
			<p>You are upgrading your subscription to <span>Paper Magazine</span>.</p>
			<p>New benefits will be available immediately, and your renewal date will be extended to one year from today.
			Based on today\'s date, you will receive your first paper magazine starting next quarter ('.$dateReturn.')</p>
			<p>Your credit card will be charged $'.substr($charge,0,-2).'.'.substr($charge,-2).'</p>
			<button onclick="subUpdate(\''.$action.'\')">Change Subscription</button>
		',
		'sub-digital+paper' => '
			<p>You are upgrading your subscription to <span>Digital + Paper Magazine</span>.</p>
			<p>New benefits will be available immediately, and your renewal date will be extended to one year from today.
			Based on today\'s date, you will receive your first paper magazine starting next quarter ('.$dateReturn.')</p>
			<p>Your credit card will be charged $'.substr($charge,0,-2).'.'.substr($charge,-2).'</p>
			<button onclick="subUpdate(\''.$action.'\')">Change Subscription</button>
		'
	);
	return $html[$to];
}
function cancel(){
	global $user;
	global $action;
	$html = '
		<p>You are about to disable auto-renew for your <span>'.subName($user->subscription['plan_type']).'</span> subscription.</p>
		<p>You can continue to enjoy your benefits until <span>'.date('m/d/Y', $user->subscription['current_period_end']).'</span>.</p>
		<button onclick="subUpdate(\''.$action.'\')">Disable Auto-Renew</button>
	';
	return $html;
}
function renew($to){
	global $cust, $user;
	global $action;
	$subscription = $cust->subscriptions->retrieve($user->subscription['sub_id']);
	if($user->subscription['cancel_at_period_end'] === true){
		$h .= '<p>You are about to enable auto-renew for your <span>'.subName($user->subscription['plan_type']).'</span> subscription.</p>';
		$h .= '<button onclick="subUpdate(\''.$action.'\')">Enable Auto-Renew</button>';
	}else{
		$meta = $subscription->metadata->__toArray();
		if($meta['downgrade'] === 'yes'){
			$h .= '<p>You are switching your subscription back to <span>'.subName($user->subscription['plan_type']).'</span>.</p>';
			$h .= '<p>Your subscription will renew on <span>'.date('m/d/Y', $user->subscription['current_period_end']).'</span>.</p>';
			$h .= '<p>Your credit card will not be charged at this time.</p>';
			$h .= '<button onclick="subUpdate(\''.$action.'\')">Change Subscription</button>';
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
	$user->checklogin(2);
	if($user->login() !== 2){
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
				$error = '0';
				$h1 = 'Subscription';
				$html ='
					<p>You are signing up for <span>Digital Magazine</span>.</p>
					<p>You will pay for the full year subscription that will renew on '.date( "m/d/Y", (time() + 31536000) ).'.</p>
					<button onclick="subUpdate(\''.$action.'\')">Subscribe</button>
				';
				break;

			case 'paper':
				$error = '0';
				$h1 = 'Subscription';
				$html ='
					<p>You are signing up for <span>Paper Magazine</span>. Based on today\'s date, you will receive your first paper magazine starting next quarter ('.$dateReturn.')</p>
					<p>You will pay for the full year subscription that will renew on '.date( "m/d/Y", (time() + 31536000) ).'.</p>
					<button onclick="subUpdate(\''.$action.'\')">Subscribe</button>
				';
				break;

			case 'digitalpaper':
				$error = '0';
				$h1 = 'Subscription';
				$html ='
					<p>You are signing up for <span>Digital + Paper Magazine</span>. Based on today\'s date, you will receive your first paper magazine starting next quarter ('.$dateReturn.')</p>
					<p>You will pay for the full year subscription that will renew on '.date( "m/d/Y", (time() + 31536000) ).'.</p>
					<button onclick="subUpdate(\''.$action.'\')">Subscribe</button>
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

	$s_err_s = print_r($s_err,true);


	$error  = '1';
    $h1     = 'Error';
	$html   = '<p>There was an error.</p><p>(ref: stripe x01)</p><p>Please try again.</p>'.$s_err_s;
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
