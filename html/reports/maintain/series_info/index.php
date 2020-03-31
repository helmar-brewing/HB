<?php
ob_start();

/* ROOT SETTINGS */
require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

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
            //<link rel="stylesheet" type="text/css" href="https://helmarbrewing.com/js/jquery.dataTables.min.css">
            //<link rel="stylesheet" href=".css">
            print '

            <script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery-1.12.4.js"></script>
            <script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery.dataTables.min.js"></script>
            
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
            <link rel="stylesheet" href="style.css">
            ';

            print '
            
            <div class="artwork">
            <h4><a href="' . $protocol . $site . '/' . 'reports/">Reports</a> > <a href="' . $protocol . $site . '/' . 'reports/maintain/">Maintenance</a> > Series Maintenance</h4>   

            <div>
            <h2>Series Maintenance</h2>              
            <p></p>  
            </div>';

            // print selection box
            print '
                <select name="series" id="series">  
                    <option value="" selected disabled hidden>Select a series...</option>
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
                print '<option value="' . $card->series_id . '">' . $card->series_name . '</option>';
            }
            $R_cards2->free();

            print ' 
               </select> 
                ';

            // end selection box
            // print inputboxes
            print '<div class="formDiv" id="formDiv" style="display:none;">

            <form class="submission-form" id="series-submission-form" action="updateTest.php" method="POST" autocomplete="off">
                <label for="sName" title="Try not to use special characters!">Series Name</label>
                <input type="text" name="sName" id="sName" title="Try not to use special characters!">

                <label for="sTag">Series ID</label>
                <input type="text" name="sID" id="sID">

                <label for="eTag">eBay Tag</label>
                <input type="text" name="eTag" id="eTag">

                <label for="eTag">Series Tag</label>
                <input type="text" name="sTag" id="sTag">

                <label for="cover" title="Replace the XXXXXX with your card number">Cover Image</label>
                <input type="text" name="cover" id="cover" title="Replace the XXXXXX with your card number">

                <label for="front" title="Replace the XXXXXX with your card number">Front Image</label>
                <input type="text" name="front" id="front" title="Replace the XXXXXX with your card number">

                <label for="back" title="Replace the XXXXXX with your card number">Back Image</label>
                <input type="text" name="back" id="back" title="Replace the XXXXXX with your card number">

                <label for="desc" title="Make sure your text is between the <p></p> tags. use <br> to add line break">Series Description</label>
                <textarea name="desc" id="desc" title="Make sure your text is between the <p></p> tags. use <br> to add line break"></textarea>

                <label for="sStatus">Series Status</label>
                <select name="sStatus" id="sStatus"> 

                    <option value="active">active</option>
                    <option value="discontinued">discontinued</option>

                </select>

                <label for="sortVal" title="After updating, you can make sure it looks good on the series sort page">Sort Field</label>
                <input type="text" name="sortVal" id="sortVal" value=""  title="After updating, you can make sure it looks good on the series sort page" disabled>

                <label for="liveStatus">Live Status</label>
                <select name="liveStatus" id="liveStatus">
                
                    <option value="live">live</option>
                    <option value="hide">hide</option>

                </select>

                <input type="submit" value="Submit" id="sendBtn">

                <br>
				<a href="javascript:;" id="deleteText" style="display:none;">delete this series</a>

                
            </form>

            </div>';

            print '<p></p>';

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

<script>
$(document).ready(function(){
    $('#deleteText').click(function(){

        var seriesSelected = $("#series").val();

        if (confirm('Are you sure you want to delete this series (' + seriesSelected +')?')) {
            // get the form data
            // there are many ways to get this data using jQuery (you can use the class or id also)
            var formData = {
                series: seriesSelected,
            };
            // process the form
            $.ajax({
                    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                    url: 'deleteSeries.php', // the url where we want to POST
                    data: formData, // our data object
                    dataType: 'json', // what type of data do we expect back from the server
                    encode: true
                })
                // using the done promise callback
                .done(function(data) {

                    // here we will handle errors and validation messages
                    if(!data.success) {
                        // handle errors for name ---------------

                        alert(data.errors.message);
                        
                    } else {
                        // ALL GOOD! just show the success message!
                        //alert(data.message);

                       alert(data.message);
                       
                       location.reload();

                    }
                })
                // using the fail promise callback
                .fail(function(data) {
                    // show any errors

                });
        } else {
            // Do nothing!
          //  alert("You decided not to delete this!");
        }

        

        
    });
});
</script>


