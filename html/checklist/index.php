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
$currentpage = 'userchecklist/';



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
<div class="artwork">
    <h4>Your Personal Helmar Artwork Collection</h4>';


 /* setup code if 1) user logged in with no subscription, 2) user logged in with subscription, 3) user not logged in */

 if(isset($user)){
    if( $user->login() === 1 || $user->login() === 2 ){
		/* do this code if user is logged in */


      // if user does NOT have subscription:
      if($user->subscription['status'] !== 'active' && $user->subscription['status'] !== 'trialing') {

				 print'

          <p>
              Sorry, you do not have a paid membership! Please checkout our paid subscriptions so you can view your personal Helmar card art checklist!
          </p>

          ';

      }else{
        // do this if the user subscription = active

				print'
				<p>
				Welcome to your personal Helmar artwork collection checklist! Here, you will be able to view cards in your collection.
				<br><br>
				';

				// grab ebay ID
				$R_cards2 = $db_main->query("
				SELECT ebayID
				FROM users
				WHERE userid ='".$user->id."'
					"
				);
				$R_cards2->data_seek(0);
				while($card = $R_cards2->fetch_object()){

					$ebayID = $card->ebayID;

				}
				$R_cards2->free();


				if(is_null($ebayID)){
				}else{
							// grab ebay auctions for ebay user
							$R_cards2 = $db_main->query("
							SELECT *
							FROM ebay_card_summary
							WHERE ebayUserID ='$ebayID' and cardNum > 0
								"
							);
							$totalSummary = $R_cards2->num_rows;
							$R_cards2->free();


							// grab ebay summary last date
							$R_cards2 = $db_main->query("
							SELECT *
							FROM ebay_card_summary
							ORDER BY dateAdded DESC
							LIMIT 1
								"
							);

							$R_cards2->data_seek(0);
							while($card = $R_cards2->fetch_object()){

								$ebaySummaryDate = $card->dateAdded;

							}

							$R_cards2->free();
				}



				// get count of CHECKLIST cards
				$R_cards2 = $db_main->query("
				SELECT
userCardChecklist.quantity, userCardChecklist.wishlistQuantity, userCardChecklist.dateadded,
cardList.series, cardList.cardnum, cardList.player, cardList.description, cardList.team,
series_info.series_name, series_info.series_tag, series_info.sort

FROM userCardChecklist
					LEFT JOIN cardList
						ON cardList.series = userCardChecklist.series
						AND cardList.cardnum = userCardChecklist.cardnum
					LEFT JOIN series_info
						ON userCardChecklist.series = series_info.series_tag

					WHERE userCardChecklist.quantity > 0 AND userCardChecklist.userid ='".$user->id."'
					ORDER BY series_info.sort, userCardChecklist.cardnum
					"
				);
				$checklistQuantity = $R_cards2->num_rows;
				$R_cards2->free();

				// get count of WISHLIST cards
				$R_cards2 = $db_main->query("
				SELECT
userCardChecklist.quantity, userCardChecklist.wishlistQuantity, userCardChecklist.dateadded,
cardList.series, cardList.cardnum, cardList.player, cardList.description, cardList.team,
series_info.series_name, series_info.series_tag, series_info.sort

FROM userCardChecklist
					LEFT JOIN cardList
						ON cardList.series = userCardChecklist.series
						AND cardList.cardnum = userCardChecklist.cardnum
					LEFT JOIN series_info
						ON userCardChecklist.series = series_info.series_tag

					WHERE userCardChecklist.wishlistQuantity > 0 AND userCardChecklist.userid ='".$user->id."'
					ORDER BY series_info.sort, userCardChecklist.cardnum
					"
				);
				$wishlistQuantity = $R_cards2->num_rows;
				$R_cards2->free();



				if(is_null($ebayID) || $ebayID == ""){
					print'

					You have not entered an ebay username! We are able to pull in card auctions
					you have won from ebay from Helmar Brewing Company and add them to your checklist.
					If you have purchased cards from Helmar and would like to pull them to your checklist,
					 <a href="https://helmarbrewing.com/account/">click to add your ebay username on the account info page.</a>
					</p>';
				}else{
					if($totalSummary == 0){
						print'

						We are able to add cards that you\'ve purchased from Helmar Brewing Company
						 and add them to your personal checklist! It looks like your ebay username
						 is "'.$ebayID.'". Unfortunately, we don\'t see any auctions
						 tied to your username. If your information is correct, please send us an email and we
						 can look into this. To update your upsername,
						 <a href="https://helmarbrewing.com/account/">click to add your ebay username on the account
						  info page.</a> Please note, in the future if you win any of our card auctions,
						  come back and we can import those auctions to your personal checklist!
						</p>';
					}else{
							print'

							Good news! We are able to add cards that you\'ve purchased from Helmar Brewing Company
							 and add them to your personal checklist! It looks like your ebay username is "'.$ebayID.'".
							 If your checklist is not up to date, you can click the button below to add all your purchased cards to your checklist.
							 <br><br>
							 The ebay summary was last updated on '.$ebaySummaryDate.' and our database shows you purchased '.$totalSummary.' card(s).
							 <br><br>
							 <a href="javascript:;" ><i class="fa fa-list" onclick="ebayImport(\''.$ebayID.'\')" > Import your ebay history to update your checklist</i></a>
							</p>';
						}
				}




		//	print '<div class="series_desc"><p><a href="/userchecklist/csv/"><i class="fa fa-download"></i> Download Your Personal Checklist</a></p></div>';
			if($checklistQuantity>0){
				print '<p><a href="csv/"><i class="fa fa-download" ></i> Download Your Personal Checklist</a></p>';
			}


			if($checklistQuantity+$wishlistQuantity==0){
				print '<p>You have no cards in your Checklist and Wishlist!
				<a href="https://helmarbrewing.com/artwork/">You can add cards by heading to the Artwork pages</a>.
				</p>';
			}else{
					// need to join several DB together - userchecklist, link to cardlist for card info, link to series for desc
									$R_cards = $db_main->query("

									SELECT
					userCardChecklist.quantity, userCardChecklist.wishlistQuantity, userCardChecklist.dateadded,
					cardList.series, cardList.cardnum, cardList.player, cardList.description, cardList.team,
					series_info.series_name, series_info.series_tag, series_info.sort

					FROM userCardChecklist
										LEFT JOIN cardList
											ON cardList.series = userCardChecklist.series
											AND cardList.cardnum = userCardChecklist.cardnum
										LEFT JOIN series_info
											ON userCardChecklist.series = series_info.series_tag

										WHERE userCardChecklist.quantity + userCardChecklist.wishlistQuantity > 0 AND userCardChecklist.userid ='".$user->id."'
										ORDER BY series_info.sort, userCardChecklist.cardnum
										"
									);

					            print'
					                    <table class="tables">
					                      <thead>
					                        <tr>

																		<th>Card Series</th>
					                          <th>Card Number</th>
					                          <th>Player</th>
					                          <th>Stance / Position</th>
					                          <th>Team</th>

					                          <th>Pictures</th>
																		<th>Checklist</th>
																		<th>Wishlist</th>
																		<th>Active Auction</th>
					                        </tr>
					                      </thead>
					                      <tbody>
					            ';

											// load active auctions in array
											$R_cards2 = $db_main->query("
											SELECT *
											FROM activeEbayAuctions
												"
											);
											$R_cards2->data_seek(0);
											$wishlist = array();

											while($card = $R_cards2->fetch_object()){

												$wishlist[$card->series_tag.'-'.$card->cardnum]['auctionID'] = $card->auctionID;

											}


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
					                  //      print'
					                  //          <td></td>
					                  //          <td></td>
					                  //      ';
					                    }else{

					                //        print'
					                  //          <td>'.$card->lastsold.'</td>
					                  //          <td>'.$card->maxSold.'</td>
					                  //      ';
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
					                    if( file_exists($_SERVER['DOCUMENT_ROOT'].$frontpic) || file_exists($_SERVER['DOCUMENT_ROOT'].$backpic) ){

					                        // print the front pic if exists
					                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$frontpic)){
					                            print'
																			<a href="'.$protocol.$site.'/'.$frontlarge.'" data-lightbox="'.$card->series.'_'.$card->cardnum.'" ><img src="'.$protocol.$site.$frontthumb.'"></a>
					                            ';
					                        }

					                        // insert space
					                        if( file_exists($_SERVER['DOCUMENT_ROOT'].$frontpic) && file_exists($_SERVER['DOCUMENT_ROOT'].$backpic) ){
					                            print'&nbsp;&nbsp;';
					                        }

					                        // print the back pic if exists
					                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$backpic)){
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


					            		/* add icon */
										if($card->quantity > 0){
											print '<td><a href="javascript:;"><i class="fa fa-check-square-o" onclick="checklist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="'.$card->cardnum.'_'.$card->series.'"></i></a></td>';
										} else{
											print '<td><a href="javascript:;" ><i class="fa fa-square-o" onclick="checklist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="'.$card->cardnum.'_'.$card->series.'"></i></a></td>';
										}

										if($card->wishlistQuantity > 0){
											print '<td><a href="javascript:;" ><i class="fa fa-check-square-o" onclick="wishlist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="WISH'.$card->cardnum.'_'.$card->series.'"></i></a></td>';
										} else{
											print '<td><a href="javascript:;" ><i class="fa fa-square-o" onclick="wishlist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="WISH'.$card->cardnum.'_'.$card->series.'"></i></a></td>';
										}

											// add ebay auction if active
												print '<td><a href="http://www.ebay.com/itm/'. $wishlist[$card->series_tag.'-'.$card->cardnum]['auctionID'].'/" target="_blank">'.$wishlist[$card->series_tag.'-'.$card->cardnum]['auctionID'].'</a></td>';

					            		/* end row */
					            		print '</tr>';

					                    $i++;
					                    $updated = $card->updatedate;
					                }
					                $R_cards->free();
													$R_cards2->free();
					            }else{
					                print'
					                    <tr><td colspan="8">could not get list of cards</td></tr>
					                ';
					            }
					            print'
					                      </tbody>
					                    </table>


					                </div>


					            ';

					      }
					}


		/* END code if user is logged in, but not paid subscription */
    }else{
		/* do this if user is not logged in */
		print'

		<p>
				Sorry, are not logged in! Please login to view your Helmar artwork checklist. If you are not a paid subscriber, sign up for a paid subscription so you can track your personal Helmar card artwork purchases!
		</p>

		';	}
}else{
		/* do this if user is not logged in */
		print '
		<p>
				Sorry, are not logged in! Please login to view your Helmar artwork checklist. If you are not a paid subscriber, sign up for a paid subscription so you can track your personal Helmar card artwork purchases!
		</p>';
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
						$('#' + c + '_' + s).removeClass('fa-square-o');
						$('#' + c + '_' + s).addClass('fa-check-square-o');
						location.reload();
				}else if(data.qty === '0'){
						$('#' + c + '_' + s).removeClass('fa-check-square-o');
						$('#' + c + '_' + s).addClass('fa-square-o');
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

<script>
function wishlist(s, c){
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "/artwork/ajax/wishlist/",
		{ series:s, cardnum:c },
        function( data ) {
			if(data.error === 0){
				if(data.qty === '1'){
						$('#WISH' + c + '_' + s).removeClass('fa-square-o');
						$('#WISH' + c + '_' + s).addClass('fa-check-square-o');
						location.reload();
				}else if(data.qty === '0'){
						$('#WISH' + c + '_' + s).removeClass('fa-check-square-o');
						$('#WISH' + c + '_' + s).addClass('fa-square-o');
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

<script>
function ebayImport(s){
    document.getElementById('fullscreenload').style.display = 'block';
    $.get(
        "ajax/",
		{ ebayID:s },
        function( data ) {
			if(data.error === 0){
		/*		if(data.qty === '1'){
						$('#' + c).removeClass('fa-square-o');
						$('#' + c).addClass('fa-check-square-o');
				}else if(data.qty === '0'){
						$('#' + c).removeClass('fa-check-square-o');
						$('#' + c).addClass('fa-square-o');
				} else {
					alert("else part...");
				}*/
				location.reload();
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
				document.getElementById('fullscreenload').style.display = 'none';
    });



}



</script>
