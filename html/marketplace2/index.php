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
$currentpage = 'marketplace2/';

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
<script src="https://helmarbrewing.com/marketplace2/_marketplace.js"></script>
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


		// buying
		print '
		<div class="auctions">
            <h2 style="color:black">Marketplace Items <span id="type_label"></span></h2>
            <div style="display: inline-flexbox">
                <button onclick="setType('."'".'selling'."'".')" id="selling_btn">For Sale</button>
                <button onclick="setType('."'".'buying'."'".')" id="buying_btn">Wanted</button>
            </div>
            
		    <p id="type_message" style="text-align: left"></p>
				';

		print '
		    <div class="search-container" style="width: 100%;">
		        <div style="float: left" class="results_per_page">
		            <label style="color: black">Results per page: </label>
		            <select id="results-per-page" onchange="changePageSize()" style="width: 100%">
		                <option>16</option>
		                <option>32</option>
		                <option>64</option>
		                <option>All</option>
                    </select>
                </div>
		        <div style="float: right" class="search">
                    <label style="color:black">Search: </label>
                    <input type="text" placeholder="Player/Team/Series" id="search-query"/>
                </div>
                <div style="float: right;" class="filter_series">
                    <label style="color: black">Filter by Series:</label>
                    <select id="series_filter" style="width: 100%;" onchange="refreshCards(false)">
                        <option></option>
                    </select>
                </div>
		    </div>
		    
		    <div class="view-container" style="width: 100%; height: 30px">
		        <a style="float: right; font-size: 20px" id="view_type_btn" onclick="toggleViewType(event)" href="#"></a>
            </div>
		    
		    
		    <div class="auctions_container" id="auction_list_container">
		        <ul id="auction_list" style="display: flex; flex-wrap: wrap;">
		        </ul>
		        <div class="auction_list_table_container" style="overflow-x:scroll">
                    <table id="auction_list_table">
                        <thead>
                            <th style="width: 40px; text-align: center"><i class="fa fa-envelope-o" /></th>
                            <th>User</th>
                            <th>Series</th>
                            <th>Card Number</th>
                            <th>Player</th>
                            <th>Stance / Position</th>
                            <th>Team</th>
                            <th>Stock Pictures</th>
                            <th>User Note</th>
                        </thead>
                        <tbody id="auction_list_table_body">
                            
                        </tbody>
                    </table>
                </div>    
		        <p id="no_results">No results match your criteria</p>
            </div>
            <div id="auction_list_loading" style="text-align: center">
                <img src="https://cdn.lowgif.com/small/d35d94c490e598e3-loading-gif-transparent-loading-gif.gif" style="width: 50px; height: 50px;"/>
            </div>
            
            <!--<p id="auction_list_loading" style="display: block; margin: 0;">Loading Results...</p>-->
            <p style="margin: 1em;">Page <span id="result_size">0</span> of <span id="total_size">0</span></p>
            <center>
                <div id="pagination-container" style="margin: 1em">
                    
                </div>
            </center>
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

<div class="modal-holder" id="market-card-info">
    <div class="modal-wrap">
        <div class="modal">
            <h1>Card Information</h1>
            <fieldset>
                <div style="column-count: 2">
                    <div>
                        <label>Series</label>
                        <input id="series_name" type="text" disabled/>
                    </div>
                    <div>
                        <label>Card Number</label>
                        <input id="card_number" type="text" disabled/>
                    </div>
                </div>

                <label>Player</label>
                <input id="player_name" type="text" disabled>

                <label>Stance/Position</label>
                <input id="player_position" type="text" disabled>

                <label>Team</label>
				<input id="player_team" type="text" disabled>

                <div style="column-count: 2">
                    <div>
                        <label>Last Sold Date</label>
                        <input id="last_sold_date" type="text" disabled>
                    </div>
                    <div>
                        <label>Max eBay Sell Price</label>
                        <input id="max_ebay_price" type="text" disabled>
                    </div>
                </div>

            </fieldset>
            <div class="buttons">
                <button id="exit">Close Info</button>
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
		$('#exit').on('click', function(){
            exitCardInfo();
        });
    });
    $(document).ready( function(){

        changePageSize();

        setType("buying");
        setViewType("card");

        refreshCards();
        getSeriesNames();

        $('.item-for-sale').on('click', function(){
            var send_to_user_id = this.getAttribute('data-send-to-user-id');
            userToUser(send_to_user_id);
        });

        $('#search-query').on('keyup', function() {
            refreshCards();
        })
    });
</script>
<script>
    CKEDITOR.replace( 'message_body', {
        toolbar: [
            ['Bold', 'Italic']
        ]
    });
</script>

<style>

    .email_btn:hover {
        background-color: lightgrey;
    }

    .thumbnail {
        height: 50px;
        max-height: 50px;
        width: auto;
        margin: 5px;
    }

    th, td {
        color: black;
    }

    .artwork p {
            margin-bottom: 1em !important;
    }

    .artwork select {
        margin-bottom: 0 !important;
    }

    .inactive {
        background-color: gray !important;
        color: black !important;
    }

    #auction_list {
        margin: 0 !important;
        max-width: 100%;
    }

    select {
        height: 35px;
        border-color: #DDD;
        background-color: white;
        color: #757575;
    }

    .nameplate {
        cursor: pointer;
    }

    .nameplate:hover {
        background-color: #b8b7b7;
    }

    #pagination-container {
        display: inline-block;
    }

    #pagination-container a {
        color: black;
        float: center;
        padding: 8px 16px;
        text-decoration: none;
        text-align:center;
    }

    #pagination-container a.active {
        background-color: #4CAF50;
        color: white;
    }

    #pagination-container a:hover:not(.active) {background-color: #ddd;}

    @media only screen and (max-width: 1000px) {
        .grid_item {
            width: 50% !important;
        }
    }

    .auctions {
        width: 100%;
    }

    @media only screen and (max-width: 600px) {

        .search-container {
            height: 178px;
        }

        .artwork {
            width: 100%;
        }

        .grid_item {
            width: 100% !important;
        }

        .search {
            width: 53%;
        }

        .results_per_page {
            width: 45%;
        }

        .filter_series {
            width: 100%;
        }

        table {
            width: 800px;
        }
    }

    @media only screen and (min-width: 600px) {

        .search-container {
            height: 90px;
        }

        .artwork {
            padding-left: 10em;
            padding-right: 10em;
        }


        .search {
            width: 30%;
        }

        .results_per_page {

        }

        .filter_series {
            width: 30%;
            margin-right: 1em;
        }
    }
</style>

<?php
/* FOOTER */ require('layout/footer1.php');
$db_auth->close();
$db_main->close();
?>
