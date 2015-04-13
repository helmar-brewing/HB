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


$series_sql = $db_main->query("SELECT * FROM series_info WHERE sort>0 ORDER BY sort ASC");


print'
		<div class="artwork">
			<h4>Artwork</h4>
			<h1>Helmar Artwork</h1>
		</div>';

		if($series_sql !== FALSE){

				print '
				<div class="auctions">
				<ul id="auction_list">';

		    $series_sql->data_seek(0);
		    while($seriesinfo = $series_sql->fetch_object()){

		      $series_id = $seriesinfo->series_id;
		      $series_name = $seriesinfo->series_name;
		      $cover_img = $seriesinfo->cover_img;

					//	<figure style="background:url(\''.$protocol.$site.'/images/cardPics/thumb/R319-Helmar_188_Front.jpg\'); background-size: cover;background-position: top center;"></figure>
					print '
									<li>
											<a href="'.$protocol.$site.'/artwork/series/'.$series_id.'/">
													<img src="'.$cover_img.'">
													<p>'.$series_name.'</p>
											</a>
									</li>
						';





		    }

				print '
				</ul>
				</div>';

		} else {
		  // need error handling - don't load page at all!
		}






/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
