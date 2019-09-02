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
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);

/* PAGE VARIABLES */
$currentpage = 'artwork/series2/'.$_GET['series'].'/';
$series_id = $_GET['series'];



$series_sql = $db_main->query("SELECT * FROM series_info WHERE series_id='".$series_id."' LIMIT 1");
if($series_sql !== FALSE){
    $series_sql->data_seek(0);
    while($seriesinfo = $series_sql->fetch_object()){

      $series_tag = $seriesinfo->series_tag;
      $series_name = $seriesinfo->series_name;
      $front_img = $protocol.$site.'/'.$seriesinfo->front_img;
      $back_img = $protocol.$site.'/'.$seriesinfo->back_img;
      $front_img_check = '/'.$seriesinfo->front_img;
      $back_img_check = '/'.$seriesinfo->back_img;
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

// check user subscription status
$user->checksub();

ob_end_flush();
/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');


/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');

print'

<script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery-1.12.4.js"></script>
<script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://helmarbrewing.com/js/jquery.dataTables.min.css">


<div class="artwork">
    <h4>Artwork</h4>
    <h1>'.$series_name.'</h1>
    <div class="series_images">';
        
        // print the back pic if exists
        if(file_exists($_SERVER['DOCUMENT_ROOT'].$front_img_check)){
           print '<img src="'.$front_img.'" />';
        }
        
        // print the back pic if exists
        if(file_exists($_SERVER['DOCUMENT_ROOT'].$back_img_check)){
            if($back_img_check!="/"){
                print '<img src="'.$back_img.'" />';
            }
        }


print'    </div>
	<div class="series_desc">'.$series_desc.'</div>';


 /* setup code if 1) user logged in with no subscription, 2) user logged in with subscription, 3) user not logged in */

 if(isset($user)){
    if( $user->login() === 1 || $user->login() === 2 ){
		/* do this code if user is logged in */

		// load active auctions in array
		$R_cards2 = $db_main->query("
		SELECT *
		FROM activeEbayAuctions
			"
		);
		$R_cards2->data_seek(0);
		$wishlist = array();

		while($card2 = $R_cards2->fetch_object()){

			$wishlist[$card2->series_tag.'-'.$card2->cardnum]['auctionID'] = $card2->auctionID;

		}

		print '<div class="series_desc"><p><a href="/artwork/csv/'.$series_id.'"><i class="fa fa-download"></i> Download Complete Card List</a></p></div>';


	$R_cards = $db_main->query("
	SELECT cardList.*, userCardChecklist.quantity, userCardChecklist.wishlistQuantity, userCardChecklist.marketWishlist FROM cardList
	LEFT JOIN userCardChecklist
		ON cardList.series = userCardChecklist.series
		AND cardList.cardnum = userCardChecklist.cardnum
		AND userCardChecklist.userid = '".$user->id."'
	WHERE cardList.series = '".$series_tag."'"
);

        // do this if the user subscription = active
				// thanks internet!
				// http://www.javascripttoolbox.com/lib/table/examples.php
				//

            print'
            <table id="example" class="display compact">
                      <thead>
                        <tr>
                            <th>Card Number</th>
                            <th>Player</th>
                            <th>Stance / Position</th>
                            <th>Team</th>
                            <th>Last Sold Date</th>
                            <th>Max Sell Price</th>
                            <th>Pictures</th>
                            <th title="Use the checkbox to indicate you own this card">Card Owned <i class="fa fa-info-circle"></i></th>
                            <th title="Use the checkbox to indicate you have an interest in this card when it shows up on eBay. Checking the box, you will receive an email when it goes live">eBay Auction Wishlist <i class="fa fa-info-circle"></i></th>
                            <th title="Use the checkbox to indicate you want to buy/trade for this card on the Marketplace">Want from Marketplace <i class="fa fa-info-circle"></i></th>
                            <th>Active Auction</th>
                        </tr>
                      </thead>
                      <tbody>
            ';
            $i = 0;
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
                    $frontpic = '/images/cardPics/'.$card->series.'_'.$card->cardnum.'_Front.jpg';
                    $frontthumb = '/images/cardPics/thumb/'.$card->series.'_'.$card->cardnum.'_Front.jpg';
                    $backpic  = '/images/cardPics/'.$card->series.'_'.$card->cardnum.'_Back.jpg';
                    $backthumb  = '/images/cardPics/thumb/'.$card->series.'_'.$card->cardnum.'_Back.jpg';
										$frontlarge = '/images/cardPics/large/'.$card->series.'_'.$card->cardnum.'_Front.jpg';
										$backlarge  = '/images/cardPics/large/'.$card->series.'_'.$card->cardnum.'_Back.jpg';

                    //check if either pic exists
                    if( file_exists($_SERVER['DOCUMENT_ROOT'].$frontlarge) || file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge) ){

                        // print the front pic if exists
                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$frontlarge)){
                            print'
                                <a href="'.$protocol.$site.'/'.$frontlarge.'" data-lightbox="'.$card->series.'_'.$card->cardnum.'" ><img src="'.$protocol.$site.$frontthumb.'"></a>
                            ';
                        }

                        // insert space
                        if( file_exists($_SERVER['DOCUMENT_ROOT'].$frontlarge) && file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge) ){
                            print'&nbsp;&nbsp;';
                        }

                        // print the back pic if exists
                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge)){
                            print'
                                <a href="'.$protocol.$site.'/'.$backlarge.'" data-lightbox="'.$card->series.'_'.$card->cardnum.'" ><img src="'.$protocol.$site.$backthumb.'"></a>
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


            		/* add CHECKLIST icon */
					if($card->quantity > 0){
						print '<td><a href="javascript:;"><i class="fa fa-check-square-o" onclick="checklist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="'.$card->cardnum.'"></i></a></td>';
					} else{
						print '<td><a href="javascript:;"><i class="fa fa-square-o" onclick="checklist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="'.$card->cardnum.'"></i></a></td>';
					}

					/* add WISHLIST icon */
		if($card->wishlistQuantity > 0){
			print '<td><a href="javascript:;"><i class="fa fa-check-square-o" onclick="wishlist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="WISH'.$card->cardnum.'"></i></a></td>';
		} else{
			print '<td><a href="javascript:;"><i class="fa fa-square-o" onclick="wishlist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="WISH'.$card->cardnum.'"></i></a></td>';
        }

        
        if($card->marketWishlist > 0){
            print '<td><a href="javascript:;" ><i class="fa fa-check-square-o" onclick="marketWishlist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="mktWish'.$card->cardnum.'_'.$card->series.'"></i></a></td>';
        } else{
            print '<td><a href="javascript:;" ><i class="fa fa-square-o" onclick="marketWishlist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="mktWish'.$card->cardnum.'_'.$card->series.'"></i></a></td>';
        }


		// add ebay auction if active
			print '<td><a href="http://www.ebay.com/itm/'. $wishlist[$series_tag.'-'.$card->cardnum]['auctionID'].'/" target="_blank">'.$wishlist[$series_tag.'-'.$card->cardnum]['auctionID'].'</a></td>';


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


            ';



		/* END code if user is logged in, but not paid subscription */
    }else{
		/* do this if user is not logged in */
		print '<a href="'.$protocol.$site.'/account/register/"><img class="centered" src="'.$protocol.$site.'/img/checklist-sample.jpg"></a>';
	}
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

