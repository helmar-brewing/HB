<?php

/* TEST FOR SUBMISSION */  if(empty($_GET)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps();

/* WHICH DATABASES DO WE NEED */
$db2use = array(
	'db_auth' 	=> FALSE,
	'db_main'	=> TRUE
);

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
require_once('libraries/drill/drill.php');
$drill = new \Gajus\Drill\Client($apikey['mandrill']);

/* PAGE VARIABLES */
$email = $_GET['email'];


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
try{

    // validate email
    if(filter_var($email, FILTER_VALIDATE_EMAIL)){}else{
        throw new Exception('You did not provide a valid email address.');
    }

    // check to see if there is a user with that email
    $username = db1($db_main, "SELECT username FROM users WHERE email='$email' LIMIT 1");
    if($username == FALSE){
        throw new Exception('There is no account associated with that email address.');
    }

    // insert email token into database
    $token = substr(md5(uniqid(rand(),true)), 0, 25);
    $id = db1($db_main, "SELECT userid FROM users WHERE username='$username' LIMIT 1");
    $db_main->query("UPDATE users SET token='$token' WHERE username='$username' LIMIT 1");

    // send the message
    $html    = '<div style="line-height:1.3; font-family:arial, sans-serif; font-size:12pt; max-width:500px">';
    $html   .= '<p>Helmar Brewing Password Reset</p>';
    $html   .= '<p>Please click on this link to reset your password.<br/> <a href="'.$protocol.$site.'/account/recover/password/?token='.$id.'_'.$token.'">'.$protocol.$site.'/account/recover/password/?token='.$id.'_'.$token.'</a> <br/></p>';
    $html   .= '<p>Please note, this is a single use link. Once you click on it it will become invalid.</p>';
    $html   .= '</div>';
    $to =	array(array("email" => $email, "name" => $username));
    $args = array(
        'message' => array(
            "html" => $html,
            "from_email" => "no-reply@helmarbrewing.com",
            "from_name" => "helmarbrewing.com",
            "subject" => "Password Reset for helmarbrewing.com",
            "to" => $to,
            "headers" =>$headers,
            "track_opens" => true,
            "track_clicks" => false,
            "auto_text" => true
        )
    );
    $r = $drill->api('messages/send', $args);
    if($r['status']== 'error'){
        throw new Exception('There was an error sending the password reset email [ref: drill]');
    }else{
        $h1 = 'Password Reset';
        $content = '<p>A password reset email was sent to <strong>'.$email.'</strong></p>';
    }
} catch (\Gajus\Drill\Exception\RuntimeException\ValidationErrorException $e) {
    $h1 = 'Error';
    $content = '<p>There was an error sending the password reset email (ref: drill 1)</p>';
    $content .= '<p>'.$e->getMessage().'</p>';
} catch (\Gajus\Drill\Exception\RuntimeException\UserErrorException $e) {
    $h1 = 'Error';
    $content = '<p>There was an error sending the password reset email (ref: drill 2)</p>';
    $content .= '<p>'.$e->getMessage().'</p>';
} catch (\Gajus\Drill\Exception\RuntimeException\UnknownSubaccountException $e) {
    $h1 = 'Error';
    $content = '<p>There was an error sending the password reset email (ref: drill 3)</p>';
    $content .= '<p>'.$e->getMessage().'</p>';
} catch (\Gajus\Drill\Exception\RuntimeException\PaymentRequiredException $e) {
    $h1 = 'Error';
    $content = '<p>There was an error sending the password reset email (ref: drill 4)</p>';
    $content .= '<p>'.$e->getMessage().'</p>';
} catch (\Gajus\Drill\Exception\RuntimeException\GeneralErrorException $e) {
    $h1 = 'Error';
    $content = '<p>There was an error sending the password reset email (ref: drill 5)</p>';
    $content .= '<p>'.$e->getMessage().'</p>';
} catch (\Gajus\Drill\Exception\RuntimeException\ValidationErrorException $e) {
    $h1 = 'Error';
    $content = '<p>There was an error sending the password reset email (ref: drill 6)</p>';
    $content .= '<p>'.$e->getMessage().'</p>';
} catch (\Gajus\Drill\Exception\RuntimeException $e) {
    $h1 = 'Error';
    $content = '<p>There was an error sending the password reset email (ref: drill 7)</p>';
    $content .= '<p>'.$e->getMessage().'</p>';
} catch (\Gajus\Drill\Exception\InvalidArgumentException $e) {
    $h1 = 'Error';
    $content = '<p>There was an error sending the password reset email (ref: drill 8)</p>';
    $content .= '<p>'.$e->getMessage().'</p>';
} catch (\Gajus\Drill\Exception\DrillException $e) {
    $h1 = 'Error';
    $content = '<p>There was an error sending the password reset email (ref: drill 9)</p>';
    $content .= '<p>'.$e->getMessage().'</p>';
}catch(mysqli_sql_exception $e){
    $h1 = 'Error';
    $content = '<p>There was an error sending the password reset email (ref: data)</p>';
    $content .= '<p>'.$e->getMessage().'</p>';
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
//$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();

?>
