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
        <p class="single">The R-319 Helmar series has 180 subjects including many of your favorite players. All the original art was painted by our artists over a period of years, you won\'t find it elsewhere. Given the scope, the expense and the complexity for a small company or artist to put together a 385 card set of original and exceptional art, no one else may attempt something this ambitious for decades. They are not available in full sets.</p>
		
		<div id="album-artwork">
        <table class="tables">
          <thead>
            <tr>
              <th>Card Number</th>
              <th>Player</th>
              <th>Stance / Position</th>
              <th>Team</th>
              <th>Last Sold Date</th>
              <th>Max Sell Price</th>
              <th>Pictures</th>
			  <th>Personal Checklist</th>
            </tr>
          </thead>
          <tbody>
';
$i = 0;
$R_cards = $db_main->query("SELECT * FROM cardList WHERE series = 'R319-Helmar'");
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
        if($card->averagesold == 0){
            print'
                <td></td>
                <td></td>
            ';
        }else{
            print'
                <td>'.$card->lastsold.'</td>
                <td>'.$card->maxSold.'</td>
            ';
        }

        // need to add ************ for one row, check if picture exists
        print'
                <td align="center" class="picCol">
        ';


        // define the pictures
        $frontpic = 'http://www.helmarbrewing.com/images/cardPics/'.$card->series.'_'.$card->cardnum.'_Front.jpg';
        $frontthumb = 'http://www.helmarbrewing.com/images/cardPics/thumb/'.$card->series.'_'.$card->cardnum.'_Front_small.jpg';
        $backpic  = 'http://www.helmarbrewing.com/images/cardPics/'.$card->series.'_'.$card->cardnum.'_Back.jpg';
        $backthumb  = 'http://www.helmarbrewing.com/images/cardPics/thumb/'.$card->series.'_'.$card->cardnum.'_Back_small.jpg';

        //check if either pic exists
        if( file_exists($frontpic) || file_exists($backpic) ){

            // print the front pic if exists
            if(file_exists($frontpic)){
                print'
                    <a href="http://www.helmarbrewing.com/'.$frontpic.'" data-lightbox="'.$card->series.'_'.$card->cardnum.'" ><img src="http://www.helmarbrewing.com/'.$frontthumb.'"></a>
                ';
            }

            // insert space
            if( file_exists($frontpic) && file_exists($backpic) ){
                print'&nbsp;&nbsp;';
            }

            // print the back pic if exists
            if(file_exists($backpic)){
                print'
                    <a href="http://www.helmarbrewing.com/'.$backpic.'" data-lightbox="'.$card->series.'_'.$card->cardnum.'" ><img src="http://www.helmarbrewing.com/'.$backthumb.'"></a>
                ';

            }

        // neither pic exists print message instead
        }else{
            print'
                    <i>no picture</i>
            ';
        }
		/* end card pic */
        print'</td>';
		
		/* need to add ajax code , how to add fontawesome icons? */
	/*	print '<td><i class="fa fa-user-plus"></i> Add</td>'; */
		print '<td><img src="https://www-304.ibm.com/support/knowledgecenter/api/content/nl/en-us/SSS8GR_2.5.0/com.ibm.websphere.datapower.xc.doc/cloudcommon/add-icon.gif" alt="'.$card->series.','.$card->cardnum.'"></td>';
		
		
		
		/* end row */
		print '</tr>';
		
        $i++;
        $updated = $card->updatedate;
    }
    $R_cards->free();
}else{
    print'
        <tr><td colspan="7">could not get list of cards</td></tr>
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
    </div>


';

/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>

<script>
 $(document).ready(function() {
        $("#album-artwork a").click(function(e) {
            e.preventDefault();

            var src = $(this).attr("href");
            //var alt = $(this).next("img").attr("alt");
            var alt = $(this).children().attr("alt");
            //find function also do the same thing if you want to use.
            /*var alt = $(this).find("img").attr("alt");*/

            alert(src); // ok!
            console.log(src); // ok!
            alert(alt); //output: not undefined now its ok!
            console.log(alt); //output: not undefined now its ok!
        });
    });
</script>