<script>
$(document).ready(function(){
    $('#series').change(function(){

        var seriesSelected = $("#series").val();

            // get the form data
            // there are many ways to get this data using jQuery (you can use the class or id also)
            var formData = {
                series: seriesSelected,
            };
            // process the form
            $.ajax({
                    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                    url: 'pullSeriesInfo.php', // the url where we want to POST
                    data: formData, // our data object
                    dataType: 'json', // what type of data do we expect back from the server
                    encode: true
                })
                // using the done promise callback
                .done(function(data) {

                    // here we will handle errors and validation messages
                    if(!data.success) {
                        // handle errors for name ---------------

                        alert(data.errors.message);

                        if(data.errors.sName) {
                            //$('#name-group').addClass('has-error'); // add the error class to show red input
                            //$('#name-group').append('<div class="help-block">' + data.errors.name + '</div>'); // add the actual error message under our input
                        }

                        $('html, body').animate({ scrollTop: 0 }, 'fast');
                        
                    } else {
                        // ALL GOOD! just show the success message!
                        //alert(data.message);

                        document.getElementById("sName").value = data.sName;
                        document.getElementById("sID").value = data.sID;
                        document.getElementById("sTag").value = data.sTag;
                        document.getElementById("eTag").value = data.eTag;
                        document.getElementById("cover").value = data.cover;
                        document.getElementById("front").value = data.front;
                        document.getElementById("back").value = data.back;
                        document.getElementById("desc").value = data.desc;
                        document.getElementById("sortVal").value = data.sortVal;

                        document.getElementById("sStatus").value = data.sStatus;
                        document.getElementById("liveStatus").value = data.liveStatus;

                        document.getElementById("formDiv").style.display = "block"; 

                        if(seriesSelected ==="new"){
                            document.getElementById("deleteText").style.display = "none"; 
                            document.getElementById("sendBtn").value = "Create"; 
                        }else{
                            document.getElementById("deleteText").style.display = "block";
                            document.getElementById("sendBtn").value = "Update"; 
                        }



                    }
                })
                // using the fail promise callback
                .fail(function(data) {
                    // show any errors

                });
    });
});
</script>


<script>
// TY: https://scotch.io/tutorials/submitting-ajax-forms-with-jquery
// https://github.com/scotch-io/ajax-forms-jquery

$(document).ready(function() {
	// process the form
	$('form').submit(function(event) {

        var seriesSelected = $("#series").val();
        var series_name = $("#sName").val();
        var series_id = $("#sID").val();
        var series_tag = $("#sTag").val();
        var ebay_tag = $("#eTag").val();
        var cover_image = $("#cover").val();
        var front_image = $("#front").val();
        var back_image = $("#back").val();
        var series_description = $("#desc").val();
        var series_status = $("#sStatus").val();
        var sort_field = $("#sortVal").val();
        var live_status = $("#liveStatus").val();


		// get the form data
		// there are many ways to get this data using jQuery (you can use the class or id also)
		var formData = {
            series: seriesSelected,
            sName: series_name,
            sID: series_id,
            sTag: series_tag,
            eTag: ebay_tag,
            cover: cover_image,
            front: front_image,
            back: back_image,
            desc: series_description,
            sStatus: series_status,
            sortVal: sort_field,
            liveStatus: live_status
		};
		// process the form
		$.ajax({
				type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
				url: 'updateSeries.php', // the url where we want to POST
				data: formData, // our data object
				dataType: 'json', // what type of data do we expect back from the server
				encode: true
			})
			// using the done promise callback
			.done(function(data) {

				// here we will handle errors and validation messages
				if(!data.success) {
					// handle errors for name ---------------

                    alert(data.errors.message);

					if(data.errors.sName) {
						//$('#name-group').addClass('has-error'); // add the error class to show red input
						//$('#name-group').append('<div class="help-block">' + data.errors.name + '</div>'); // add the actual error message under our input
					}

                    $('html, body').animate({ scrollTop: 0 }, 'fast');
					
				} else {
					// ALL GOOD! just show the success message!
					//$('form').append('<div class="alert alert-success">' + data.message + '</div>');
                   // alert('Data was updated! Your browser will refresh');
                   alert(data.message);

                   location.reload();
					// usually after form submission, you'll want to redirect
					// window.location = '/thank-you'; // redirect a user to another page
				}
			})
			// using the fail promise callback
			.fail(function(data) {
				// show any errors
				// best to remove for production
				console.log(data);
			});
		// stop the form from submitting the normal way and refreshing the page
		event.preventDefault();
	});
});
</script>

<script>

    
            $(document).on('keyup', '#sName', function(){

                var seriesSelected = $("#series").val();

                if(seriesSelected==="new"){


                    // add code to make sure the sID doesn't already exist!!

                    
                // alert($("#sName").val());
                    document.getElementById('sID').value = $("#sName").val().replace(/ /g, "-");
                    document.getElementById('sTag').value = $("#sName").val().replace(/ /g, "_");
                    document.getElementById('eTag').value = $("#sName").val();
                    document.getElementById('cover').value = "images/cardPics/large/" + document.getElementById('sID').value + "_XXXXXXX_Front.jpg";
                    document.getElementById('front').value = "images/cardPics/large/" + document.getElementById('sID').value + "_XXXXXXX_Front.jpg";
                    document.getElementById('back').value = "images/cardPics/large/" + document.getElementById('sID').value + "_XXXXXXX_Back.jpg";

                };

        });

</script>