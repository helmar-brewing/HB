<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps();

/* WHICH DATABASES DO WE NEED */
$db2use = array(
	'db_auth' 	=> FALSE,
	'db_main'	=> FALSE
);

/* GET KEYS TO SITE */ require($path_to_keys);

/* PAGE VARIABLES */
$currentpage = 'artwork/';

ob_end_flush();
/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');


/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');

print'
    <div class="artwork">
        <h4>Artwork</h4>
        <h1>Helmar Artwork</h1>
        <ul>
            <li>
                <a href="'.$protocol.'://'.$site.'/artwork/r319/">
                    <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/R319-Helmar_188_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                    <p>R319-Helmar</p>
                </a>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/E145-Helmar_4_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>E145-Helmar</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/Helmar_6_Up_Die-Cut_71_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>Helmar 6 Up Die-Cut</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/Helmar_Trolley_Card_13_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>Helmar Trolley Card</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/R318-Helmar_147_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>R318-Helmar</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/Helmar_Cabinet_14_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>Helmar Cabinet</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/T206-Helmar_110_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>T206-Helmar</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/H813-4_Boston_Garter-Helmar_17_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>H813-4 Boston Garter-Helmar</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/R321-Helmar_1_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>R321-Helmar</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/Helmar_Imperial_Cabinet_23_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>Helmar Imperial Cabinet</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/L1-Helmar_43_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>L1-Helmar</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/T202-Helmar_2_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>T202-Helmar</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/Our_Guy_18_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>Our Guy</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/T3-Helmar_1_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>T3-Helmar</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/Helmar_Pharohs_Choice_Cabinet_1_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>Helmar Pharoh\'s Choice Cabinet</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/L3-Helmar_Cabinet_52_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>L3-Helmar Cabinet</p>
            </li>
            <li>
                <figure style="background:url(\''.$protocol.'://'.$site.'/images/cardPics/thumb/Polo_Grounds_Heroes_51_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
                <p>Polo Grounds Heroes</p>
            </li>
        </ul>
    </div>
';



/* FOOTER */ require('layout/footer1.php');
?>
