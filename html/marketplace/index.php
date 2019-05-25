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
/* <HEAD> */ $head='
<script src="https://cdn.ckeditor.com/4.9.1/standard/ckeditor.js"></script>
<script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery-1.12.4.js"></script>
<script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://helmarbrewing.com/js/jquery.dataTables.min.css">
'; // </HEAD>
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

		// get unique users, want the user who has the farthest end date (newest card listed)
		$R_cards = $db_main->query("
		    SELECT marketSale.userid, users.*
		    from marketSale
		    LEFT JOIN users ON users.userid = marketSale.userid
		    WHERE expired = 'N' and ".$user->id." <> marketSale.userid
		    GROUP BY marketSale.userid
		    ORDER BY max(endDate) DESC
		");


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
							<table id="selling" class="display compact">
						  <thead>
							<tr>
							<th>User</th>
							<th>Series</th>
							<th>Card Number</th>
							<th>Player</th>
							<th>Stance / Position</th>
							<th>Team</th>
							<th>Stock Pictures</th>
							<th>Seller Note</th>
							</tr>
						  </thead>
						  <tbody>
				';

			$R_cards->data_seek(0);
			while($card = $R_cards->fetch_object()){

				$R_cards2->data_seek(0);
				while($card2 = $R_cards2->fetch_object()){
					if($card->userid === $card2->userid){

						if($card->state == ""){
							if($greetings=$card->firstname == ""){
								$greetings='User';
							}else{
								$greetings=$card->firstname;
							}
							
						}else{
							if($greetings=$card->firstname == ""){
								$greetings='User from '.$card->state;
							}else{
								$greetings=$card->firstname.' from '.$card->state;
							}
							
						}

                        // define the pictures
						$frontpic = '/images/cardPics/'.$card2->series.'_'.$card2->cardnum.'_Front.jpg';
						$frontthumb = '/images/cardPics/thumb/'.$card2->series.'_'.$card2->cardnum.'_Front.jpg';
						$backpic  = '/images/cardPics/'.$card2->series.'_'.$card2->cardnum.'_Back.jpg';
						$backthumb  = '/images/cardPics/thumb/'.$card2->series.'_'.$card2->cardnum.'_Back.jpg';
						$frontlarge = '/images/cardPics/large/'.$card2->series.'_'.$card2->cardnum.'_Front.jpg';
						$backlarge  = '/images/cardPics/large/'.$card2->series.'_'.$card2->cardnum.'_Back.jpg';

						print'
							<tr >
								<td class="item-for-sale" data-send-to-user-id="'.$card->userid.'">'.$greetings.'</td>
								<td>'.$card2->series.'</td>
								<td>'.$card2->cardnum.'</td>
								<td>'.$card2->player.'</td>
								<td>'.$card2->description.'</td>
								<td>'.$card2->team.'</td>
                                <td>
                        ';

	                    //check if either pic exists
	                    if(file_exists($_SERVER['DOCUMENT_ROOT'].$frontlarge) || file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge) ){

	                        // print the front pic if exists
	                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$frontlarge)){
	                            print'
									<a href="'.$protocol.$site.'/'.$frontlarge.'" data-lightbox="'.$card->series.'_'.$card->cardnum.'" >
                                        <img src="'.$protocol.$site.$frontthumb.'">
                                    </a>
	                            ';
	                        }

	                        // insert space
	                        if( file_exists($_SERVER['DOCUMENT_ROOT'].$frontlarge) && file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge) ){
	                            print'&nbsp;&nbsp;';
	                        }

	                        // print the back pic if exists
	                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge)){
	                            print'
								    <a href="'.$protocol.$site.'/'.$backlarge.'" data-lightbox="'.$card->series.'_'.$card->cardnum.'" >
                                        <img src="'.$protocol.$site.$backthumb.'">
                                    </a>
	                            ';
	                        }

	                    // neither pic exists print message instead
	                    }else{
	                        print'<i>no picture</i>';
	                    }

						print '
                                </td>
							    <td>'.$card2->card_note.'</td>
                            </tr>
                        ';
					}
				}
			}
			$R_cards->free();
			$R_cards2->free();
            unset($R_cards, $R_cards2);
		}else{
			print'<tr><td colspan="7">could not get list of cards</td></tr>';
	    }
		print'
		        </tbody>
			</table>
		';


        print '<p></p><p></p>';


		// buying
        print '
            <h2>Marketplace Items Wanted</h2>
		    <p>The following users are interested in the items listed below. Click on the items if you would like to trade or reach out to that user!</p>
			    <table id="wishlist" class="display compact">
				    <thead>
						<tr>
							<th>User</th>
							<th>Series</th>
							<th>Card Number</th>
							<th>Player</th>
							<th>Stance / Position</th>
							<th>Team</th>
							<th>Pictures</th>
							</tr>
						  </thead>
						  <tbody>
				';



		// get unique users, want the user who has the farthest end date (newest card listed)
		$R_cards = $db_main->query("
		    SELECT marketWishlist.userid, users.*
		    from marketWishlist
		    LEFT JOIN users ON users.userid = marketWishlist.userid
		    where expired = 'N' and $user->id <> marketWishlist.userid
		    GROUP BY marketWishlist.userid
		    ORDER BY max(endDate) DESC
		");


		if($R_cards !== FALSE){

    		// grab card info --- need to left join on the card list table on series and card num, sory by series, card num
    		$R_cards2 = $db_main->query("
    		    SELECT marketWishlist.*, cardList.*
    		    FROM marketWishlist
    		    LEFT JOIN cardList ON marketWishlist.series = cardList.series and marketWishlist.cardnum = cardList.cardnum
    		    WHERE marketWishlist.expired = 'N' and $user->id <> marketWishlist.userid
    		");

			$R_cards->data_seek(0);
			while($card = $R_cards->fetch_object()){

				$R_cards2->data_seek(0);
				while($card2 = $R_cards2->fetch_object()){
					if($card->state == ""){
						if($greetings=$card->firstname == ""){
							$greetings='User';
						}else{
							$greetings=$card->firstname;
						}
						
					}else{
						if($greetings=$card->firstname == ""){
							$greetings='User from '.$card->state;
						}else{
							$greetings=$card->firstname.' from '.$card->state;
						}
						
					}

					if($card->userid === $card2->userid){

                        // define the pictures
						$frontpic = '/images/cardPics/'.$card2->series.'_'.$card2->cardnum.'_Front.jpg';
						$frontthumb = '/images/cardPics/thumb/'.$card2->series.'_'.$card2->cardnum.'_Front.jpg';
						$backpic  = '/images/cardPics/'.$card2->series.'_'.$card2->cardnum.'_Back.jpg';
						$backthumb  = '/images/cardPics/thumb/'.$card2->series.'_'.$card2->cardnum.'_Back.jpg';
						$frontlarge = '/images/cardPics/large/'.$card2->series.'_'.$card2->cardnum.'_Front.jpg';
						$backlarge  = '/images/cardPics/large/'.$card2->series.'_'.$card2->cardnum.'_Back.jpg';

						print'
							<tr class="item-wanted" data-send-to-user-id="'.$card->userid.'">
							    <td>'.$greetings.'</td>
							    <td>'.$card2->series.'</td>
							    <td>'.$card2->cardnum.'</td>
								<td>'.$card2->player.'</td>
								<td>'.$card2->description.'</td>
								<td>'.$card2->team.'</td>
								<td>
                        ';


						//check if either pic exists
						if(file_exists($_SERVER['DOCUMENT_ROOT'].$frontlarge) || file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge) ){

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
							print'<i>no picture</i>';
						}

						print '
                                </td>
                            </tr>
                        ';
					}
				}
			}
			$R_cards->free();
			$R_cards2->free();

		}else{
			print'
				<tr><td colspan="6">could not get list of cards</td></tr>
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

print'</div>';
?>


<div class="modal-holder" id="user-to-user">
    <div class="modal-wrap">
        <div class="modal">
            <h1>Helmar Brewing Marketplace Messaging</h1>
            <fieldset>
                <label for="name">From Name</label>
                <input id="name" type="text">
                <label>From Email</label>
                <input id="email" type="text" disabled>
                <p><a href="/account/">Need to update your email?</a> <a href="/account/">Account Settings</a></p>
                <label>To</label>
                <input id="to" type="text" disabled>
                <label for="subject">Subject</label>
                <input id="subject" type="text">
            </fieldset>
            <p id="error_no_message_body" class="inline_error">You must enter a message.</p>
            <textarea id="message_body"></textarea>
            <div class="disclaimer">
                <input id="disclaimer" type="checkbox">
                <p>You understand that you are contacting another member via the Helmar Brewing Marketplace because you have an interest in a card listed on the Marketplace. You will be respectful for other members and not send obscene or explicit communication, else your account may be terminated. After the communication is sent via the Marketplace, the receiving user may or may not respond to you. We have policies in place to disallow spamming of communication. The receiving party will have your email listed in your helmarbrewing.com account. If they choose to respond, you are free to communicate with the other party as you wish. This will take place outside of the helmarbrewing.com website and you will not hold Helmar Brewing responsible for any issues that may occur outside of the helmarbrewing.com website.</p>
                <p>Helmar Brewing recommends safe trading practices.</p>
                <p>In order to send your message, you must agree to the above terms.</p>
            </div>
            <div class="buttons">
                <button id="send" disabled>Send</button>
                <button id="cancel">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready( function(){
        $('#disclaimer').on('change', function(){
            userToUserAcceptDisclaimer();
        });
        $('#cancel').on('click', function(){
            userToUserCancel();
        });
    });
    $(document).ready( function(){
        $('.item-for-sale').on('click', function(){
            var send_to_user_id = this.getAttribute('data-send-to-user-id');
            userToUser(send_to_user_id);
        });
    });
    $(document).ready( function(){
        $('.item-wanted').on('click', function(){
            var send_to_user_id = this.getAttribute('data-send-to-user-id');
            userToUser(send_to_user_id, true);
        });
    });
</script>
<script>
    CKEDITOR.replace( 'message_body', {
        toolbar: [
            ['Bold', 'Italic']
        ]
    });
</script>
<script>
    $(document).ready(function() {
        $('#selling').DataTable({
          "columns": [
            { "width": "10%" },
            { "width": "11%" },
            { "width": "7%" },
            { "width": "19%" },
            { "width": "13%" },
        	{ "width": "12%" },
        	{ "width": "12%" },
        	{ "width": "16%" }
          ]
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#wishlist').DataTable({
          "columns": [
            { "width": "10%" },
            { "width": "13%" },
            { "width": "7%" },
            { "width": "23%" },
            { "width": "15%" },
        	{ "width": "15%" },
        	{ "width": "17%" }
          ]
        });
    });
</script>




<?php
/* FOOTER */ require('layout/footer1.php');
$db_auth->close();
$db_main->close();
?>
