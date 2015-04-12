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

// Card Series Variables
$seriesID = $_GET['series'];
//$series_id = 'R319';

$series_sql = $db_main->query("SELECT * FROM series_info WHERE series_id='".$series_id."' LIMIT 1");
if($series_sql !== FALSE){
    $series_sql->data_seek(0);
    while($seriesinfo = $series_sql->fetch_object()){

      $series_tag = $seriesinfo->series_tag;
      $series_name = $seriesinfo->series_name;
      $front_img = $seriesinfo->front_img;
      $back_img = $seriesinfo->back_img;
      $series_desc = $seriesinfo->series_desc;
    }
} else {
  // need error handling - don't load page at all!
}

$series_sql->free();


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
    <h1>'.$series_name.'</h1>
    <div class="series_images">
        <img src="'.$front_img.'" />
        <img src="'.$back_img.'" />
    </div>'

    .$series_desc

    ;


 /* setup code if 1) user logged in with no subscription, 2) user logged in with subscription, 3) user not logged in */

 if(isset($user)){
    if( $user->login() == 1 || $user->login() == 2 ){
		/* do this code if user is logged in, but not paid subscription */

print '
        <table class="tables">
          <thead>
            <tr>
              <th>Card Number</th>
              <th>Player</th>
              <th>Stance / Position</th>
              <th>Team</th>
            </tr>
          </thead>
          <tbody>
';

$i = 0;
$R_cards = $db_main->query("SELECT * FROM cardList WHERE series = '".$series_tag."'");
if($R_cards !== FALSE){
    $R_cards->data_seek(0);
    while($card = $R_cards->fetch_object()){
        print'
            <tr>
                <td>'.$card->cardnum.'</td>
                <td>'.$card->player.'</td>
                <td>'.$card->description.'</td>
                <td>'.$card->team.'</td>
        ';

        print'
                </td>
            </tr>
        ';
        $i++;
        $updated = $card->updatedate;
    }
    $R_cards->free();
}else{
    print'
        <tr><td colspan="4">could not get list of cards</td></tr>
    ';
}
print'
          </tbody>
        </table>
        <p>
            Card list last updated: '.$updated.'<br/>
            Number of Records: '.$i.'
        </p>

';











		/* END code if user is logged in, but not paid subscription */
    }else{
		/* do this if user is not logged in */
		print '<a href="'.$protocol.$site.'/account/register/"><img class="centered" src="'.$protocol.$site.'/img/checklist-sample.jpg"></a>';    }
}else{
		/* do this if user is not logged in */
		print '<a href="'.$protocol.$site.'/account/register/"><img class="centered" src="'.$protocol.$site.'/img/checklist-sample.jpg"></a>';
}

print'
	</div>
';



/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
