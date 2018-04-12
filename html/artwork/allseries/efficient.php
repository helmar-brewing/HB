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
<script language="javascript" type="text/javascript" src="jquery-1.12.4.js"></script>
<script language="javascript" type="text/javascript" src="jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="jquery.dataTables.min.css">


<div class="artwork">
    <h4>Artwork</h4>
    <h1>All Card Series</h1>';







 /* setup code if 1) user logged in with no subscription, 2) user logged in with subscription, 3) user not logged in */



		print'
			<div class="series_desc">Below is the Helmar Brewing checklist for all of our cards.
			You can use filters to display the cards you want and you can even download the checklist.
			To get more information regarding each series, <a href="'.$protocol.$site.'/artwork/">please visit the individual series pages</a>.</div>';

print '<br>Before all query<br>';
echo date('m/d/Y h:i:s a', time());
print '<br>';

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
		print '<br><u>After active, before all cards query<br>';
		echo date('m/d/Y h:i:s a', time());
		print '</u><br>';

		print '<div class="series_desc"><p><a href="/artwork/csvall/"><i class="fa fa-download"></i> Download Complete Card List</a></p></div>';


	$R_cards = $db_main->query("
	SELECT cardList.*, series_info.series_name FROM cardList
	LEFT JOIN series_info
			ON cardList.series = series_info.series_tag"
);

        // do this if the user subscription = active
				// thanks internet!
				// http://www.javascripttoolbox.com/lib/table/examples.php
				//

				print '<br><u>After all cards query<br>';
				echo date('m/d/Y h:i:s a', time());
				print '</u><br>';

            print'
						<table id="example" class="display" width="100%" cellspacing="0">
                      <thead>
                        <tr>
													<th>Series</th>
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
														<td>'.$card->series_name.'</td>
                            <td>'.$card->cardnum.'</td>
                            <td>'.$card->player.'</td>
                            <td>'.$card->description.'</td>
                            <td>'.$card->team.'</td>
                    ';




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


						print '<br>End Page query<br>';
						echo date('m/d/Y h:i:s a', time());
						print '<br>';

print'
	</div>
';




/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>

<script>
$(document).ready(function() {
    $('#example').DataTable();
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
