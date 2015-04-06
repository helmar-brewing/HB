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
$currentpage = 'artwork/';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

ob_end_flush();
/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');


/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');

print'
    <div class="artwork">
        <h4>Artwork</h4>
        <h1>R319-Helmar Series</h1>
        <div class="series_images">
            <img src="'.$protocol.$site.'/images/cardPics/R319-Helmar_375_Front.jpg" />
            <img src="'.$protocol.$site.'/images/cardPics/R319-Helmar_375_Back.jpg" />
        </div>
        <p class="single">The R-319 Helmar series has 180 subjects including many of your favorite players. All the original art was painted by our artists over a period of years, you won\'t find it elsewhere. Given the scope, the expense and the complexity for a small company or artist to put together a 385 card set of original and exceptional art, no one else may attempt something this ambitious for decades. They are not available in full sets.</p>';


print '<a href="'.$protocol.$site.'/account/register/"><img src="'.$protocol.$site.'/img/checklist-sample.jpg"></a>';


/* FOOTER */ require('layout/footer1.php');


?>
