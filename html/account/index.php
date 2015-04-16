<?php
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
require_once('libraries/stripe/Stripe.php');
Stripe::setApiKey($apikey['stripe']['secret']);

/* PAGE VARIABLES */
$currentpage = 'account/';


// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(2);
$user->checksub();

switch($user->login()){
    case 0:
        $db_auth->close();
        $db_main->close();
        header('Location: '.$protocol.$site.'/account/login/?redir='.$currentpage,TRUE,303);
        ob_end_flush();
        exit;
        break;
    case 1:
        $user->regen();
        $db_auth->close();
        $db_main->close();
        header('Location: '.$protocol.$site.'/account/verify/?redir='.$currentpage,TRUE,303);
        ob_end_flush();
        exit;
        break;
    case 2:
        $user->regen();
        break;
    default:
        $db_auth->close();
        $db_main->close();
        ob_end_flush();
        print'You created a tear in the space time continuum.';
        exit;
        break;
}



	try {

		$cust = Stripe_Customer::retrieve($user->stripeID);

		if($cust['cards']['total_count'] !== 0){
			$card_info = $cust->cards->data;
			$card_num = '&#183;&#183;&#183;&#183; &#183;&#183;&#183;&#183; &#183;&#183;&#183;&#183; '.$card_info[0]['last4'];
			$brand = $card_info[0]['brand'];
			$exp_month = sprintf('%02d', $card_info[0]['exp_month']);
			$exp_year = $card_info[0]['exp_year'];
			$card_button_text = 'Update Card';
			$delete_disabled = false;
		}else{
			$card_button_text = 'Add Card';
			$delete_disabled = true;
		}
	} catch(Stripe_CardError $e) {

		// this still needs to show the form in case of expired cards that were already on the account

		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception card error)';
	} catch (Stripe_InvalidRequestError $e) {
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception invalid request)';
	} catch (Stripe_AuthenticationError $e) {
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception authentication)';
	} catch (Stripe_ApiConnectionError $e) {
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception api connection)';
	} catch (Stripe_Error $e) {
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception general)';
	} catch (Exception $e) {
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception generic)';
	}


	switch($user->subscription[status]){
		case 'error':
			$sub_msg = 'There was an error determining you subscription status.';
			break;
		case 'none':
			$status = 'You do not have an active subscription.';
			$sub_button_text = 'Subscribe Now';
			break;
		case 'active':
			$status = 'active';
			$sub_button_text = 'Cancel';
			if($user->subscription['cancel_at_period_end'] === true){
				$status .= ' - Your subscription is paid in full until '.date('M j Y', $user->subscription['current_period_end']).' at which point it will be canceled.';
				$sub_button_text = 'Resume Subscription';
			}
			break;
	}


	//show renewal date for subscription




ob_end_flush();
/* <HEAD> */ $head='
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script type="text/javascript">
        Stripe.setPublishableKey(\''.$apikey['stripe']['public'].'\');
    </script>
'; // </HEAD>
/* PAGE TITLE */ $title='Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');


/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');


print'
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
				<dd id="account-email">'.$user->email.'</dd>
				<input type="button" value="Change Email" onclick="changeEmail(1)" />
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
		<hr>

		<section class="subscription">
			<h2>Baseball History Subscription</h2>
			<div>All subscriptions include full access to the website. Describe what that means.</div>
			<ul class="sub-buttons">
	';
	if($user->subscription['status'] === 'none'){
		print '<li class="selected">';
	}else{
		print '<li>';
	}
	print'
					<h4>Website Access</h4>
					<div class="price">FREE</div>
					<p>Description</p>
	';
	if($user->subscription['status'] === 'none'){
		print '<div class="sub-checkbox"><i class="fa fa-check-square-o"></i></div>';
	}else{
		print '<div class="sub-checkbox"><i class="fa fa-square-o"></i></div>';
	}
	print'</li>';
	if($user->subscription['plan_type'] === 'sub-digital'){
		print '<li class="selected">';
	}else{
		print '<li>';
	}
	print'
					<h4>Digital Magazine</h2>
					<div class="price">$20</div>
					<p>Access to digital copies of the magazine via the website.</p>
	';
	if($user->subscription['plan_type'] === 'sub-digital'){
		print '<div class="sub-checkbox"><i class="fa fa-check-square-o"></i></div>';
	}else{
		print '<div class="sub-checkbox"><i class="fa fa-square-o"></i></div>';
	}
	print'</li>';
	if($user->subscription['plan_type'] === 'sub-paper'){
		print'<li class="selected">';
	}else{
		print'<li>';
	}
	print'
					<h4>Paper Magazine</h2>
					<div class="price">$30</div>
					<p>A paper copy of the magazine sent to you when they are released.</p>
	';
	if($user->subscription['plan_type'] === 'sub-paper'){
		print '<div class="sub-checkbox"><i class="fa fa-check-square-o"></i></div>';
	}else{
		print '<div class="sub-checkbox"><i class="fa fa-square-o"></i></div>';
	}
	print'</li>';
	if($user->subscription['plan_type'] === 'sub-digital+paper'){
		print'<li class="selected">';
	}else{
		print'<li>';
	}
	print'
					<h4>Digital + Paper Magazine</h2>
					<div class="price">$36</div>
					<p>A paper copy of the magazine sent to you when they are released as well as access to digital copies of the magazine via the website.</p>
	';
	if($user->subscription['plan_type'] === 'sub-digital+paper'){
		print '<div class="sub-checkbox"><i class="fa fa-check-square-o"></i></div>';
	}else{
		print '<div class="sub-checkbox"><i class="fa fa-square-o"></i></div>';
	}
	print'
				</li>
			</ul>
			<div class="credit-card">
				<form action="" method="POST" id="payment-form">
					<div class="payment-errors" id="payment-errors">'.$msg.'</div>
					<label>Card Number</label>
					<input type="text" size="20" id="card_number" data-stripe="number" value="'.$card_num.'" />
					<fieldset class="cvc">
						<label>CVC</label>
						<input type="text" size="4" id="cvc" data-stripe="cvc"/>
					</fieldset>
					<fieldset class="exp">
					<label>Expiration (MM/YYYY)</label>
						<input type="text" size="2" id="exp_month" data-stripe="exp-month" value="'.$exp_month.'"/>
						<span> / </span>
						<input type="text" size="4" id="exp_year" data-stripe="exp-year" value="'.$exp_year.'"/>
					</fieldset>
					<button id="add_update_card" type="submit">'.$card_button_text.'</button>
				</form>
				<button id="delete_card" onclick="deleteCard()"';if($delete_disabled){print' disabled';}print'>Delete Card</button>
			</div>
		</section>
	</div>
';

/* FOOTER */ require('layout/footer1.php');


$db_auth->close();
$db_main->close();
?>
