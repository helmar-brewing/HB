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
$currentpage = 'contact/';

ob_end_flush();
/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');


/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');



print'
	<div class="contact sideimage">
		<div class="images-wrapper"></div>
		<div class="side-image-content">
			<h4>Stay In Touch</h4>
			<h1>Send us a message</h1>
			<p>Text here about what type of messages we\'d like to receive, and how quickly you can expect a response.</p>
			<label>Name</label>
			<input type="text" name="name" id="name">
			<label>Email</label>
			<input type="email" name="email" id="email">
			<label>Message</label>
			<textarea></textarea>
			<button>Send</button>
		</div>
	</div>
';

/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
