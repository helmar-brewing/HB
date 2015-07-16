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

/* PAGE VARIABLES */
$currentpage = '';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

ob_end_flush();
/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');

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



/* HEADER */ require('layout/header1.php');
print'



	    <div class="auctions">

					<h1><a href="subscription/"><img src="/img/join_free.png" alt="Helmar Brewing"></a></h1>

	        <h1>Current Auctions</h1>
	        <p id="auction_end">Auctions end on Tueday evenings</p>
	        <ul id="auction_list">
	        </ul>
			<button id="auction_button">show more</button>
	    </div>
		<script>
		$( document ).ready(function() {
			auctions(1);
		});
		</script>
	';

/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
