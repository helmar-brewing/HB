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
        $h1   = 'Change Email Address';
        $html = '
            <p>Please enter your new email address.</p>
            <fieldset class="input-icon-button">
                <input type="email" id="change-email-field">
                <button type="button" id="change-email-button"><i class="fa fa-refresh"></i></button>
            </fieldset>
        ';
    }
    if($step === '2'){
        $email = strtolower($data1);
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            if($email == $user->email){
                $error  = '1';
                $h1     = 'Change Email Address';
                $html   = '
                    <p>You already told us your email was <span class="msg-user-input">'.$email.'</span>, if you are trying to change your email, please enter your new address.<br><span class="errortime">'.date("G:i:sa",time()).'</span></p>
                    <fieldset class="input-icon-button">
                        <input type="email" id="change-email-field" value="'.$email.'">
                        <button type="button" id="change-email-button"><i class="fa fa-refresh"></i></button>
                    </fieldset>
                ';
            }elseif(db1($db_main, "SELECT email FROM users WHERE email='$email' LIMIT 1")){
                $error  = '1';
                $h1     = 'Change Email Address';
                $html   = '
                    <p>A user with that email address already exists.</p><p>Try <a href="'.$protocol.$site.'/account/recover/">Account Recovery</a> if you do not remember the username associated with <span class="msg-user-input">'.$email.'</span><br><span class="errortime">'.date("G:i:sa",time()).'</span></p>
                    <fieldset class="input-icon-button">
                        <input type="email" id="change-email-field" value="'.$email.'">
                        <button type="button" id="change-email-button"><i class="fa fa-refresh"></i></button>
                    </fieldset>
                ';
            }else{
				mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
				try{
					$db_main->query("UPDATE users SET email='$email' WHERE username='".$user->username."' LIMIT 1");
					$cust = Stripe_Customer::retrieve($user->stripeID);
					$cust->email = $email;
					$cust->save();
					$error  = '0';
				}catch(Stripe_CardError $e){
					// Since it's a decline, Stripe_CardError will be caught
					$error = '2';
					$stripe_error = $e->getJsonBody();
				}catch(Stripe_InvalidRequestError $e){
					// Invalid parameters were supplied to Stripe's API
					$error = '2';
					$stripe_error = $e->getJsonBody();
				}catch(Stripe_AuthenticationError $e){
					// Authentication with Stripe's API failed
					// (maybe you changed API keys recently)
					$error = '2';
					$stripe_error = $e->getJsonBody();
				}catch(Stripe_ApiConnectionError $e){
					// Network communication with Stripe failed
					$error = '2';
					$stripe_error = $e->getJsonBody();
				}catch(Stripe_Error $e){
					// Display a very generic error to the user, and maybe send yourself an email
					$error = '2';
					$stripe_error = $e->getJsonBody();
				}catch(mysqli_sql_exception $e){
					$error = '1';
				}catch(Exception $e){
					// Something else happened, completely unrelated to Stripe
					$error = '1';
				}
				mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
				if($error == '0'){
					$h1     = 'Change Email Address';
                    $html   = '
                        <p>Your email address has been changed to <span class="msg-user-input">'.$email.'</span></p>
                    ';
				}elseif($error == '1'){
					$h1     = 'Change Email Address';
                    $html   = '
                        <p>There was an error updating your email address. Please try again. (ref: database fail)<br><span class="errortime">'.date("G:i:sa",time()).'</span></p>
                        <fieldset class="input-icon-button">
                            <input type="email" id="change-email-field" value="'.$email.'">
                            <button type="button" id="change-email-button"><i class="fa fa-refresh"></i></button>
                        </fieldset>
                    ';
				}elseif($error == '2'){
					// reset the email address
					$db_main->query("UPDATE users SET email='".$user->email."' WHERE username='".$user->username."' LIMIT 1");
					$h1     = 'Change Email Address';
                    $html   = '
                        <p>There was an error updating your email address. Please try again. (ref: stripe fail)<br><span class="errortime">'.date("G:i:sa",time()).'</span></p>
                        <fieldset class="input-icon-button">
                            <input type="email" id="change-email-field" value="'.$email.'">
                            <button type="button" id="change-email-button"><i class="fa fa-refresh"></i></button>
                        </fieldset>
                    ';
				}
            }
        }else{
            $error  = '1';
            $h1     = 'Change Email Address';
            $html   = '
                <p>You did not enter a valid email address.<br><span class="errortime">'.date("G:i:sa",time()).'</span></p>
                <fieldset class="input-icon-button">
                    <input type="email" id="change-email-field" value="'.$email.'">
                    <button type="button" id="change-email-button"><i class="fa fa-refresh"></i></button>
                </fieldset>
            ';
        }
    }


    $json = array(
        'error' => $error,
        'h1' => $h1,
        'content' => $html,
        'email' => $email,
		'stripe_error' => $stripe_error
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
