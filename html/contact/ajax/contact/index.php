<?php

/* TEST FOR SUBMISSION */  if(empty($_GET)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps($use_https);

/* WHICH DATABASES DO WE NEED */
$db2use = array(
	'db_auth' 	=> FALSE,
	'db_main'	=> FALSE
);

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
require_once('libraries/drill/drill.php');


$go = TRUE;

// 2. CHECK NAME AND EMAIL
if($go){
	$email = $_GET['email'];
	$name = $_GET['name'];
}


// 3. CLEAN INPUT and SEND MAIL
if($go){

	$comment = $_GET['comment'];

	$to =	array(
                array("email" => "topshelfsmith@gmail.com", "name" => "Robert")
			);
	$headers = array(
        "Reply-To" => $email,
        "Bcc" => 'smith@stev.co',
//        "Bcc" => 'djradam@tek13.com'
    );
	$args = array(
		'key' => $apikey['mandrill'],
		'message' => array(
			"html" => "<p>Name: ".$name."<br/>Email: ".$email."</p><p>".$comment."</p>",
			"from_email" => "contact.form@helmarbrewing.com",
			"from_name" => "Helmar Contact Form",
			"subject" => "Helmar Brewing Co (Ticket #".time()." )",
			"to" => $to,
			"headers" =>$headers,
			"track_opens" => true,
			"track_clicks" => false,
			"auto_text" => true
		)
	);

	$mandrill = new \Gajus\Drill\Client($apikey['mandrill']);
    $r = $mandrill->api('messages/send', $args);

	if($r['status']== 'error'){
		$json = array(
				'error' => '1',
				'msg' => 'There was an error sending your message, please try again. (ref: madrill error)'
			);
	}else{
		$json = array(
				'error' => '0',
				'msg' => 'Your message has been sent. Thank you.'
			);
	}

}

header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();

?>
