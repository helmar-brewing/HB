<?php

/* TEST FOR SUBMISSION */  if(empty($_GET)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

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
require_once('classes/phnx-user.class.php');
require_once('classes/mailchimp.class.php');
$chimp = new \DrewM\MailChimp\MailChimp($apikey['mailchimp']);


$user = new phnx_user;
$user->checklogin(1);
if($user->login() === 1){

    $subscriber = md5(strtolower($user->email));
    $r = $chimp->get('lists/'.$apikey['mailchimp_list'].'/members/'.$subscriber);

    if($r === FALSE){
        $error = 1;
    }else{
        switch($r['status']){
            case 404:
                $args = array(
                    'email_address'	=> $user->email,
                    'status'		=> 'subscribed',
                    'merge_fields'	=> array(
                        'FNAME'		=> $user->firstname,
                        'LNAME'		=> $user->lastname
                    )
                );
                $m = $chimp->post('lists/'.$apikey['mailchimp_list'].'/members', $args);
                if($m['id'] === $subscriber){
                    $error = 0;
                    $checked = 'yes';
                }else{
                    $error = 1;
                    $h1 = 'Error';
                    $html = 'There was an error chnaging your newsletter subscription. Please try again.';
                }
                break;
            case 'subscribed':
                $args = array('status' => 'unsubscribed');
                $m  = $chimp->patch('lists/'.$apikey['mailchimp_list'].'/members/'.$subscriber, $args);
                if($m['id'] === $subscriber){
                    $error = 0;
                    $checked = 'no';
                }else{
                    $error = 1;
                    $h1 = 'Error';
                    $html = 'There was an error chnaging your newsletter subscription. Please try again.';
                }
                break;
            case 'unsubscribed':
                $args = array('status' => 'subscribed');
                $m  = $chimp->patch('lists/'.$apikey['mailchimp_list'].'/members/'.$subscriber, $args);
                if($m['id'] === $subscriber){
                    $error = 0;
                    $checked = 'yes';
                }else{
                    $error = 1;
                    $h1 = 'Error';
                    $html = 'There was an error chnaging your newsletter subscription. Please try again.';
                }
                break;
            case 'cleaned':
                $error = 0;
                $checked = 'no';
                break;
            case 'pending':
                $args = array('status' => 'subscribed');
                $m  = $chimp->patch('lists/'.$apikey['mailchimp_list'].'/members/'.$subscriber, $args);
                if($m['id'] === $subscriber){
                    $error = 0;
                    $checked = 'yes';
                }else{
                    $error = 1;
                    $h1 = 'Error';
                    $html = 'There was an error chnaging your newsletter subscription. Please try again.';
                }
                break;
            default:
                $error = 1;
                break;
        }
    }
}else{
    $error = '1';
    $h1 = 'Error';
    $html = '<p>There was an error updating your account. Please refresh the page and try again.</p><p>(ref. auth fail)</p>';
}

$json = array(
	'error'     => $error,
    'h1'        => $h1,
    'html'      => $html,
    'checked'   => $checked
);



$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();

?>
