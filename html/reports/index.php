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
$currentpage = 'reports/';

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
                <h4>Reports > Main Menu</h4>
    

                <div>
                <h2><a href="'.$protocol.$site.'/'.'reports/user/">User Reports</a></h2>
                    <ul>
                        <li><a href="'.$protocol.$site.'/'.'reports/user/spend/">User Spend</a></li>
                    </ul>
                </div>

                <div>
                <h2><a href="'.$protocol.$site.'/'.'reports/maintain/">Maintenance</a></h2>
                    <ul>
                        <li><a href="'.$protocol.$site.'/'.'reports/maintain/series_info/">Series Info</a></li>
                        <li><a href="'.$protocol.$site.'/'.'reports/maintain/series_order/">Series Image Order of Display</a></li>
                    </ul>
                </div>



                </div>
            ';

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