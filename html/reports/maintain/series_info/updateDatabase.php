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

$series = str_replace("'", "", stripslashes($_POST['series']));
$sName = $_POST['sName'];
$sID = $_POST['sID'];
$sTag = $_POST['sTag'];
$eTag = $_POST['eTag'];
$cover = $_POST['cover'];
$front = $_POST['front'];
$back = $_POST['back'];
$desc = $_POST['desc'];
$sStatus = $_POST['sStatus'];
$sortVal = $_POST['sortVal'];
$liveStatus = $_POST['liveStatus'];

//$errors['message'] .= "hello.\n";

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
if (empty($sTag)) $errors['message'] .= "Series Tag is required.\n";
if (empty($sName)) $errors['message'] .= "Series Name is required.\n";
if (empty($series)) $errors['message'] .= "Series needs to be selected.\n";
if (empty($sID)) $errors['message'] .= "Series ID is required.\n";
if (empty($eTag)) $errors['message'] .= "Auction Title Tag is required.\n";



//if (empty($_POST['email'])) $errors['email'] = 'Email is required.';

//if (empty($_POST['superheroAlias'])) $errors['superheroAlias'] = 'Superhero alias is required.';

// return a response ===========================================================
// if there are any errors in our errors array, return a success boolean of false
if (!empty($errors))
{

    // if there are items in our errors array, return those errors
    $data['success'] = false;
    $data['errors'] = $errors;
}
else
{

    // if there are no errors process our form, then return a message
    // DO ALL YOUR FORM PROCESSING HERE
    // THIS CAN BE WHATEVER YOU WANT TO DO (LOGIN, SAVE, UPDATE, WHATEVER)


    if ($series !== "new"){

        $run = mysqli_query($db_main, "UPDATE series_info SET series_name='".$sName."', series_tag='".$sTag."', ebay_tag='".$eTag."', front_img='".$front."', back_img='".$back."', series_desc='".$desc."',
         sort='".$sortVal."', cover_img='".$cover."', series_status='".$sStatus."', live_status='".$liveStatus."' WHERE series_id='".$sID."'");
        

        $data['success'] = true;
        $data['message'] .= 'Series Information Updated!';


    }else{

        // update NEW series

        // OPTIONAL - add error checking - see if series name / tag / etag / sID already exist
        
        
        $run = mysqli_query($db_main, "INSERT INTO series_info (series_id, series_name, series_tag, ebay_tag, front_img, back_img, series_desc, sort, cover_img, series_status, live_status) 
        VALUES ('".$sID."', '".$sName."', '".$sTag."', '".$eTag."', '".$front."', '".$back."', '".$desc."', '".$sortVal."', '".$cover."', '".$sStatus."', '".$liveStatus."')");
        
        $result = mysqli_query($db_main, "SELECT * FROM series_info WHERE series_id='".$sID."'");

        $row = mysqli_num_rows($result); 

        // check if row was entered. we want ONE. if it's 1, we're good. if it's more than one, something is wrong. if it's 0, it wasnt added
        if ($row === 1){
            $data['success'] = true;
            $data['message'] .= $sName." has been created! Your page will now refresh";
        } elseif( $row === 2){
            $data['success'] = false;
            $errors['message'] .= "NOT UPDATED! A series already exists with this series name / ID: ".$sName;
            $data['errors'] = $errors;
        } else{
            $data['success'] = false;
            $errors['message'] .= "The series was not added: ".$sName;
            $data['errors'] = $errors; 
        }

    }




}

// return all our data to an AJAX call
echo json_encode($data);

$db_auth->close();
$db_main->close();

?>
