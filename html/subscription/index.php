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

/* PAGE VARIABLES */
$currentpage = 'subscription/';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);
if($user->login() === 1){
	$user->checksub();
}



ob_end_flush();

// date formulas
date_default_timezone_set('US/Eastern');

 $currentmonth = date('n');
 $currentyear = date('Y');

if ($currentmonth == 1 ||$currentmonth == 2 ){
	$dateReturn = 'March '.$currentyear;
} elseif ($currentmonth == 12 ){
	$dateReturn = 'March '.$currentyear+1;
} elseif ($currentmonth == 3 ||$currentmonth == 4 ||$currentmonth == 5 ){
	$dateReturn = 'June '.$currentyear;
} elseif ($currentmonth == 6 ||$currentmonth == 7 ||$currentmonth == 8 ){
	$dateReturn = 'September '.$currentyear;
} elseif ($currentmonth == 9 ||$currentmonth == 10 ||$currentmonth == 11 ){
	$dateReturn = 'December '.$currentyear;
} else{
	$dateReturn = 'Error! well, this isn\'t good! there isn\'t a 13th month!';
}



/* <HEAD> */ $head='
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script type="text/javascript">
        Stripe.setPublishableKey(\''.$apikey['stripe']['public'].'\');
    </script>
'; // </HEAD>
/* PAGE TITLE */ $title='Subscription - Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');

if($user->subscription['status'] != 'active') {
	print'
		    <div class="hero">
		    	<div class="hero-inner">
					<div class="hero-twocol">
						<div class="l">
							<img src="/img/helmar_logo_striped.png" alt="Helmar Brewing">
							<p>Welcome to the Helmar Brewing Company. Helmar is the consummate originator of fine, hand-made art cards for serious sports enthusiasts.</p>
							<p>Our magazine, <em>Baseball History & Art</em>, shares joyous insight into the history and art of the National Game.</p>
						</div>
						<div class="r">
							<img src="/img/mag-2015-06.png"><br><br>
							<a class="button" href="http://helmarbrewing.com/magazine-preview/2015/06/" target="_blank">Preview</a>
						</div>
					</div>
		    	</div>
		    </div>
	';
}else{
	require('layout/header2.php');
}
/* HEADER */ require('layout/header1.php');




print'
	<div class="account">
		<h1 class="pagetitle">Baseball History &amp; Art Subscription</h1>
		<section class="subscription">
';
if($user->login() === 1){
	if($user->subscription['status'] != 'active') {
		print'
			<label>Choose Your Annual Subscription</label>
			<ul class="sub-buttons">
				<li id="sub-digitalpaper" onclick="sub2(\'digitalpaper\')" class="selected">
					<h4>Digital + Paper Magazine</h2>
					<div class="price">$34.95</div>
					<p>A paper copy of the quarterly magazine sent to you when they are released</p>
					<p>Access to the digital copy of the quarterly magazine via the website</p>
					<p>Enhanced card art lists</p>
					<p>Track your personal Helmar card collection</p>
					<label>Note: You will receive your first magazine starting next quarter ('.$dateReturn.')</label>
				</li>
				<li id="sub-paper" onclick="sub2(\'paper\')" class="selected">
					<h4>Paper Magazine</h2>
					<div class="price">$29.95</div>
					<p>A paper copy of the quarterly magazine sent to you when they are released</p>
					<p>Enhanced card art lists</p>
					<p>Track your personal Helmar card collection</p>
					<label>Note: You will receive your first magazine starting next quarter ('.$dateReturn.')</label>
				</li>
				<li id="sub-digital" onclick="sub2(\'digital\')" class="selected">
					<h4>Digital Magazine</h2>
					<div class="price">$19.95</div>
					<p>Access to digital copies of our quarterly magazine via the website</p>
					<p>Enhanced card art lists</p>
					<p>Track your personal Helmar card collection</p>
				</li>
			</ul>
		';
	}else{
		print'
			<div class="sub-row">
				<div class="credit-card">
					<h2>Issues</h2>
		';
		if($user->subscription['digital'] == TRUE){
			print'
					<ul>
						<li><a href="'.$protocol.$site.'/magazine/2015/06/" target="_blank">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Summer 2015</a></li>
					</ul>
			';
		}else{
			print'
					<p>You do not have access to the digital magazine. <a href="'.$protocol.$site.'/account/">Manage your subscription</a> on the <a href="'.$protocol.$site.'/account/">account</a> page.</p>
			';
		}
		print'
				</div>
				<div class="credit-card">
					<label>Current Subscription</label>
					<div id="sub-info"></div>
					<p><a href="'.$protocol.$site.'/account/">Manage your subscription</a> on the <a href="'.$protocol.$site.'/account/">account</a> page.</p>
				</div>
			</div>
			<script>
				$( document ).ready(function() {
					currentSub();
				});
			</script>
		';
	}
}else{
	print'
			<label>Choose Your Subscription</label>
			<ul class="sub-buttons">
				<li id="sub-digitalpaper" onclick="showModal(\'login_or_register\')" class="selected">
					<h4>Digital + Paper Magazine</h2>
					<div class="price">$34.95</div>
					<p>A paper copy of the quarterly magazine sent to you when they are released</p>
					<p>Access to the digital copy of the quarterly magazine via the website</p>
					<p>Enhanced card art lists</p>
					<p>Track your personal Helmar card collection</p>
					<label>Note: You will receive your first magazine starting next quarter ('.$dateReturn.')</label>
				</li>
				<li id="sub-paper" onclick="showModal(\'login_or_register\')" class="selected">
					<h4>Paper Magazine</h2>
					<div class="price">$29.95</div>
					<p>A paper copy of the quarterly magazine sent to you when they are released</p>
					<p>Enhanced card art lists</p>
					<p>Track your personal Helmar card collection</p>
					<label>Note: You will receive your first magazine starting next quarter ('.$dateReturn.')</label>
				</li>
				<li id="sub-digital" onclick="showModal(\'login_or_register\')" class="selected">
					<h4>Digital Magazine</h2>
					<div class="price">$19.95</div>
					<p>Access to digital copies of our quarterly magazine via the website</p>
					<p>Enhanced card art lists</p>
					<p>Track your personal Helmar card collection</p>
				</li>
			</ul>
			<ul class="sub-buttons">
				<li id="sub-none" onclick="showModal(\'login_or_register\')" class="selected free">
					<h4>Website Access</h4>
					<div class="price">FREE</div>
					<p>Join our email list</p>
					<p>View our basic card art list</p>
				</li>
			</ul>
	';
}
print'
		</section>
	</div>
	<div class="modal-holder" id="login_or_register">
		<div class="modal-wrap">
			<div class="modal">
				<i id="modal_close" class="close fa fa-times" onclick="hideModal(\'login_or_register\');"></i>
				<h1>Do you have an account?</h1>
				<a class="button" href="'.$protocol.$site.'/account/register/">Register</a>
				<a class="button" href="'.$protocol.$site.'/account/login/?redir='.$currentpage.'">Log in</a>
			</div>
		</div>
	</div>
';



/* FOOTER */ require('layout/footer1.php');


$db_auth->close();
$db_main->close();
?>
