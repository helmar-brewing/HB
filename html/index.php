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
	        <a href="" class="hero-logo"><img src="/img/helmar_logo_centered.png" alt="Helmar Brewing"></a>
	    		<div class="hero-copy">
	    			<h1>Welcome to the Helmar Brewing Company</h1>
	    			<p>Helmar Brewing Co. produces fine, hand-made art cards, mainly of sports and history subjects.</p>
	    		</div>
	        <a class="button" href="http://stores.ebay.com/Helmar-Brewing-Art-and-History/">Visit Our Store</a>
	    	</div>
	    </div>
';
/* HEADER */ require('layout/header1.php');
print'
	    <div class="auctions">
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