<script>
$(document).ready(function() {
    $('#example').DataTable({
  "columns": [
    { "width": "5%" },
    { "width": "15%" },
    { "width": "10%" },
    { "width": "9%" },
    { "width": "9%" },
	{ "width": "9%" }, 
	{ "width": "7%" },
    { "width": "9%" },
    { "width": "9%" },
	{ "width": "9%" },
	{ "width": "9%" }
  ],
  "pageLength": 50
});
} );
</script>

<script>
function checklist(s, c){
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/artwork/ajax/checklist/",
		{ series:s, cardnum:c },
        function( data ) {
			if(data.error === 0){
				if(data.qty === '1'){
						$('#' + c).removeClass('fa-square-o');
						$('#' + c).addClass('fa-check-square-o');
				}else if(data.qty === '0'){
						$('#' + c).removeClass('fa-check-square-o');
						$('#' + c).addClass('fa-square-o');
				} else {
					alert("else part...");
				}
			}else{
				alert(data.msg);
			}
            document.getElementById('fullscreenload').style.display = 'none';
        },
        "json"
    )
    .fail(function() {
        alert('There was an error, refresh the page.');
    });
}
</script>

<script>
function wishlist(s, c){
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/artwork/ajax/wishlist/",
		{ series:s, cardnum:c },
        function( data ) {
			if(data.error === 0){
				if(data.qty === '1'){
						$('#WISH' + c).removeClass('fa-square-o');
						$('#WISH' + c).addClass('fa-check-square-o');
				}else if(data.qty === '0'){
						$('#WISH' + c).removeClass('fa-check-square-o');
						$('#WISH' + c).addClass('fa-square-o');
				} else {
					alert("else part...");
				}
			}else{
				alert(data.msg);
			}
            document.getElementById('fullscreenload').style.display = 'none';
        },
        "json"
    )
    .fail(function() {
        alert('There was an error, refresh the page.');
    });
}
</script>

<script>
function marketWishlist(s, c){
	var txt = "To list a card on the Helmar Brewing Marketplace, you must agree to the following:\n\nYou understand that you are listing a card or cards on the Helmar Brewing Marketplace.\nWithin the Marketplace, your identity will be anonymous. In using the Marketplace, \nanother user may or may not reach out to you via the Marketplace contact form. You \nwill receive this communication to the email you have listed under your \nhelmarbrewing.com account. You are free to communicate with the other party \nas you wish. This will take place outside of the helmarbrewing.com website and \nyou will not hold Helmar Brewing responsible for any issues that may occur outside \nof the helmarbrewing.com website.\n\nHelmar Brewing recommends safe trading practices.\n\nIn order to list your card, you must agree to the above terms. If you do not agree, \nyou will be taken back to the page you were last visiting.\n\nClick \'ok\' to agree to these terms, or click \'cancel\' to return to the checklist";
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/artwork/ajax/marketWishlist/",
		{ series:s, cardnum:c },
        function( data ) {
			if(data.error === 0){
				if(data.qty === '1'){
					if(window.confirm(txt)){
						$('#mktWish' + c + '_' + s).removeClass('fa-square-o');
						$('#mktWish' + c + '_' + s).addClass('fa-check-square-o');
						location.reload();
					}else{
						$.get(
							"/artwork/ajax/marketWishlist/",
							{ series:s, cardnum:c },
							"json"
						);
						window.alert("You need to accept terms to list on the Marketplace");
					}
				}else if(data.qty === '0'){
						$('#mktWish' + c + '_' + s).removeClass('fa-check-square-o');
						$('#mktWish' + c + '_' + s).addClass('fa-square-o');
						location.reload();
				} else {
					alert("else part...");
				}
			}else{
				alert(data.msg);
				location.reload();
			}
            document.getElementById('fullscreenload').style.display = 'none';
        },
        "json"
    )
    
    .fail(function() {
        alert('There was an error, refresh the page.');
    });
    
}
</script>
