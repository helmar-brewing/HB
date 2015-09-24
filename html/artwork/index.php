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


$series_sql = $db_main->query("SELECT * FROM series_info WHERE sort>0 AND series_status <> 'discontinued' ORDER BY sort ASC");
$series_sql2 = $db_main->query("SELECT * FROM series_info WHERE sort>0 AND series_status = 'discontinued'  ORDER BY sort ASC");


print'
		<div class="artwork">
			<h4>Artwork</h4>
			<h1>Helmar Artwork</h1>
		';

		if($series_sql !== FALSE){

				print '
				<div class="auctions">
				<ul id="auction_list">';

		    $series_sql->data_seek(0);
		    while($seriesinfo = $series_sql->fetch_object()){

				$series_id = $seriesinfo->series_id;
				$series_name = $seriesinfo->series_name;
				$cover_img = $protocol.$site.'/'.$seriesinfo->cover_img;



				print'
					<li>
						<a style="background:url(\''.$cover_img.'\'); background-size: cover; background-position: center center;background-repeat: repeat;" href="'.$protocol.$site.'/artwork/series/'.$series_id.'">
							<span>
								<figure style="background:url(\''.$cover_img.'\'); background-size: contain;background-position: center center;background-repeat: no-repeat;"></figure>
							</span>
						</a>
						<p class="nameplate">'.$series_name.'</p>
					</li>
				';
		    }
				print '
				</ul>
				<h3>Discontinued Sets</h3>
				<ul id="auction_list">';

				$series_sql2->data_seek(0);
		    while($seriesinfo = $series_sql2->fetch_object()){

				$series_id = $seriesinfo->series_id;
				$series_name = $seriesinfo->series_name;
				$cover_img = $protocol.$site.'/'.$seriesinfo->cover_img;



				print'
					<li>
						<a style="background:url(\''.$cover_img.'\'); background-size: cover; background-position: center center;background-repeat: repeat;" href="'.$protocol.$site.'/artwork/series/'.$series_id.'">
							<span>
								<figure style="background:url(\''.$cover_img.'\'); background-size: contain;background-position: center center;background-repeat: no-repeat;"></figure>
							</span>
						</a>
						<p class="nameplate">'.$series_name.'</p>
					</li>
				';
		    }

				print '
				</ul>
				</div>';

		} else {
		  // need error handling - don't load page at all!
		}

		print'</div>';






/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
