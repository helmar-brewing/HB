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


/* PAGE FUNCTIONS */





/* DO IT! */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
try{

    // get the user and test for login
	$user = new phnx_user;
	$user->checklogin(2);
	if($user->login() !== 2){
        throw new AuthException('');
    }


    // address validation using USPS...

    $address = $_GET['address'];
    $city = $_GET['city'];
    $state = $_GET['state'];
    $zip5 = $_GET['zip5'];
    $zip4 = $_GET['zip4'];

    // upload address
    $stmt = $db_main->prepare("UPDATE users SET address=?, city=?, state=?, zip5=?, zip4=? WHERE username='".$user->username."' LIMIT 1");
    $stmt->bind_param("sssss", $address, $city, $state, $zip5, $zip4);
    $stmt->execute();
    $stmt->close;

    $error = '0';

	$return['fulladdress'] = $address.'<br>'.$city.' '.$state.' '.$zip5.'-'.$zip4;



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
	'return'	=> $return
);

$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();
?>
