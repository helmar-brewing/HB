<?php
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
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);

/* PAGE VARIABLES */
$file = $magazine_path.$_GET['f'];
$currentpage = 'magazine/'.$_GET['f'];
class SubException extends Exception{}
class SubRedirException extends Exception{}

$user = new phnx_user;
$user->checklogin(1);
$user->checksub();

$db_auth->close();
$db_main->close();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
try{

	if($user->login() !== 1){
        throw new AuthException('');
    }

	switch($user->subscription[status]){
		case 'error':
            throw new SubException('x1');
			break;
		case 'none':
  //          throw new SubRedirException('/subscription/?status=none');
	//		break;
		case 'past_due':
	//		throw new SubRedirException('/subscription/?status=past_due');
	//		break;
		case 'unpaid':
//			throw new SubRedirException('/subscription/?status=unpaid');
// 			break;
		case 'canceled':
//			throw new SubRedirException('/subscription/?status=canceled');
//			break;
		case 'trialing':
		case 'active':

			if($_GET['f'] == '/' || $_GET['f'] == ''){
				http_response_code(404);
				echo '404';
			}else{
                if(file_exists($file)){
                    header('Content-Type: ' . mime_content_type($file));
                    header('Content-Length: ' . filesize($file));
                    readfile($file);
                }else{
                    http_response_code(404);
                    echo '404';
                }
			}

			break;
		default:
            throw new SubException('x2');
			break;
	}
}catch(SubRedirException $e){
	$page = $e->getMessage();
	header('Location: '.$protocol.$site.$page,TRUE,303);
    ob_end_flush();
    exit;
}catch(SubException $e){
    http_response_code(500);
    echo '500 ';
	echo $e->getMessage();
	///* SESSION DEBUGGING */ print'<pre style="font-family:monospace;background-color:#444;padding:1em;color:white;">';var_dump($_SESSION);print'</pre>';
}catch(AuthException $e){
    header('Location: '.$protocol.$site.'/account/login/?redir='.$currentpage,TRUE,303);
    ob_end_flush();
    exit;
}catch(\Stripe\Error\Card $e) {
    http_response_code(503);
    echo '503';
}catch(\Stripe\Error\InvalidRequest $e) {
    http_response_code(503);
    echo '503';
}catch(\Stripe\Error\Authentication $e) {
    http_response_code(503);
    echo '503';
}catch(\Stripe\Error\ApiConnection $e) {
    http_response_code(503);
    echo '503';
}catch(\Stripe\Error\Base $e) {
    http_response_code(503);
    echo '503';
}catch(mysqli_sql_exception $e){
    http_response_code(500);
    echo '500 x3';
}catch(Exception $e){
    http_response_code(500);
    echo '500 x4';
}
mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries

ob_end_flush();

?>
