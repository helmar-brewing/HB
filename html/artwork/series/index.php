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
require_once('libraries/stripe/Stripe.php');
Stripe::setApiKey($apikey['stripe']['secret']);

/* PAGE VARIABLES */
$currentpage = 'artwork/';

// Card Series Variables
$series_id = $_GET['series'];
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
    <h4>Artwork</h4>
    <h1>'.$series_name.'</h1>
    <div class="series_images">
        <img src="'.$front_img.'" />';

		if($back_img!=""){
					print '<img src="'.$back_img.'" />';
			}else{
        // do nothing if no back image
			}


print'    </div>
	<div class="series_desc">'.$series_desc.'</div>';


 /* setup code if 1) user logged in with no subscription, 2) user logged in with subscription, 3) user not logged in */

 if(isset($user)){
    if( $user->login() === 1 || $user->login() === 2 ){
		/* do this code if user is logged in */

		print '<div class="series_desc"><p><a href="/artwork/csv/'.$series_id.'"><i class="fa fa-download"></i> Download Card List</a></p></div>';

    $R_cards = $db_main->query("
		SELECT cardList.*, userCardChecklist.quantity FROM cardList
		LEFT JOIN userCardChecklist
			ON cardList.series = userCardChecklist.series
			AND cardList.cardnum = userCardChecklist.cardnum
			AND userCardChecklist.userid = '".$user->id."'
		WHERE cardList.series = '".$series_tag."'"
	);

      // if user does NOT have subscription:
      if($user->subscription['status'] != 'active') {
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

      }else{
        // do this if the user subscription = active

            print'
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
                    $frontthumb = '/images/cardPics/thumb/'.$card->series.'_'.$card->cardnum.'_Front_small.jpg';
                    $backpic  = '/images/cardPics/'.$card->series.'_'.$card->cardnum.'_Back.jpg';
                    $backthumb  = '/images/cardPics/thumb/'.$card->series.'_'.$card->cardnum.'_Back_small.jpg';

                    //check if either pic exists
                    if( file_exists($_SERVER['DOCUMENT_ROOT'].$frontpic) || file_exists($_SERVER['DOCUMENT_ROOT'].$backpic) ){

                        // print the front pic if exists
                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$frontpic)){
                            print'
                                <a href="http://www.helmarbrewing.com/'.$frontpic.'" data-lightbox="'.$card->series.'_'.$card->cardnum.'" ><img src="http://www.helmarbrewing.com/'.$frontthumb.'"></a>
                            ';
                        }

                        // insert space
                        if( file_exists($_SERVER['DOCUMENT_ROOT'].$frontpic) && file_exists($_SERVER['DOCUMENT_ROOT'].$backpic) ){
                            print'&nbsp;&nbsp;';
                        }

                        // print the back pic if exists
                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$backpic)){
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


            		/* add icon */
								if($card->quantity > 0){
									print '<td><img src="'.$protocol.$site.'/img/delete-icon.png" alt="'.$card->series.'-'.$card->cardnum.'" data-series="'.$card->series.'" data-cardnum="'.$card->cardnum.'" class="card-click"></td>';
								} else{
									print '<td><img src="'.$protocol.$site.'/img/add_new_icon.gif" alt="'.$card->series.'-'.$card->cardnum.'" data-series="'.$card->series.'" data-cardnum="'.$card->cardnum.'" class="card-click"></td>';
								}



//									print '<td><button type="button" onclick="checklist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="'.$card->cardnum.'">Remove Card</button></td>';
//								} else{
//									print '<td><button type="button" onclick="checklist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="'.$card->cardnum.'">Add Card</button></td>';
//								}

//								print '<td><i class="fa fa-trash-o" onclick="checklist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="'.$card->cardnum.'"></i></td>';
//							} else{
//								print '<td><i class="fa fa-user-plus" onclick="checklist(\''.$card->series.'\',\''.$card->cardnum.'\')" id="'.$card->cardnum.'"></i></td>';
//							}




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

      }









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

<script type="text/javascript">

$(".card-click").click(function(){
		var cardNum = $(this).data("cardnum");
		var series = $(this).data("series");
	//	var cardid = this.id;

//	alert("you made it");

		$.ajax({
              type: "GET",                                 // If you want to send information to the PHP file your calling, do you want it to be POST or GET. Just get rid of this if your not sending data to the file
            	url: "/artwork/ajax/checklist/index.php",
						//  url: "testAdd.php",                             // The location of the PHP file your calling
              data: "series=" + series + "&cardnum=" + cardNum,           // The information your passing in the variable1=value1&variable2=value2 pattern
              success: function( data ) {

			//	  console.log(data);  // message in php file had JSON variable
				var data = $.parseJSON(data);  // message in php file had JSON variable

				if( data.status == 'success' ) {
					//alert(data.message);
					if(data.qty === 1){
						$('#' + cardid).attr('src','delete-icon.png'); // update image picture
					}else{
						$('#' + cardid).attr('src','delete-icon.png');
					}
			//		document.getElementById("" + cardid + "").className = "class-delete";
			//		$('#' + cardid).addclass("card-delete");
				} else {
					alert(data.message);
				}
			}   // When you get the information, what to do with it. In this case, an alert
            });

      });


function checklist(s, c){
		$.ajax({
              type: "GET",
              url: "/artwork/ajax/checklist/",
              data: { series:s, cardnum:c },
              success: function( data ) {
				var data = $.parseJSON(data);  // message in php file had JSON variable

						if( data.status == 'success' ) {
								if(data.qty === 1){
									alert("qty = 1");
										document.getElementById(c).innnerHTML="Remove Card";
								}else if(data.qty === 0){
									alert("qty = 0");
									document.getElementById(c).innnerHTML="Add Card";
								}else{
									alert("noooo!!!!");
									//something really bad happened
								}
						} else {
							alert(data.message);
						} // end IF data success
			}   // end success function
          }); // end AJAX

    }; // end function checklist



function OLDchecklist(s, c){


	$.get(
					 "/artwork/ajax/checklist/index.php",
					{ series:s, cardnum:c },
					function( data ) {

						if( data.status == 'success' ) {

							// if(data.qty === '1')
							if(data.qty === 1){
								alert("qty = 1");
									document.getElementById(c).innnerHTML="Remove Card";
							}else if(data.qty === 0){
								alert("qty = 2");
								document.getElementById(c).innnerHTML="Add Card";
							}else{
								alert("noooo!!!!");
								//something really bad happened
							}

// old using fontawesome
	//						if(data.qty === 1){
//									$('#' + c).removeClass('fa fa-user-plus');
//									$('#' + c).addClass('fa fa-trash-o');
//							}else if(data.qty === 0){
//									$('#' + c).removeClass('fa fa-trash-o');
//									$('#' + c).addClass('fa fa-user-plus');



						} else {
							alert("else part...");
							alert(data.message);
						}, // this ends the function STATUSdata
					"json"
					} // this ends the FUNCTION


	.fail(function() {
		alert('There was an error. ref: ajax fail');
	}); // this ends the .GET and .fail function

	alert(data.message);
}



</script>
