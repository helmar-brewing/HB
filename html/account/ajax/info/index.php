<?php

/* TEST FOR SUBMISSION */  if(empty($_GET)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

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
$step = $_GET['step'];
$data1 = $_GET['data1'];
//

$user = new phnx_user;
$user->checklogin(2);
$user->checksub();
if($user->login() === 2){
	if($step === '1'){
		$error = '0';
		$h1 = 'Update Info';
		$content = '
				<label for="change-info-firstname">First Name</label>
				<input type="text" id="change-info-firstname" value="'.$user->firstname.'">
				<label for="change-info-lastname">Last Name</label>
				<input type="text" id="change-info-lastname" value="'.$user->lastname.'">
				<label for="change-info-address">Address</label>
				<input type="text" id="change-info-address" value="" disabled>
				<label for="change-info-city">City</label>
				<input type="text" id="change-info-city" value="" disabled>
				<label for="change-info-state">State</label>
				<input type="text" id="change-info-state" value="" disabled>
			<fieldset>
				<label for="change-info-zip5">ZIP Code</label>
				<input type="text" id="change-info-zip5" value="" disabled> - <input type="text" id="change-info-zip4" value="" disabled>
			</fieldset>
			<button id="change-info-button">Update Info</button>
		';
	}elseif($step === '2'){

		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
		try{

			$data1['firstname'] = preg_replace('/[^0-9a-zA-Z\s]/', '', $data1['firstname']);
			$data1['lastname'] = preg_replace('/[^0-9a-zA-Z\s]/', '', $data1['lastname']);
			if($data1['firstname'] == '' || $data1['lastname'] == ''){
				throw new Exception('Please enter your first and last names.');
			}



			// if paper subscription validate mailing address
			// there will have ot be a step 3


			// do the update

			$stmt = $db_main->prepare("UPDATE users SET firstName=?, lastName=? WHERE username='".$user->username."' LIMIT 1");
			$stmt->bind_param("ss", $data1['firstname'], $data1['lastname']);
			$stmt->execute();
			$stmt->close;

			$error = '0';
			$content = '<p>Your name was successfully updated</p>';

			// pull this from the datbase instead  ***********
			$return = $data1;






		}catch(mysqli_sql_exception $e){
			$error = '1';
			$msg = '<li>There was an error updating your info [ref: data fail]</li>';
		}catch(Exception $e){
			$error = '1';
			$msg = '<li>'.$e->getMessage().'</li>';
		}
		mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
		if($error == '1'){
			$h1 = 'Update Info';
			$content = '
				<ul>'.$msg.'</ul>
				<fieldset>
					<label for="change-info-firstname">First Name</label>
					<input type="text" id="change-info-firstname" value="'.$data1['firstname'].'">
					<label for="change-info-lastname">Last Name</label>
					<input type="text" id="change-info-lastname" value="'.$data1['lastname'].'">
				</fieldset>
				<fieldset>
					<label for="change-info-address">Address</label>
					<input type="text" id="change-info-address" value="" disabled>
					<label for="change-info-city">City</label>
					<input type="text" id="change-info-city" value="" disabled>
					<label for="change-info-state">State</label>
					<input type="text" id="change-info-state" value="" disabled>
					<label for="change-info-zip">ZIP Code</label>
					<input type="text" id="change-info-zip" value="" disabled>
				</fieldset>
				<button id="change-info-button">Update Info</button>
			';
		}

	}
}else{
    $error = '1';
    $h1 = 'Error';
    $content = '<p>There was an error updating your account. Please refresh the page and try again.</p><p>(ref. auth fail)</p>';
}

$json = array(
	'error'     	=> $error,
	'h1'        	=> $h1,
	'content'   	=> $content,
	'return'		=> $return
);

$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();

?>
