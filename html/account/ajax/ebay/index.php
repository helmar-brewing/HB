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
if($user->login() === 2){


    if($step === '1'){
        $error = '0';
        $h1   = 'Update ebay Account';
        $html = '
            <p>Please enter your eBay username.</p>
            <fieldset class="input-icon-button">
                <input type="text" id="change-email-field">
                <button type="button" id="change-email-button"><i class="fa fa-refresh"></i></button>
            </fieldset>
        ';
    }
    if($step === '2'){
        $ebay = strtolower($data1);

            if($ebay == $user->ebay){
                $error  = '1';
                $h1     = 'Update ebay Account';
                $html   = '
                    <p>You already told us your ebay username was <span class="msg-user-input">'.$ebay.'</span>, if you are trying to change ebay username, please enter your new username.<br><span class="errortime">'.date("G:i:sa",time()).'</span></p>
                    <fieldset class="input-icon-button">
                        <input type="text" id="change-email-field" value="'.$ebay.'">
                        <button type="button" id="change-email-button"><i class="fa fa-refresh"></i></button>
                    </fieldset>
                ';
            }else{
				mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
				try{
					$db_main->query("UPDATE users SET ebayID='$ebay' WHERE username='".$user->username."' LIMIT 1");
					$error  = '0';
				}catch(mysqli_sql_exception $e){
					$error = '1';
				}catch(Exception $e){
					$error = '1';
				}
				mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
				if($error == '0'){
					$h1     = 'Update ebay Account';
                    $html   = '
                        <p>Your eBay username has been changed to <span class="msg-user-input">'.$ebay.'</span></p>
                    ';
				}else{
					$h1     = 'Update ebay Account';
                    $html   = '
                        <p>There was an error updating your eBay username. Please try again. (ref: database fail)<br><span class="errortime">'.date("G:i:sa",time()).'</span></p>
                        <fieldset class="input-icon-button">
                            <input type="text" id="change-email-field" value="'.$ebay.'">
                            <button type="button" id="change-email-button"><i class="fa fa-refresh"></i></button>
                        </fieldset>
                    ';
                }
            }

    }


    $json = array(
        'error' => $error,
        'h1' => $h1,
        'content' => $html,
        'ebay' => $ebay,
    );




}else{
    $json = array(
        'error'     => '1',
        'h1'        => 'Error',
        'content'   => '<p>There was an error updating your account. Please refresh the page and try again.</p><p>(ref. auth fail)</p>',
    );
}


$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();

?>
