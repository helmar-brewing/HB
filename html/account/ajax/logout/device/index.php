<?php

/* TEST FOR SUBMISSION */  if(empty($_POST)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

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


/* PAGE VARIABLES */
$loginID = $_POST['login'];
//

$user = new phnx_user;
$user->checklogin(2);
if($user->login() === 2){
    $user->regen();
	if($user->del_active_login($loginID)){
        $error = '0';
        foreach($user->get_active_logins() as $login){
    		$html = '
    			<li>
    				Last accessed on <span>'.date("M j Y",$login['logintime']).'</span> at <span>'.date("g:ia",$login['logintime']).'</span><br />
    				from IP address <span>'.$login['IP'].'</span> with <span>'.$login['browser']['parent'].'</span> on <span>'.$login['browser']['platform'].'</span>
    		';
    		if($login['loginID'] === $user->loginID){
    			$html .= '<button class="signout" disabled>This Device</button>';
    		}else{
    			$html .= '<button class="signout" onclick="logoutDevice(\''.$login['loginID'].'\')">Log out device <i class="fa fa-sign-out"></i></button>';
    		}
    		$html .= '</li>';
    	}
    }else{
        $error = '1';
    }
}else{
    $error  = '1';
}

$json = array(
    'error' => $error,
    'list_html' => $html
);


$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();

?>
