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
$currentpage = 'marketplace/';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

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
			<h4>Marketplace</h4>
			<h1>Helmar Marketplace</h1>
					<p>Welcome to the Helmar Brewing Marketplace. The Marketplace is where you can reach out to other users to buy, sell, and trade Helmar Brewing cards!</p>
		';

/* setup code if 1) user logged in with no subscription, 2) user logged in with subscription, 3) user not logged in */

if(isset($user)){
    if( $user->login() === 1 || $user->login() === 2 ){
		/* do this code if user is logged in */


		// selling


		// get unique users, want the user who has the farthest end date (newest card listed)
		$R_cards = $db_main->query("
		SELECT marketSale.userid, users.*
		from marketSale
		LEFT JOIN users ON users.userid = marketSale.userid
		WHERE expired = 'N' and ".$user->id." <> marketSale.userid
		GROUP BY marketSale.userid
		ORDER BY max(endDate) DESC
		"
		);


		if($R_cards !== FALSE){

		// grab card info --- need to left join on the card list table on series and card num, sory by series, card num
		$R_cards2 = $db_main->query("
		SELECT marketSale.*, cardList.*
		FROM marketSale
		LEFT JOIN cardList ON marketSale.series = cardList.series and marketSale.cardnum = cardList.cardnum
		WHERE marketSale.expired = 'N' and $user->id <> marketSale.userid
		"
		);


		print '<h2>Marketplace Items for Sale</h2>
		<p>The following users have items listed for sale on the helmar market place. Click on the items you\'re interested in to reach out to that user!</p>';

				print'
							<table>
						  <thead>
							<tr>
							<th>Card Number</th>
							<th>Player</th>
							<th>Stance / Position</th>
							<th>Team</th>
							<th>x</th>
							<th>x</th>
							<th>Pictures</th>
							</tr>
						  </thead>
						  <tbody>
				';

				$R_cards->data_seek(0);
				while($card = $R_cards->fetch_object()){

					print'
						<tr><td colspan="7" style="background: #f2f2f2;">'.strtoupper(substr($card->firstname,0,1)).' from '.$card->state.'</td></tr>
					';

					$R_cards2->data_seek(0);
					while($card2 = $R_cards2->fetch_object()){
						if($card->userid === $card2->userid){
							print'
								<tr>
									<td>'.$card2->cardnum.'</td>
									<td>'.$card2->player.'</td>
									<td>'.$card2->description.'</td>
									<td>'.$card2->team.'</td>

									<td>x1</td>
									<td>x2</td>
									<td>pcitures</td>
								';
						}

					}


						/* end row */
						print '</tr>';

					}


					$R_cards->free();
					$R_cards2->free();
				}else{
					print'
						<tr><td colspan="7">could not get list of cards</td></tr>
					';
				}
				print'
						  </tbody>
						</table>
						';




print '<p></p><p></p>';


			// buying


		// get unique users, want the user who has the farthest end date (newest card listed)
		$R_cards = $db_main->query("
		SELECT marketWishlist.userid, users.*
		from marketWishlist
		LEFT JOIN users ON users.userid = marketWishlist.userid
		where expired = 'N' and $user->id <> marketWishlist.userid
		GROUP BY marketWishlist.userid
		ORDER BY max(endDate) DESC
		"
		);


		if($R_cards !== FALSE){

		// grab card info --- need to left join on the card list table on series and card num, sory by series, card num
		$R_cards2 = $db_main->query("
		SELECT marketWishlist.*, cardList.*
		FROM marketWishlist
		LEFT JOIN cardList ON marketWishlist.series = cardList.series and marketWishlist.cardnum = cardList.cardnum
		WHERE marketWishlist.expired = 'N' and $user->id <> marketWishlist.userid
		"
		);


		print '<h2>Marketplace Items Wanted</h2>
		<p>The following users are interested in the items listed below. Click on the items if you would like to trade or reach out to that user!</p>';

				print'
							<table>
						  <thead>
							<tr>
							<th>Card Number</th>
							<th>Player</th>
							<th>Stance / Position</th>
							<th>Team</th>
							<th>x</th>
							<th>x</th>
							<th>Pictures</th>
							</tr>
						  </thead>
						  <tbody>
				';

				$R_cards->data_seek(0);
				while($card = $R_cards->fetch_object()){

					print'
						<tr><td colspan="7" style="background: #f2f2f2;">'.strtoupper(substr($card->firstname,0,1)).' from '.$card->state.'</td></tr>
					';

					$R_cards2->data_seek(0);
					while($card2 = $R_cards2->fetch_object()){
						if($card->userid === $card2->userid){
							print'
								<tr>
									<td>'.$card2->cardnum.'</td>
									<td>'.$card2->player.'</td>
									<td>'.$card2->description.'</td>
									<td>'.$card2->team.'</td>

									<td>x1</td>
									<td>x2</td>
									<td>pcitures</td>
								';
						}

					}


						/* end row */
						print '</tr>';

					}


					$R_cards->free();
					$R_cards2->free();
				}else{
					print'
						<tr><td colspan="7">could not get list of cards</td></tr>
					';
				}
				print'
						  </tbody>
						</table>
						';

		/* END code if user is logged in, but not paid subscription */
    }else{
		/* do this if user is not logged in */
		print 'You must be logged in to use the Marketplace. You can log in or sign up for a free account today!';
	}
}else{
		/* do this if user is not logged in */
		print 'You must be logged in to use the Marketplace. You can log in or sign up for a free account today!';
}

print'
	</div>
    <div class="modal-holder" id="user-to-user">
        <div class="modal-wrap">
            <div class="modal user-to-user">
                <label>From Email</label>
                <input id="email" type="text" disabled>
                <p><a href="/account/">Update your email</a></p>
                <label for="name">From Name</label>
                <input id="name" type="text">
                <label for="subject">Subject</label>
                <input id="subject" type="text">
                <textarea id="message_body"></textarea>
                <input id="disclaimer" type="checkbox">
                <p>Disclaimer text goes here.</p>
                <button id="send" disabled>Send</button>
                <button id="cancel">Cancel</button>
            </div>
        </div>
    </div>
';





/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
