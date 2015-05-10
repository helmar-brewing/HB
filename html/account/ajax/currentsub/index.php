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

	// get the stripe customer
	$cust = \Stripe\Customer::retrieve($user->stripeID);

	


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
