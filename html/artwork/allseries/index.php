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
$currentpage = 'artwork/';



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

<script language="javascript" type="text/javascript" src="table.js"></script>
<link rel="stylesheet" type="text/css" href="sorting.css">


<div class="artwork">
    <h4>Artwork</h4>
    <h1>All Card Series</h1>';







 /* setup code if 1) user logged in with no subscription, 2) user logged in with subscription, 3) user not logged in */

 if(isset($user)){
    if( $user->login() === 1 || $user->login() === 2 ){
		/* do this code if user is logged in */

		print'
			<div class="series_desc">Below is the Helmar Brewing checklist for all of our cards.
			You can use filters to display the cards you want and you can even download the checklist.
			To get more information regarding each series, <a href="'.$protocol.$site.'/artwork/">please visit the individual series pages</a>.</div>';

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

		print '<div class="series_desc"><p><a href="/artwork/csvall/"><i class="fa fa-download"></i> Download Complete Card List</a></p></div>';


	$R_cards = $db_main->query("
	SELECT cardList.*, userCardChecklist.quantity, userCardChecklist.wishlistQuantity, series_info.series_name FROM cardList
	LEFT JOIN userCardChecklist
		ON cardList.series = userCardChecklist.series
		AND cardList.cardnum = userCardChecklist.cardnum
		AND userCardChecklist.userid = '".$user->id."'
	LEFT JOIN series_info
			ON cardList.series = series_info.series_tag"
);

        // do this if the user subscription = active
				// thanks internet!
				// http://www.javascripttoolbox.com/lib/table/examples.php
				//

            print'
						<table id="t1" class="tables table-autosort table-autofilter table-stripeclass:alternate table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
                      <thead>
                        <tr>
													<th class="table-filterable table-sortable:ignorecase">Series</th>
                          <th class="table-sortable:numeric">Card Number</th>
                          <th class="table-filterable table-sortable:ignorecase">Player</th>
                          <th class="table-filterable table-sortable:ignorecase">Stance / Position</th>
                          <th class="table-filterable table-sortable:ignorecase">Team</th>
                          <th class="table-sortable:date">Last Sold Date</th>
                          <th class="table-sortable:currency">Max Sell Price</th>
                          <th>Pictures</th>
            			        <th>Personal Checklist</th>
													<th>Personal Wishlist</th>
													<th class="table-sortable:numeric">Active Auction</th>
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
														<td>'.$card->series_name.'</td>
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
						print '<td><a href="javascript:;"><i class="fa fa-check-square-o" onclick="checklist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="'.$card->series.'-'.$card->cardnum.'"></i></a></td>';
					} else{
						print '<td><a href="javascript:;"><i class="fa fa-square-o" onclick="checklist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="'.$card->series.'-'.$card->cardnum.'"></i></a></td>';
					}

					/* add WISHLIST icon */
		if($card->wishlistQuantity > 0){
			print '<td><a href="javascript:;"><i class="fa fa-check-square-o" onclick="wishlist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="WISH'.$card->series.'-'.$card->cardnum.'"></i></a></td>';
		} else{
			print '<td><a href="javascript:;"><i class="fa fa-square-o" onclick="wishlist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="WISH'.$card->series.'-'.$card->cardnum.'"></i></a></td>';
		}

		// add ebay auction if active
			print '<td><a href="http://www.ebay.com/itm/'. $wishlist[$card->series.'-'.$card->cardnum]['auctionID'].'/" target="_blank">'.$wishlist[$card->series.'-'.$card->cardnum]['auctionID'].'</a></td>';


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
		print'
			<div class="series_desc">To view the Helmar Brewing checklist, <a href="'.$protocol.$site.'/account/register/">please sign up for a free account or log in</a>!</div>';
		print '<a href="'.$protocol.$site.'/account/register/"><img class="centered" src="'.$protocol.$site.'/img/checklist-sample.jpg"></a>';
	}
}else{
		/* do this if user is not logged in */
		print'
			<div class="series_desc">To view the Helmar Brewing checklist, <a href="'.$protocol.$site.'/account/register/">please sign up for a free account or log in</a>!</div>';
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
function checklist(s, c){
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/artwork/ajax/checklist/",
		{ series:s, cardnum:c },
        function( data ) {
			if(data.error === 0){
				if(data.qty === '1'){
						$('#' + s + '-' + c).removeClass('fa-square-o');
						$('#' + s + '-' + c).addClass('fa-check-square-o');
				}else if(data.qty === '0'){
						$('#' + s + '-' + c).removeClass('fa-check-square-o');
						$('#' + s + '-' + c).addClass('fa-square-o');
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
						$('#WISH' + s + '-' + c).removeClass('fa-square-o');
						$('#WISH' + s + '-' + c).addClass('fa-check-square-o');
				}else if(data.qty === '0'){
						$('#WISH' + s + '-' + c).removeClass('fa-check-square-o');
						$('#WISH' + s + '-' + c).addClass('fa-square-o');
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