<?php
exit;




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
$currentpage = 'account/';
$redir = 'account/';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(2);

switch($user->login()){
    case 0:
        $db_auth->close();
        $db_main->close();
        break;
    case 1:
			$db_auth->close();
			$db_main->close();
			header('Location: '.$protocol.$site.'/account/login/?redir='.$redir,TRUE,303);
			ob_end_flush();
			exit;
			break;
    case 2:
        // they already have an elevated login, how did they get here? let's make them verify anyways, to avoid a loop.
				$db_auth->close();
        $db_main->close();
        header('Location: '.$protocol.$site.'/account/login/?redir='.$redir,TRUE,303);
        ob_end_flush();
        exit;
        break;
    default:
        $db_auth->close();
        $db_main->close();
        ob_end_flush();
        print'You created a tear in the space time continuum.';
        exit;
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
/* PAGE TITLE */ $title='Subscriptions - Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');


/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');


print'
	<div class="account">
		<h1 class="pagetitle">Helmar Subscription Plans</h1>
		<section class="subscription">
			<p>Below are the subscription plans we offer. Click <a href="/account/register">HERE</a> or below to create an account. After creating an account, you will be able to select a paid subscription or remain at a free membership</p>
			<a href="/account/register">
			<ul class="sub-buttons">
	';





		print'<li id="sub-digitalpaper" class="selected">';

	print'
					<h4>Digital + Paper Magazine</h2>
					<div class="price">$34.95</div>
					<p>A paper copy of the quarterly magazine sent to you when they are released</p>
					<p>Access to the digital copy of the quarterly magazine via the website</p>
					<p>Enhanced card art lists</p>
					<p>Track your personal Helmar card collection</p>
	';

		print '<div class="sub-checkbox"><i id="sub-digitalpaper-checkbox" class="fa fa-square-o"></i></div>';

	print'
				</li>
	';






		print'<li id="sub-paper" class="selected">';

	print'
					<h4>Paper Magazine</h2>
					<div class="price">$29.95</div>
					<p>A paper copy of the quarterly magazine sent to you when they are released</p>
					<p>Enhanced card art lists</p>
					<p>Track your personal Helmar card collection</p>
	';

		print '<div class="sub-checkbox"><i id="sub-paper-checkbox" class="fa fa-square-o"></i></div>';

	print'</li>';




		print '<li id="sub-digital" class="selected">';

	print'
					<h4>Digital Magazine</h2>
					<div class="price">$19.95</div>
					<p>Access to digital copies of our quarterly magazine via the website</p>
					<p>Enhanced card art lists</p>
					<p>Track your personal Helmar card collection</p>
	';

		print '<div class="sub-checkbox"><i id="sub-digital-checkbox" class="fa fa-square-o"></i></div>';

	print'</li>';






	print'</ul>';

	print '<ul class="sub-buttons">';

		print '<li id="sub-none" class="selected free">';

	print'
					<h4>Website Access</h4>
					<div class="price">FREE</div>
					<p>Join our email list</p>
					<p>View our basic card art list</p>
	';

		print '<div class="sub-checkbox"><i id="sub-none-checkbox" class="fa fa-square-o"></i></div>';

	print'</li>';
	print'</ul>
	</a>';




	print'

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
