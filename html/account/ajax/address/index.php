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
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);
require_once('libraries/usps/USPSAddressVerify.php');
$usps = new USPSAddressVerify($apikey['usps']['username']);
require_once('classes/phnx-user.class.php');
$user = new phnx_user;

$user->checklogin(2);
$user->checksub();


/* PAGE VARIABLES */
$h1 = null;
$html = null;
$return = null;
if(isset($_GET['step'])){
	$step = $_GET['step'];
}else{
	$step = null;
}
$data1 = $_GET['data1'];
$data2 = $_GET['data2'];




// function to build the form
function addressForm($source){

	switch(gettype($source)){
		case 'object':
			$s = $source->address;
			$s['firstname'] = $source->firstname;
			$s['lastname'] = $source->lastname;
			break;

		case 'array':
			if(isset($source['Address2'])){
				global $user;
				$s['firstname'] = $user->firstname;
				$s['lastname'] = $user->lastname;
				$s['firmname'] = $source['FirmName'];
				$s['address'] = $source['Address2'];
				$s['unit'] = $source['Address1'];
				$s['city'] = $source['City'];
				$s['state'] = $source['State'];
				$s['zip5'] = $source['Zip5'];
				$s['zip4'] = $source['Zip4'];
			}elseif(isset($source['address'])){
				$s = $source;
			}else{
				$s = array();
			}
			break;

		default:
			$s = array();
			break;

	}

	$ret  = '<label for="change-info-firmname">Company (for address, optional)</label>';
	$ret .= '<input type="text" id="change-info-firmname" value="'.$s['firmname'].'" data-original="'.$s['firmname'].'">';
	$ret .= '<label for="change-info-address">Address</label>';
	$ret .= '<input type="text" id="change-info-address" value="'.$s['address'].'" data-original="'.$s['address'].'" >';
	$ret .= '<label for="change-info-unit">Bldg / Unit / Other (optional, put apartment on address line)</label>';
	$ret .= '<input type="text" id="change-info-unit" value="'.$s['unit'].'" data-original="'.$s['unit'].'">';
	$ret .= '<label for="change-info-city">City</label>';
	$ret .= '<input type="text" id="change-info-city" value="'.$s['city'].'" data-original="'.$s['city'].'">';
	$ret .= '<label for="change-info-state">State</label>';
	$ret .= '<input type="text" id="change-info-state" value="'.$s['state'].'" data-original="'.$s['state'].'">';
	$ret .= '<fieldset class="zip">';
	$ret .= '<label for="change-info-zip5">ZIP Code</label>';
	$ret .= '<input type="text" id="change-info-zip5" placeholder="zip code" maxlength="5" value="'.$s['zip5'].'" data-original="'.$s['zip5'].'"> - <input type="text" id="change-info-zip4" placeholder="+4" maxlength="4" value="'.$s['zip4'].'" data-original="'.$s['zip4'].'">';
	$ret .= '</fieldset>';

	return $ret;

}




mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
try{

	switch($user->login()){
	    case 0:
			throw new AuthException('account/');
	        break;
	    case 1:
			$user->regen();
			break;
	    case 2:
			$user->regen();
			break;
	    default:
			throw new AuthException('account/');
	        break;
	}

	switch($step){

		case '1':
			$error = '0';
			$h1 = 'Update Address';
			$html = addressForm($user);
			$html .= '<button id="change-info-button">Update Address</button>';
			break;

		case '2':

			// verify the address
			$addy = new USPSAddress;
			$addy->setFirmName($data1['firmname']);
			$addy->setApt($data1['unit']);
			$addy->setAddress($data1['address']);
			$addy->setCity($data1['city']);
			$addy->setState($data1['state']);
			$addy->setZip5($data1['zip5']);
			$addy->setZip4($data1['zip4']);

			$usps->addAddress($addy);

			$usps->verify();

			$resp = $usps->getArrayResponse();

			if($usps->isSuccess()){
				$resp = $resp['AddressValidateResponse']['Address'];
				if(isset($resp['ReturnText'])){
					$html  = '<p>Important: Address changes have not yet been saved.</p>';
					$html .= '<p><i class="fa fa-exclamation-triangle"></i> The post office was\'t 100% sure. Make sure to double check apartment numbers, etc. Edit if needed.</p>';
					$html .= '<p>'.$user->firstname.' '.$user->lastname.'<br>';
					$html .= (isset($resp['Address1'])) ? $resp['Address1'].'<br>' : '';
					$html .= (isset($resp['FirmName'])) ? $resp['FirmName'].'<br>' : '';
					$html .= $resp['Address2'].'<br>';
					$html .= $resp['City'].' '.$resp['State'].' '.$resp['Zip5'].'-'.$resp['Zip4'].'</p>';
					$html .= '<button id="change-info-use">Use This</button>';
					$html .= '<hr>';
					$html .= addressForm($resp);
					$html .= '<button id="change-info-button"><i class="fa fa-refresh"></i> Re-verify Address</button>';
					$html .= '<button id="change-info-save">Save</button>';
				}else{
					$html  = '<p>Important: Address changes have not yet been saved.</p>';
					$html .= '<p><i class="fa fa-check-circle"></i> The post office found you! Does this look right? Edit if needed.</p>';
					$html .= '<p>'.$user->firstname.' '.$user->lastname.'<br>';
					$html .= (isset($resp['Address1'])) ? $resp['Address1'].'<br>' : '';
					$html .= (isset($resp['FirmName'])) ? $resp['FirmName'].'<br>' : '';
					$html .= $resp['Address2'].'<br>';
					$html .= $resp['City'].' '.$resp['State'].' '.$resp['Zip5'].'-'.$resp['Zip4'].'</p>';
					$html .= '<button id="change-info-use">Use This</button>';
					$html .= '<hr>';
					$html .= addressForm($resp);
					$html .= '<button id="change-info-button"><i class="fa fa-refresh"></i> Re-verify Address</button>';
					$html .= '<button id="change-info-save">Save</button>';
				}
			}else{
				$html  = '<p><i class="fa fa-times-circle"></i> Please try again. The post office had an issue with that address. Here\'s What they said:</p>';
				$html .= '<p>'.$usps->getErrorMessage().'</p>';
				$html .= addressForm($data1);
				$html .= '<button id="change-info-button"><i class="fa fa-refresh"></i> Re-verify Address</button>';
				$html .= '<button id="change-info-save">Use What I Enter</button>';
			}

			$h1 = 'Update Info';
			$error = '0';

			break;

		case '3':
			// the USE THIS button, save info useing the data fields
			$stmt2 = $db_main->prepare("UPDATE users SET firmname=?, unit=?, address=?, city=?, state=?, zip5=?, zip4=?, address_verification=0 WHERE userid='".$user->id."' LIMIT 1");
			$stmt2->bind_param("sssssss", $data2['firmname'], $data2['unit'], $data2['address'], $data2['city'], $data2['state'], $data2['zip5'], $data2['zip4']);
			$stmt2->execute();
			$stmt2->close;

			$user->updateInfo();


			$fulladdress  = ($user->address['unit'] !== '') ? $user->address['unit'].'<br>' : '';
			$fulladdress .= ($user->address['firmname'] !== '') ? $user->address['firmname'].'<br>' : '';
			$fulladdress .= $user->address['address'].'<br>';
			$fulladdress .= $user->address['city'].' '.$user->address['state'].' '.$user->address['zip5'].'-'.$user->address['zip4'];

			$return = array();
			$return['fulladdress'] = $fulladdress;

			$h1    = 'Update Info';
			$html  = '<p>Your address has been successfully changed to</p>';
			$html .= '<p>'.$fulladdress.'</p>';
			$error = '0';

			break;

		case '4':
			// the SAVE button, save info using the text entered in the input boxes

			$stmt3 = $db_main->prepare("UPDATE users SET firstname=?, lastname=?, firmname=?, unit=?, address=?, city=?, state=?, zip5=?, zip4=?, address_verification=0 WHERE userid='".$user->id."' LIMIT 1");
			$stmt3->bind_param("sssssssss", $data1['firstname'], $data1['lastname'], $data1['firmname'], $data1['unit'], $data1['address'], $data1['city'], $data1['state'], $data1['zip5'], $data1['zip4']);
			$stmt3->execute();
			$stmt3->close;

			$user->updateInfo();

			$fulladdress  = ($user->address['unit'] !== '') ? $user->address['unit'].'<br>' : '';
			$fulladdress .= ($user->address['firmname'] !== '') ? $user->address['firmname'].'<br>' : '';
			$fulladdress .= $user->address['address'].'<br>';
			$fulladdress .= $user->address['city'].' '.$user->address['state'].' '.$user->address['zip5'].'-'.$user->address['zip4'];

			$return = array();
			$return['fulladdress'] = $fulladdress;

			$h1    = 'Update Info';
			$html  = '<p>Your address has been successfully changed to</p>';
			$html .= '<p>'.$fulladdress.'</p>';
			$error = '0';

			break;

	default:
        $error = '1';
        $h1 = 'Error';
        $html = '<p>There was an error. Please try again.</p><p>(ref: invalid step)</p>';
        break;

    }


}catch(mysqli_sql_exception $e){
    $error  = '1';
    $h1     = 'Error';
    $html   = '<p>There was an error updating your account information, please try again.</p><p>(ref: database)</p>';
	$html  .= '<p>'.$e->getMessage().'</p>';
}catch(AuthException $e){
    $error  = '2';
    $redir  = $protocol.$site.'/account/login/?redir='.$e->getMessage();
}catch(Exception $e){
    $error  = '1';
    $h1     = 'Error';
    $html   = '<p>'.$e->getMessage().'</p>';
}
mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries


$json = array(
	'error'     => $error,
	'redir'		=> $redir,
	'h1'        => $h1,
	'html'   	=> $html,
	'return'	=> $return
);

$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();
?>
