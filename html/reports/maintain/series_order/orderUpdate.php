<?php
ob_start();

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

$txt = "start0";
$id="yes";
$count=1;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries



 
// Get images id and generate ids array 
$idArray = explode(",", $_POST['ids']); 

$txt = implode(",",$idArray);
$id = $_POST['ids'];
 
//$update = $db_main->query("INSERT INTO test (test1, test2, num1) VALUES ('".$txt."', '".$id."', '".$count."')");

     /* 
     * Update image order 
     */ 

        $count = 1; 
        foreach ($idArray as $id){ 
        //    $txt = "UPDATE series_info SET sort=1 WHERE series_id='".$id."' LIMIT 1";
            $update = $db_main->query("UPDATE series_info SET sort=".$count." WHERE series_id='".$id."'");
       //     $update = $db_main->query("INSERT INTO test (test1, test2, num1) VALUES ('".$txt."', '".$id."', '".$count."')");
            $count ++;     
        } 

   //     $update = $db_main->query("INSERT INTO test (test1, test2, num1) VALUES ('".$txt."', '".$id."', '".$count."')");




        ob_end_flush();



$db_auth->close();
$db_main->close();

return TRUE; 


?>