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
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);
require_once('classes/mailchimp.class.php');
$chimp = new \DrewM\MailChimp\MailChimp($apikey['mailchimp']);

/* PAGE VARIABLES */
$currentpage = 'account/';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(2);
$user->checksub('no-cache');

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


// get credit card info
try {
	$cust = \Stripe\Customer::retrieve($user->stripeID);
	if($cust['sources']['total_count'] !== 0){
		$card_info = $cust->sources->data;
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
}catch(Stripe_CardError $e){
	// this still needs to show the form in case of expired cards that were already on the account
	$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception card error)';
}catch (Stripe_InvalidRequestError $e){
	$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception invalid request)';
}catch (Stripe_AuthenticationError $e){
	$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception authentication)';
}catch (Stripe_ApiConnectionError $e){
	$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception api connection)';
}catch (Stripe_Error $e){
	$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception general)';
}catch (Exception $e){
	$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception generic)';
}




// next mag date
$currentmonth = date('n');
$currentyear = date('Y');
if ($currentmonth == 1 || $currentmonth == 2 ){
	$dateReturn = 'March '.$currentyear;
} elseif ($currentmonth == 12 ){
	$dateReturn = 'March '.date('Y',strtotime('+1 year'));
} elseif ($currentmonth == 3 ||$currentmonth == 4 ||$currentmonth == 5 ){
	$dateReturn = 'June '.$currentyear;
} elseif ($currentmonth == 6 ||$currentmonth == 7 ||$currentmonth == 8 ){
	$dateReturn = 'September '.$currentyear;
} elseif ($currentmonth == 9 ||$currentmonth == 10 ||$currentmonth == 11 ){
	$dateReturn = 'December '.$currentyear;
} else{
	$dateReturn = 'Error! well, this isn\'t good! there isn\'t a 13th month!';
}


// find out if they are already subscribed to the newsletter
$subscriber = md5(strtolower($user->email));
$r = $chimp->get('lists/'.$apikey['mailchimp_list'].'/members/'.$subscriber);
$email_sub_check = ($r['status'] === 'subscribed') ? ' checked' : '';

// check subscription status
$status_test    = (in_array($user->subscription['status'], array('active','trialing'), TRUE) && $user->subscription['cancel_at_period_end'] !== TRUE) ? TRUE : FALSE;
$sub_pay_class  = ($status_test) ? 'selected' : '';
$sub_pay_xnote  = ($status_test) ? '' : '<p><strong>Note: You will receive your first magazine starting next quarter ('.$dateReturn.')</strong></p>';
$sub_pay_check  = ($status_test) ? 'fa-check-square-o' : 'fa-square-o';
$sub_free_class = ($status_test) ? '' : 'selected';
$sub_free_check = ($status_test) ? 'fa-square-o' : 'fa-check-square-o';




ob_end_flush();
/* <HEAD> */ $head='
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script type="text/javascript">
        Stripe.setPublishableKey(\''.$apikey['stripe']['public'].'\');
    </script>
'; // </HEAD>
/* PAGE TITLE */ $title='Account - Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');
/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');







