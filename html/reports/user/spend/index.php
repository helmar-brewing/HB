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
$currentpage = 'reports/user/';

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




 /* setup code if 1) user logged in with no subscription, 2) user logged in with subscription, 3) user not logged in */

 if(isset($user)){
    if( $user->login() === 1 || $user->login() === 2 ){
		/* do this code if user is logged in */

		// grab userType
        $R_cards2 = $db_main->query("
        SELECT userType
        FROM users
        WHERE userid ='".$user->id."'
            "
        );
        $R_cards2->data_seek(0);
        while($card = $R_cards2->fetch_object()){
            $userType = $card->userType;
        }
        $R_cards2->free();

        if ($userType === 'admin') {
            // where all the code goes if you're admin!



            print'

            <script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery-1.12.4.js"></script>
            <script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery.dataTables.min.js"></script>
            <link rel="stylesheet" type="text/css" href="https://helmarbrewing.com/js/jquery.dataTables.min.css">
            
            
            <div class="artwork">
                <h4><a href="'.$protocol.$site.'/'.'reports/">Reports</a> > <a href="'.$protocol.$site.'/'.'reports/user/">User Reports</a> > User Spend</h4>   

                <div>
                <h2>User Spend Report</h2>              
                <p></p>  
                </div>';

                print '<div><p><a href="'.$protocol.$site.'/'.'reports/user/spend/csv/"><i class="fa fa-download"></i> Download User Report</a></p></div>';

                  
            // run the sql code to get user data

            print'<div>';  

            $R_cards2 = $db_main->query("
            SELECT ebayID, buyerName, sum(auctionAmount) as SumAmount
            FROM completed_auctions
            GROUP BY ebayID, buyerName
            ORDER BY Sum(auctionAmount) DESC
                "
            );
            $R_cards2->data_seek(0);

            print '<table>
            <tr>
                <th>eBay ID</th>
                <th>Name</th>
                <th>Amount</th>
            </tr>';


            while($card = $R_cards2->fetch_object()){

            print '<tr>';
            print '<td>'.$card->ebayID.'</td>';
            print '<td>'.$card->buyerName.'</td>';
            print '<td>$ '.number_format($card->SumAmount,2).'</td>';
            print '</tr>';
            }

            print '</table>';

                $R_cards2->free();
            print'</div>';  

            // end report code



                print'    </div>
            ';

            // close out db 


        } else{
            // not admin
            print '<meta http-equiv="Refresh" content="0; url=https://helmarbrewing.com/">';
        }



		/* END code if user is logged in, but not paid subscription */
    }else{
		/* do this if user is not logged in */
		print '<meta http-equiv="Refresh" content="0; url=https://helmarbrewing.com/">';
	}
}else{
		/* do this if user is not logged in */
		print '<meta http-equiv="Refresh" content="0; url=https://helmarbrewing.com/">';
}




/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>