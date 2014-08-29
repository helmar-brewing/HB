<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ if($use_https === TRUE){if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;}}

/* SET PROTOCOL FOR REDIRECT */ if($use_https === TRUE){$protocol='https';}else{$protocol='http';}

/* WHICH DATABASES DO WE NEED */
	$db2use = array(
		'db_auth' 	=> TRUE,
		'db_main'	=> TRUE
	);
//

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
	require_once('classes/phnx-user.class.php');
	require_once('libraries/stripe/Stripe.php');
//



/* PAGE VARIABLES */
	$currentpage = 'account/';
	$do = $_POST['do'];
//


$user = new phnx_user;
$user->checklogin(2);

/* <HEAD> */ $head='
	<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
	<script type="text/javascript">
		Stripe.setPublishableKey(\''.$apikey['stripe']['public'].'\');
		var stripeResponseHandler = function(status, response) {
		  var $form = $(\'#payment-form\');
		  if (response.error) {
			// Show the errors on the form
			$form.find(\'.payment-errors\').text(response.error.message);
			$form.find(\'button\').prop(\'disabled\', false);
		  } else {
			// token contains id, last4, and card type
			var token = response.id;
			// Insert the token into the form so it gets submitted to the server
			$form.append($(\'<input type="hidden" name="stripeToken" />\').val(token));
			
			// instead of inserting token here how about we do AJAX instead, then move this whole script and form into a modal
			
			// and re-submit
			$form.get(0).submit();
		  }
		};
 
		jQuery(function($) {
		  $(\'#payment-form\').submit(function(e) {
			var $form = $(this);
 
			// Disable the submit button to prevent repeated clicks
			$form.find(\'button\').prop(\'disabled\', true);
 
			Stripe.card.createToken($form, stripeResponseHandler);
 
			// Prevent the form from submitting with the default action
			return false;
		  });
		});
	</script>
'; // </HEAD>
/* PAGE TITLE */ $title='Account';

include 'layout/header.php';


if($user->login() === 0){
	$db_auth->close();
	$db_main->close();
	header("Location: $protocol://$site/account/login/?redir=$currentpage",TRUE,303);
	ob_end_flush();
	exit;
}elseif($user->login() === 1){
	$user->regen();
	$db_auth->close();
	$db_main->close();
	header("Location: $protocol://$site/account/verify/?redir=$currentpage",TRUE,303);
	ob_end_flush();
	exit;
}elseif($user->login() === 2){
	$user->regen();
	ob_end_flush();

	print'
	<div class="page-content">
		<div class="account">
			<h1 class="pagetitle">Account</h1>
		
			<div class="yourinfo">
				<h2>Your Info</h2>
				<dl>
					<dt>Username</dt>
					<dd>'.$user->username.'</dd>
					<dt>First Name</dt>
					<dd>'.$user->firstname.'</dd>
					<dt>Last Name</dt>
					<dd>'.$user->lastname.'</dd>
					<input type="button" value="Update Info" />
					<hr />
					<dt>Email</dt>
					<dd>'.$user->email.'</dd>
					<input type="button" value="Change Email" />
				</dl>
			</div>
		
			<div class="active-logins">
				<h2>Active Logins</h2>
				<ul>
		';
	
		foreach($user->get_active_logins() as $login){
			print'
					<li>
						Last accessed on <span>'.date("M j Y",$login['logintime']).'</span> at <span>'.date("g:ia",$login['logintime']).'</span><br />from IP address <span>'.$login['IP'].'</span> with <span>'.$login['browser']['parent'].'</span> on <span>'.$login['browser']['platform'].'</span>
						<input type="button" value="Log out device" />
					</li>
			';
		}

		print'
				</ul>
				<form action="logout/all/" method="post">
					<input type="submit" value="Invalidate all logins" />
				</form>
			</div>
			
			<div>
				<h2>Subscription</h2>
				<form action="" method="POST" id="payment-form">
					<div class="payment-errors">'.$_POST['stripeToken'].'</div>
					<label>Card Number</label>
					<input type="text" size="20" data-stripe="number"/>
					<label>CVC</label>
					<input type="text" size="4" data-stripe="cvc"/>
					<label>Expiration (MM/YYYY)</label>
					<input type="text" size="2" data-stripe="exp-month"/>
					<span> / </span>
					<input type="text" size="4" data-stripe="exp-year"/>
					<button type="submit">Submit Payment</button>
				</form>
			</div>
		</div>
	</div>
	';
	
	
}else{
	ob_end_flush();
	$db_auth->close();
	$db_main->close();
	print'You created a tear in the space time continuum.';
	exit;
}

$db_auth->close();
$db_main->close();

include 'layout/footer.php';

?>