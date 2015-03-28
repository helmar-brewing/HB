<?php

///* TEST FOR SUBMISSION */  if(empty($_GET)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

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
require_once('libraries/drill/Client.php');
$drill = new \Gajus\Drill\Client($api_key['madrill']);

/* PAGE VARIABLES */
$email = $_GET['email'];


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
try{

    // validate email
    if(filter_var($email, FILTER_VALIDATE_EMAIL)){}else{
        throw new Exception('You did not provide a valid email address.');
    }

    // check to see if there is a user with that email
    $username = db1($db_auth, "SELECT username FROM users WHERE email = '$email'");
    if($username == FALSE){
        throw new Exception('There is no account associated with that email address.');
    }

    // send the message
    $html    = '<div style="line-height:1.3; font-family:arial, sans-serif; font-size:12pt; max-width:500px">';
    $html	.= '<p>As requested, here is your username for helmarbrewing.com.</p>';
    $html   .= '<p style="font-weight:bold;">'.$username.'</p>';
    $html   .= '<p>Login at <a href="'.$protocol.$site.'/account/login/">'.$protocol.$site.'/account/login/</a></p>';
    $html   .= '</div>';
    $to =	array(array("email" => $email, "name" => $username));
    $headers = array("Reply-To"=> "membership@hoaumich.org");
    $args = array(
        'message' => array(
            "html" => $html,
            "from_email" => "no-reply@helmarbrewing.com",
            "from_name" => "helmarbrewing.com",
            "subject" => "Your username for helmarbrewing.com",
            "to" => $to,
            "headers" =>$headers,
            "track_opens" => true,
            "track_clicks" => false,
            "auto_text" => true
        )
    );
    $r = $drill->api('messages/send', $args);
    if($r['status']== 'error'){
    }else{
        $h1 = 'Username Recovery';
        $content = '<p>Your username was sent in an email to <strong>'.$email.'</strong></p>';
    }

}catch(\Gajus\Drill\Exception\DrillException $e){
    $h1 = 'Error';
    $content = 'There was an error sending the username (ref: drill)';
}catch(mysqli_sql_exception $e){
    $h1 = 'Error';
    $content = 'There was an error sending the username (ref: data)';
}catch(Exception $e){
    $h1 = 'Error';
    $content = $e->getMessage();
}
mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries


$json = array(
    'h1' => $h1,
    'content' => $content
);


$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();

?>
