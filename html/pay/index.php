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

if($_POST['amount'] && $_POST['desc']){
	header('Location: '.$protocol.$site.'/pay/'.$_POST['amount'].'/'.$_POST['desc'],TRUE,303);
}elseif($_POST['amount']){
    header('Location: '.$protocol.$site.'/pay/'.$_POST['amount'],TRUE,303);
}elseif($_POST['desc']){
	header('Location: '.$protocol.$site.'/pay/x/'.$_POST['desc'],TRUE,303);
}

$desc = $_GET['desc'];
if($desc == ''){
	$desc = 'Custom Payment';
}else{
	$desc = preg_replace('/[-]/', ' ', $desc);
}



$amount = $_GET['amount'] * 100;

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
            <h1>Pay</h1>
            <form action="/pay/charge/" method="POST">
';
if($amount === 0){
    print'
                <label>Amount</label>
                <input type="text" name="amount">
    ';
}else{
    print'
                <h2>$'.$amount_disp.'</h2>
                <input type="hidden" name="amount" value="'.$amount_disp.'">
    ';
}
if($desc == 'Custom Payment'){
	print'
		<label>What is this payment for?</label>
		<input type="text" name="desc">
	';
}else{
	print'
		<h2>For: '.$desc.'</h2>
		<input type="hidden" name="desc" value="'.$desc.'">
	';
}
print'

                <script
                src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                data-key="'.$apikey['stripe']['public'].'"
                data-image="/img/receipt.png"
                data-name="Helmar Brewing"
                data-description="'.$desc.'"
                data-amount="'.$amount.'"
                data-allow-remember-me="false"
                data-locale="auto">
                </script>
            </form>
			<div class="custom_payment_footer">Your info protected by <a href="http://en.wikipedia.org/wiki/Secure_Socket_Layer" target="_blank">SSL</a> and <a href="https://stripe.com/help/security" target="_blank">Stripe</a>.</div>
';


print'
        </div>
    </body>
</html>
';