print'
	<div class="account">
		<h1 class="pagetitle">Your Account</h1>
		<section class="subscription">
			<h2>Baseball History Subscription</h2>
			<label>Choose Your Subscription</label>
			<p>When you downgrade your subscription to the free plan, you will continue to receive full digital + paper benefits through the end of your current subscription term. When you sign up for the digital + paper paid subscription, your subscription benefits will begin immediately.</p>
			<ul class="sub-buttons">
				<li id="sub-digitalpaper" onclick="sub(\'subscribe\')" class="'.$sub_pay_class.'">
					<a href="javascript:;">
						<h4>Digital + Paper Magazine</h4>
						<div class="price">$39.95</div>
						<p>A paper copy of the quarterly magazine sent to you when they are released</p>
						<p>A Helmar Brewing baseball art card sent quarterly, complementing the theme of the magazine</p>
						<p>Website access to all digital magazines</p>
						<p>Personal card checklist</p>
						<p>Import eBay card purchases to checklist</p>
						<p>Wishlist: eBay auction email notification</p>
						<p>Enhanced card art lists</p>
						'.$sub_pay_xnote.'
						<div class="sub-checkbox"><i id="sub-digitalpaper-checkbox" class="fa '.$sub_pay_check.'"></i></div>
					</a>
				</li>
				<li id="sub-none" onclick="sub(\'cancel\')" class="'.$sub_free_class.'">
					<a href="javascript:;">
						<h4>Website Access / Cancel Paid Subscription</h4>
						<div class="price">FREE</div>
						<p>Join our email list</p>
						<p>View our basic card art list</p>
						<div class="sub-checkbox"><i id="sub-none-checkbox" class="fa '.$sub_free_check.'"></i></div>
					</a>
				</li>
			</ul>
			<div class="sub-row">
				<div class="credit-card">
					<label>Current Subscription</label>
					<div id="sub-info"></div>
				</div>
				<div class="credit-card">
					<form action="" method="POST" id="payment-form">
						<div class="payment-errors" id="payment-errors">'.$msg.'</div>
						<label>Card Number</label>
						<input type="text" maxlength="30" id="card_number" data-stripe="number" value="'.$card_num.'" />
						<fieldset class="exp">
							<label>Expiration</label>
							<input type="text" placeholder="MM" maxlength="2" id="exp_month" data-stripe="exp-month" value="'.$exp_month.'"/><span>/</span><input type="text" placeholder="YYYY" maxlength="4" id="exp_year" data-stripe="exp-year" value="'.$exp_year.'"/>
						</fieldset>
						<fieldset class="cvc">
							<label>CVC</label>
							<input type="text" maxlength="4" id="cvc" data-stripe="cvc"/>
						</fieldset>
						<button id="add_update_card" type="submit">'.$card_button_text.'</button>
					</form>
				</div>
			</div>
		</section>
		<section class="yourinfo">
			<h2>Your Info</h2>
			<dl>
				<dt>Name</dt>
				<dd id="account-name">'.$user->firstname.' '.$user->lastname.'</dd>
				<dt>Address</dt>
				<dd id="account-address">'.$user->address['address'].'<br>'.$user->address['city'].' '.$user->address['state'].' '.$user->address['zip5'].'-'.$user->address['zip4'].'</dd>
				<button type="button" onclick="changeInfo(1)">Update Info</button>
			</dl>
			<dl>
				<dt>Username</dt>
				<dd>'.$user->username.'</dd>
				<dt>Email</dt>
				<dd id="account-email">'.$user->email.'</dd>
				<button type="button" onclick="changeEmail(1)">Change Email</button>
				<dt>Email Subscription</dt>
				<dd>
					<input type="checkbox" id="email-sub-checkbox" onclick="changeEmailSub()"'.$email_sub_check.'> Receive updates about auctions and new cards.
					<p id="emali-sub-msg"></p>
				</dd>
				<dt>eBay Account</dt>
				<dd id="account-ebay">'.$user->ebay.'</dd>
				<button type="button" onclick="updateEbay(1)">Update eBay</button>
			</dl>
		</section>
		<div class="active-logins">
			<h2>Active Logins</h2>
			<form class="all-logins" action="logout/all/" method="post">
				<p>This is a list of devices that are currently logged into your account. Use the "invalidate all logins" button to log out of all devices including this one, or log out devices individually. To log out of just this device, use the log out link in the menu or footer on any page.</p>
				<input type="submit" value="Invalidate all logins" />
			</form>
			<ul id="login-list" class="login-list">
	';

	foreach($user->get_active_logins() as $login){
		print'
			<li>
				Last accessed on <span>'.date("M j Y",$login['logintime']).'</span> at <span>'.date("g:ia",$login['logintime']).'</span><br />
				from IP address <span>'.$login['IP'].'</span> with <span>'.$login['browser']['parent'].'</span> on <span>'.$login['browser']['platform'].'</span>
		';
		if($login['loginID'] === $user->loginID){
			print '<button class="signout" disabled>This Device</button>';
		}else{
			print '<button class="signout" onclick="logoutDevice(\''.$login['loginID'].'\')">Log out device <i class="fa fa-sign-out"></i></button>';
		}
		print '</li>';
	}

	print'
			</ul>
		</div>
	</div>
';

?>
<script>
	$( document ).ready(function() {
		currentSub();
	});
</script>
<?php

/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();

?>
