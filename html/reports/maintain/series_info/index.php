<?php
ob_start();

/* ROOT SETTINGS */
require ($_SERVER['DOCUMENT_ROOT'] . '/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */
forcehttps();

/* WHICH DATABASES DO WE NEED */
$db2use = array(
    'db_auth' => true,
    'db_main' => true
);

/* GET KEYS TO SITE */
require ($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
require_once ('classes/phnx-user.class.php');
require_once ('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);

/* PAGE VARIABLES */
$currentpage = 'reports/maintain/series_info/';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

// check user subscription status
$user->checksub();

ob_end_flush();
/* <HEAD> */
$head = ''; // </HEAD>
/* PAGE TITLE */
$title = 'Helmar Brewing Co';
/* HEADER */
require ('layout/header0.php');

/* HEADER */
require ('layout/header2.php');
/* HEADER */
require ('layout/header1.php');

/* setup code if 1) user logged in with no subscription, 2) user logged in with subscription, 3) user not logged in */

if (isset($user))
{
    if ($user->login() === 1 || $user->login() === 2)
    {
        /* do this code if user is logged in */

        // grab userType
        $R_cards2 = $db_main->query("
        SELECT userType
        FROM users
        WHERE userid ='" . $user->id . "'
            ");
        $R_cards2->data_seek(0);
        while ($card = $R_cards2->fetch_object())
        {
            $userType = $card->userType;
        }
        $R_cards2->free();

        if ($userType === 'admin')
        {
            // where all the code goes if you're admin!
            

            print '

            <script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery-1.12.4.js"></script>
            <script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery.dataTables.min.js"></script>
            <link rel="stylesheet" type="text/css" href="https://helmarbrewing.com/js/jquery.dataTables.min.css">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="getData.js"></script>

            
            <div class="artwork">
            <h4><a href="' . $protocol . $site . '/' . 'reports/">Reports</a> > <a href="' . $protocol . $site . '/' . 'reports/maintain/">Maintenance</a> > Series Maintenance</h4>   

            <div>
            <h2>Series Maintenance</h2>              
            <p></p>  
            </div>';

            // print selection box
            print '
                <select name="series" id="series">  
                    <option value="">Select a series...</option>
                    <option value="new">Add a new series</option>  ';

            // grab Series
            $R_cards2 = $db_main->query("
                    SELECT *
                    FROM series_info
                    ORDER BY series_name ASC
                        ");

            $R_cards2->data_seek(0);
            while ($card = $R_cards2->fetch_object())
            {
                print '<option value="' . $card->series_id . '">' . $card->series_name . '</option> 
                         ';
            }
            $R_cards2->free();

            print ' 
               </select> 
                ';

            // end selection box
            // print inputboxes
            

            print '
            <div>
            <p>Series ID: <input type="text" id="sID" value=""></p>
            <p>Series Name: <input type="text" id="sName" value=""></p>
            <p>Series Tag: <input type="text" id="sTag" value=""></p>
            <p>eBay Tag: <input type="text" id="eTag" value=""></p>
            <p>Cover Image: <input type="text" id="cover" value=""></p>
            <p>Front Image: <input type="text" id="front" value=""></p>
            <p>Back Image: <input type="text" id="back" value=""></p>
            <p>Series Description: <input type="text" id="desc" value=""></p>
            <p>Series Status: <input type="text" id="sStatus" value=""></p>
            <p>Sort Field: <input type="text" id="sortVal" value=""></p>
            <p>Live Status: <input type="text" id="liveStatus" value=""></p>
            </div>
            ';

        }
        else
        {
            // not admin
            print '<meta http-equiv="Refresh" content="0; url=https://helmarbrewing.com/">';
        }

        /* END code if user is logged in, but not paid subscription */
    }
    else
    {
        /* do this if user is not logged in */
        print '<meta http-equiv="Refresh" content="0; url=https://helmarbrewing.com/">';
    }
}
else
{
    /* do this if user is not logged in */
    print '<meta http-equiv="Refresh" content="0; url=https://helmarbrewing.com/">';
}

/* FOOTER */
require ('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>
