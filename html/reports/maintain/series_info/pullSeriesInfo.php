<?php

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ if($use_https === TRUE){if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;}}

/* WHICH DATABASES DO WE NEED */
$db2use = array(
    'db_auth' => TRUE,
    'db_main'	=> TRUE
);
//

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
require_once('classes/phnx-user.class.php');
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

// check user subscription status
$user->checksub();


$errors = array(); // array to hold validation errors
$data = array(); // array to pass back data

$series = $_POST['series'];


if($user->login() !== 1){
    $errors['message'] .= "You need to be logged in to use this.\n";
}

// get current qty
$result = mysqli_query($db_main, "SELECT userType FROM users WHERE userid='".$user->id."' LIMIT 1");

$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

$user_type = $row["userType"];

//$errors['message'] .= "User Type: ".mysqli_num_rows($result)."\n";

if($user_type !== "admin"){
    $errors['message'] .= "You need to be an administrator to use this.\n";
}

// validate the variables ======================================================

// if any of these variables don't exist, add an error to our $errors array
if (empty($series)) $errors['message'] .= "Series needs to be selected.\n";


//$errors['message'] .= $series."\n";



// return a response ===========================================================
// if there are any errors in our errors array, return a success boolean of false
if (!empty($errors))
{

    $errors['message'] = "Here are the following errors:\n".$errors['message'];
    // if there are items in our errors array, return those errors
    $data['success'] = false;
    $data['errors'] = $errors;
}
else
{

    // if there are no errors process our form, then return a message
    // DO ALL YOUR FORM PROCESSING HERE
    // THIS CAN BE WHATEVER YOU WANT TO DO (LOGIN, SAVE, UPDATE, WHATEVER)




    // grab Series
    $result = mysqli_query($db_main, "SELECT max(sort) as maxSort FROM series_info WHERE series_status='active' LIMIT 1");
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $sortVal = $row["maxSort"] + 1;

    if($series==="new"){
        // variables are mostly empty for a new series
        $sName = "";
        $sID = "";
        $sTag = "";
        $eTag = "";
        $cover = "";
        $front = "";
        $back = "";
        $desc = "<p></p>";
        $sStatus = "active";
        $liveStatus = "live";
    }else{
        // for existing series, load variables with existing  data

        $result = mysqli_query($db_main, "SELECT * FROM series_info WHERE series_id='".$series."' LIMIT 1");
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        $sName = $row["series_name"];
        $sID = $series;
        $sTag = $row["series_tag"];
        $eTag = $row["ebay_tag"];
        $cover = $row["cover_img"];
        $front = $row["front_img"];
        $back = $row["back_img"];
        $desc = $row["series_desc"];
        $sortVal = $row["sort"];
        $sStatus = $row["series_status"];
        $liveStatus = $row["live_status"];

    }





    $data['success'] = true;
    $data['sName'] = $sName;
    $data['sID'] = $sID;
    $data['sTag'] = $sTag;
    $data['eTag'] = $eTag;
    $data['cover'] = $cover;
    $data['front'] = $front;
    $data['back'] = $back;
    $data['desc'] = $desc;
    $data['sStatus'] = $sStatus;
    $data['liveStatus'] = $liveStatus;
    $data['sortVal'] = $sortVal;


}

// return all our data to an AJAX call
echo json_encode($data);

$db_auth->close();
$db_main->close();

?>
