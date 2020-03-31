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





    if($series==="new"){
        // do nothing
        $errors['message'] = "You shouldn't be here. A new series was seelcted".$errors['message'];
        // if there are items in our errors array, return those errors
        $data['success'] = false;
        $data['errors'] = $errors;
    }else{
        // for existing series, load variables with existing  data

        $result = mysqli_query($db_main, "DELETE FROM series_info WHERE series_id='".$series."'");

        $data['success'] = true;
        $data['message'] = "You DELETED the series '".$series."' from the Series Info table. Your browser will now refresh!";
    }


}

// return all our data to an AJAX call
echo json_encode($data);

$db_auth->close();
$db_main->close();

?>
