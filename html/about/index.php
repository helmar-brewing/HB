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
$currentpage = 'about/';

ob_end_flush();
/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');

/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');



print'
	<div class="about">
		<div class="images-wrapper"></div>
			<div class="side-image-content">
			<h4>About Helmar</h4>
			<h1>The Story Behind Helmar and Charles</h1>
            <p>Perhaps you\'ve enjoyed some of my other projects including cult favorites Helmar Big League Brew (see the back of every card, including this current offering). By the way, this Helmar beer won a Gold Medal at the 2005 World Beer Festival and has been the subject of quite a few magazine articles. We\'ve also made Potato Chips featuring sports cards and the ongoing series of Helmar Famous Athletes trading cards that one often finds on eBay offered by other sellers. Check out other eBay sellers of Helmar Brewing products. I\'ve been fortunate enough to be at the forefront of some of the hobby\'s most interesting products and trends.</p>
            <p>I believe that quality original art (coupled with a well-known brand) has great potential growth within our hobby. Collectors are increasing less interested in the mass produced items and more appreciative of the innovative art and products of those who are devoted to the game for its own sake. I join those who think that this type of handmade, quality item will be of supreme interest to future collectors.</p>
		</div>
	</div>
';

/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
