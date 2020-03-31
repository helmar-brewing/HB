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
$currentpage = 'reports/maintain/series_order/';

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
            // https://www.codexworld.com/drag-drop-images-reorder-using-jquery-ajax-php-mysql/

            print '
            <link rel="stylesheet" href="style.css">
            <script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery-1.12.4.js"></script>
            <script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery.dataTables.min.js"></script>
            <link rel="stylesheet" type="text/css" href="https://helmarbrewing.com/js/jquery.dataTables.min.css">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
            <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
            



            
            <div class="artwork">
            <h4><a href="' . $protocol . $site . '/' . 'reports/">Reports</a> > <a href="' . $protocol . $site . '/' . 'reports/maintain/">Maintenance</a> > Series Order</h4>   

            <div>
            <h2>Series Order Maintenance</h2>              
            <p></p>
            <p>Put the cards in the order you want the series displayed on the Artwork main page</p>  
            </div>';

            

            print '
            <div class="container">
            <a href="javascript:void(0);" class="reorder_link" id="saveReorder">reorder photos</a>
            <div id="reorderHelper" class="light_box" style="display:none;">1. Drag photos to reorder.<br>2. Click "Save Reordering" when finished.</div>
            <div class="gallery">
            <ul class="reorder_ul reorder-photos-list">
            ';


            $series_sql = $db_main->query("SELECT * FROM series_info ORDER BY series_status ASC, sort ASC");
           // $series_sql = $db_main->query("SELECT * FROM series_info WHERE sort>0 AND series_status <> 'discontinued' AND live_status = 'live' ORDER BY sort ASC");
            $series_sql->data_seek(0);
		    while($seriesinfo = $series_sql->fetch_object()){

                $series_id = $seriesinfo->series_id;
				$series_name = $seriesinfo->series_name;
                $cover_img = $protocol.$site.'/'.$seriesinfo->cover_img;

                if ($seriesinfo->series_status <> "discontinued"){
                    $serStatus = "Active Series";
                }else{
                    $serStatus = "Discontinued Series";
                }

                if ($seriesinfo->live_status === "live"){
                    $liveStatus = "Visible";
                }else{
                    $liveStatus = "NOT VISIBLE";
                }
                

                print'
                <li id="image_li_'.$series_id.'" class="ui-sortable-handle">
                    <a href="javascript:void(0); style="float:none;"" class="image_link"  >
                        <img src="'.$cover_img.'" alt="">
                        <tag>'.$serStatus.' & '.$liveStatus.'</tag>
                    </a>
                </li>
            ';

            }

        print '
            </ul>
        </div>
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

<script type="text/javascript">
$(document).ready(function(){
    $('.reorder_link').on('click',function(){
        $("ul.reorder-photos-list").sortable({ tolerance: 'pointer' });
        $('.reorder_link').html('save reordering');
        $('.reorder_link').attr("id","saveReorder");
        $('#reorderHelper').slideDown('slow');
        $('.image_link').attr("href","javascript:void(0);");
        $('.image_link').css("cursor","move");
        
        $("#saveReorder").click(function( e ){
            if( !$("#saveReorder i").length ){
                $(this).html('').prepend('<img src="refresh-animated.gif"/>');
                $("ul.reorder-photos-list").sortable('destroy');
                $("#reorderHelper").html("Reordering Photos - This could take a moment. Please don't navigate away from this page.").removeClass('light_box').addClass('notice notice_error');
                
                var h = [];
                $("ul.reorder-photos-list li").each(function() {
                    h.push($(this).attr('id').substr(9));
                });

               // window.alert(h);
                
                $.ajax({
                    type: "POST",
                    url: "orderUpdate.php",
                    data: {ids: "" + h + ""},
                    success: function(){
                     //   window.alert(h);
                        window.location.reload();
                    }
                });	
                return false;
            }	
            e.preventDefault();
        });
    });
});
</script>
