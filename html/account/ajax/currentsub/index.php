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


$user = new phnx_user;
$user->checklogin(1);





mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
try{

    // test for login
	if($user->login() !== 1){
        throw new AuthException('');
    }



	// pull the current subscription
	$user->checksub('no-cache');
	if($user->subscription['status'] === 'error'){
		throw new Exception('sub pull fail');
	}



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
	function subAction($sub){
		switch($sub){
			case 'sub-digital':
				return 'digital';
				break;
			case 'sub-paper':
				return 'paper';
				break;
			case 'sub-digital+paper':
				return 'digitalpaper';
				break;
			default: throw new Exception('Cannot get subscription action, invalid plan type.');
		}
	}
	switch($user->subscription[status]){
		case 'error':
			$html = '<p>There was an error determining you subscription status.</p>';
			break;
		case 'none':
			$html = '<p>You do not have an active subscription.</p>';
			break;
		case 'active':
			$html = '<p>You are currently subscribed to <span>'.subName($user->subscription['plan_type']).'</span></p>';
			if($user->subscription['cancel_at_period_end'] === true){
				$html .= '<p><i class="fa fa-ban"></i> Auto re-new is turned off. Your subscription will be canceled on <span>'.date('M j Y', $user->subscription['current_period_end']).'</span>.</p>';
				$html .= '<button onclick="sub(\''.subAction($user->subscription['plan_type']).'\')">Resume Subscription</button>';
			}else{
				if($user->subscription['plan_type'] === $user->subscription['next_plan']){
					$html .= '<p><i class="fa fa-refresh"></i> Your subscription will renew on <span>'.date('M j Y', $user->subscription['current_period_end']).'</span>.</p>';
					$html .= '<p>Next Payment: $'.substr($user->subscription['next_payment'],0,-2).'.'.substr($user->subscription['next_payment'],-2).' on '.date('M j Y', $user->subscription['current_period_end']).'.</p>';
				}else{
					$html .= '<p><i class="fa fa-level-down"></i> You have elected to change your subscription to <span>'.subName($user->subscription['next_plan']).'</span> effective on <span>'.date('M j Y', $user->subscription['current_period_end']).'</span>.</p>';
					$html .= '<p>Next Payment: $'.substr($user->subscription['next_payment'],0,-2).'.'.substr($user->subscription['next_payment'],-2).' on '.date('M j Y', $user->subscription['current_period_end']).'.</p>';
				}
			}
			break;
		default:
			$html = '<p>There was an error determining you subscription status.</p>';
			break;
	}











}catch(\Stripe\Error\Card $e) {
	// Since it's a decline, \Stripe\Error\Card will be caught
	$body = $e->getJsonBody();
	$s_err  = $body['error'];


	$error  = '1';
	$html   = '<p>There was an error.</p><p>(ref: stripe x00)</p><p>Please try again.</p>';
	$msg	= $e->getMessage();



}catch (\Stripe\Error\InvalidRequest $e) {
	// Invalid parameters were supplied to Stripe's API
	$body = $e->getJsonBody();
	$s_err  = $body['error'];




	$error  = '1';
	$html   = '<p>There was an error.</p><p>(ref: stripe x01)</p><p>Please try again.</p>';
	$msg	= $e->getMessage();



}catch (\Stripe\Error\Authentication $e) {
	// Authentication with Stripe's API failed (maybe you changed API keys recently)
	$error  = '1';
	$html   = '<p>There was an error.</p><p>(ref: stripe x02)</p><p>Please try again.</p>';
	$msg	= $e->getMessage();
}catch (\Stripe\Error\ApiConnection $e) {
	// Network communication with Stripe failed
	$error  = '1';
	$html   = '<p>There was an error.</p><p>(ref: stripe x02)</p><p>Please try again.</p>';
	$msg	= $e->getMessage();
}catch (\Stripe\Error\Base $e) {
	// Display a very generic error to the user, and maybe send yourself an email
	$error  = '1';
	$html   = '<p>There was an error.</p><p>(ref: stripe x04)</p><p>Please try again.</p>';
	$msg	= $e->getMessage();
}catch(mysqli_sql_exception $e){
    $error  = '1';
    $html   = '<p>There was an error.</p><p>(ref: '.$e->getMessage().')</p><p>Please try again.</p>';
}catch(AuthException $e){
    $error = '2';
}catch(Exception $e){
    $error  = '1';
    $html   = '<p>There was an error.</p><p>(ref: '.$e->getMessage().')</p><p>Please try again.</p>';
}
mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries

$json = array(
    'error'     => $error,
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
