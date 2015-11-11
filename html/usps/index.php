<?php


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
require_once('libraries/usps/USPSAddressVerify.php');
$usps = new USPSAddressVerify($apikey['usps']['username']);

/* PAGE VARIABLES */
$currentpage = '';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);


// Create new address object and assign the properties apartently the order you assign them is important so make sure to set them as the example below
$address = new USPSAddress;
$address->setFirmName('');
$address->setApt('');
$address->setAddress('');
$address->setCity('');
$address->setState('');
$address->setZip5('');
$address->setZip4('');


// Add the address object to the address verify class
$usps->addAddress($address);


// Perform the request and return result
$usps->verify();

print_r($usps->getArrayResponse());
print '<hr>';
var_dump($usps->isError());
print '<hr>';

// See if it was successful
if($usps->isSuccess()) {
  echo 'Done';
} else {
  echo 'Error: ' . $usps->getErrorMessage();
}





$db_auth->close();
$db_main->close();
?>
