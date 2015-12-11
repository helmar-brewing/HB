<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps();

/* WHICH DATABASES DO WE NEED */
$db2use = array(
	'db_auth' 	=> FALSE,
	'db_main'	=> FALSE
);

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
require_once('classes/phnx-user.class.php');
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);
require_once('libraries/drill/drill.php');





$token = $_POST['stripeToken'];

$amount = preg_replace('/[^0-9.]/', '', $_POST['amount']);

$desc = $_POST['desc'];

$amount = $amount * 100;
$amount_disp = $amount / 100;

ob_end_flush();

print'
    <!DOCTYPE html>
    <html>
    <head>
        <title>Custom Payment Form</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width">
        <meta name="viewport" content="initial-scale=1.0">
		<style>
            body{
                background-color:#EEEEEE;
            }
            .custom_payment{
                width:90%;
                max-width:400px;
                margin:1em auto;
                padding:1em;
                border-radius:3px;
                background-color:white;
                font-family:arial;
                text-align:center;
            }
            label{
                display:block;
            }
            input[type="text"]{
                font-size:1em;
                width:100%;
                display:block;
                padding:0;
                border:none;
                outline:none;
                box-shadow:none;
                -webkit-appearance:none;
                line-height:2em;
                height:2em;
                box-sizing:border-box;
                text-align:center;
                border-radius:5px;
                border:1px solid #CCC;
                margin:1em 0;
            }
            .custom_payment_logo{
                display:block;
                width:100%;
                margin:0 auto;
            }
			.custom_payment_footer{
				font-size:.8em;
				padding-top:1em;
				margin-top:1em;
				border-top:1px solid #EEEEEE;
				color:#999999;
			}
			.custom_payment_footer a{
				color:#999999;
			}
        </style>
    </head>
    <body>
        <div class="custom_payment">
			<img class="custom_payment_logo" src="/img/helmar_logo_lg_color.png">
';

try {
  $charge = \Stripe\Charge::create(array(
    "amount" => $amount,
    "currency" => "usd",
    "source" => $token,
    "description" => $desc,
    "receipt_email" => $_POST['stripeEmail']
    ));

    print'
        Your payment has been processed. Thank you.
    ';

	$html = '
		<p>You received a payment via helmarbrewing.com/pay. Please log in to Stripe to verify the payment before taking further aciton.</p>
		<p>
			From: '.$_POST['stripeEmail'].'<br>
			Amount: '.$amount_disp.'<br>
			For: '.$desc.'<br>

		</p>
	';
	$to = $apikey['mandrill_email'];
	$args = array(
		'key' => $apikey['mandrill'],
		'message' => array(
			"html" => $html,
			"from_email" => "no-reply@helmarbrewing.com",
			"from_name" => "Helmar Custom Payment",
			"subject" => "Helmar Custom Payment (Timestamp ".time()." )",
			"to" => $to,
			"track_opens" => true,
			"track_clicks" => false,
			"auto_text" => true
		)
	);

	$drill = new \Gajus\Drill\Client($apikey['mandrill']);
    $r = $drill->api('messages/send', $args);
	if($r['status']== 'error'){
		// maybe create a log?
	}

} catch(\Stripe\Error\Card $e) {
    $body = $e->getJsonBody();
    $err  = $body['error'];
    print $err['message'];

} catch (\Stripe\Error\RateLimit $e) {

    print'
        <p>There was an error charging your card, please try again.</p>
        <a href="/pay/'.$amount_disp.'/">Start Over</a>
    ';

} catch (\Stripe\Error\Authentication $e) {

    print'
        <p>There was an error charging your card, please try again.</p>
        <a href="/pay/'.$amount_disp.'/">Start Over</a>
    ';

} catch (\Stripe\Error\ApiConnection $e) {

    print'
        <p>There was an error charging your card, please try again.</p>
        <a href="/pay/'.$amount_disp.'/">Start Over</a>
    ';

} catch (\Stripe\Error\Base $e) {

    print'
        <p>There was an error charging your card, please try again.</p>
        <a href="/pay/'.$amount_disp.'/">Start Over</a>
    ';

} catch (Exception $e) {

    print'
        <p>There was an error charging your card, please try again.</p>
        <a href="/pay/'.$amount_disp.'/">Start Over</a>
    ';

}

print'
			<div class="custom_payment_footer">Your info protected by <a href="http://en.wikipedia.org/wiki/Secure_Socket_Layer" target="_blank">SSL</a> and <a href="https://stripe.com/help/security" target="_blank">Stripe</a>.</div>
        </div>
    </body>
</html>
';
