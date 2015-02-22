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

/* PAGE VARIABLES */
$currentpage = '';

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
	        <button><a href="http://stores.ebay.com/Helmar-Brewing-Art-and-History/">Visit Our Store</a></button>
	    	</div>
	    </div>
';
/* HEADER */ require('layout/header1.php');
print'
	    <div class="auctions">
	        <h1>Current Auctions</h1>
	        <p>Auctions end on Tueday October 28 2014.</p>
	        <ul>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/E145-Helmar_8_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/E145-Helmar_12_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/E145-Helmar_18_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/E145-Helmar_34_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/Helmar_Imperial_Cabinet_14_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/L3-Helmar_Cabinet_9_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/L3-Helmar_Cabinet_145_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/R319-Helmar_30_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/R319-Helmar_259_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/T206-Helmar_225_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/Helmar_6_Up_Die-Cut_17_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/R321-Helmar_61_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/R318-Helmar_56_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	            <li><figure style="background:url(\'http://www.helmarbrewing.com/images/cardPics/medium/R318-Helmar_31_Front.jpg\'); background-size: cover;background-position: top center;"></figure></li>
	        </ul>
	    </div>
	';

/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
