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
	$page = (isset($_GET['page'])) ? preg_replace('[^a-z]', '', $_GET['page']) : NULL;

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




	if($user->subscription['status'] === 'none'){

		switch($action){
			case 'cancel' : {
				$error = '0';
				$h1    = 'Subscription Status';
				$html  = '<p>This is your current subscription.</p>';
				$html .= '<p>You are currently enjoying the free version of the website.</p>';
				break;
			}
			case 'subscribe' : {
				$error = '0';
				$h1    = 'Subscribe';
				$html  = '<p>You are signing up for <span>Digital + Paper Magazine</span>. Based on today\'s date, you will receive your first paper magazine starting next quarter ('.$dateReturn.')</p>';
				$html .= '<p>You will pay for the full year subscription that will renew on '.date( "m/d/Y", (time() + 31536000) ).'.</p>';
				$html .= '<button onclick="subUpdate(\''.$action.'\')">Subscribe</button>';
				break;
			}
			default : {
				throw new Exception('invalid action x02');
				break;
			}
		}

	}elseif(in_array($user->subscription['status'], array('active', 'trialing'))){

		switch($action){
			case 'cancel' : {
				$error = '0';
				$h1    = 'Subscription Status';
				if($user->subscription['cancel_at_period_end'] === TRUE){
					$html  = '<p><i class="fa fa-ban"></i> Auto re-new is turned off. Your subscription will be canceled on <span>'.date('M j Y', $user->subscription['current_period_end']).'</span>.</p>';
				}else{
					$html  = '<p>You are about to disable auto-renew for your <span>Digital + Paper Magazine</span> subscription.</p>';
					$html .= '<p>You can continue to enjoy your benefits until <span>'.date('m/d/Y', $user->subscription['current_period_end']).'</span>.</p>';
					$html .= '<button onclick="subUpdate(\'cancel\')">Disable Auto-Renew</button>';
				}
				break;
			}
			case 'subscribe' : {
				$error = '0';
				$h1    = 'Subscription Status';
				$subscription = $cust->subscriptions->retrieve($user->subscription['id']);
				if($user->subscription['cancel_at_period_end'] === TRUE){
					$html  = '<p>You are about to enable auto-renew for your <span>Digital + Paper Magazine</span> subscription.</p>';
					$html .= '<button onclick="subUpdate(\'subscribe\')">Enable Auto-Renew</button>';
				}else{
					$payment = $user->subscription['next_payment'] / 100;
					$html .= '<p>This is your current subscription.</p>';
					$html .= '<p>Your subscription will renew on <span>'.date('M j Y', $user->subscription['current_period_end']).'</span>.</p>';
					$html .= '<p>Next Payment: $'.$payment.' on '.date('M j Y', $user->subscription['current_period_end']).'.</p>';
				}
				break;
			}
			default : {
				throw new Exception('invalid action x02');
				break;
			}
		}

	}else{ throw new Exception('invalid status'); }




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
