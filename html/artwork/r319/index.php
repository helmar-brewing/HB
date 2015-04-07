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
$seriesName = 'R319-Helmar';

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
$R_cards = $db_main->query("SELECT * FROM cardList WHERE series = '".$seriesName."'");
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
    </div>
';











		/* END code if user is logged in, but not paid subscription */
    }else{
		/* do this if user is not logged in */
		print '<a href="'.$protocol.$site.'/account/register/"><img src="'.$protocol.$site.'/img/checklist-sample.jpg"></a>';    }
}else{
		/* do this if user is not logged in */
		print '<a href="'.$protocol.$site.'/account/register/"><img src="'.$protocol.$site.'/img/checklist-sample.jpg"></a>';
}



/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
