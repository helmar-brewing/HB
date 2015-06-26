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
$currentpage = 'account/';
if(isset($_POST['redir'])){
	$redir = $_POST['redir'];
}else{
	$redir = $_GET['redir'];
}
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$username = strtolower($_POST['username']);
$email = strtolower($_POST['email']);
$password1 = $_POST['password1'];
$password2 = $_POST['password2'];


// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);


// check if logged in
if($user->login() === 1){
    $user->regen();
    $db_auth->close();
    $db_main->close();
    if(isset($redir)){
        header('Location: '.$protocol.$site.'/'.$redir,TRUE,303);
        ob_end_flush();
        exit;
    }else{
        header('Location: '.$protocol.$site,TRUE,303);
        ob_end_flush();
        exit;
    }
}else{

    $i=0;
    switch($i){
        case 0:

			// check if registration is on
			if($gv_registraion_status === FALSE){
				$msg = '<li>Registration for this website is currently disabled.</li>';
				break;
			}

            // check if submitted
            if(empty($_POST)){
                break;
            }

            // validate input data
            $stop = FALSE;
            if($username == ''){
                $stop = TRUE;
                $msg .= '<li>You did not choose a username</li>';
            }elseif(strlen($username)<3 || strlen($username)>32){
                $stop = TRUE;
                $msg .= '<li>Usernames must be between 3 and 32 characters.</li>';
            }
            if(preg_match('/[^a-z0-9.]+/',$username)===1){
                $stop = TRUE;
                $msg .= '<li>Username can only contain letters and numbers.</li>';
            }
            if($user->exists($username)===TRUE){
                $stop = TRUE;
                $msg .= '<li>Username already taken</li>';
            }
            if(filter_var($email, FILTER_VALIDATE_EMAIL)){}else{
                $stop = TRUE;
                $msg .= '<li>You did not enter a valid email address.</li>';
            }
            if(db1($db_main, "SELECT email FROM users WHERE email ='$email' LIMIT 1")){
                $stop = TRUE;
                $msg .= '<li>A user with that email address already exists. <a href="https://'.$site.'/account/recover/">Account Recovery</a></li>';
            }
            if($password1 == ''){
                $stop = TRUE;
                $msg .= '<li>You did not enter a password. (passwords, must be at least 8 characters long)</li>';
            }else{
                if(strlen($password1)<8){
                    $stop = TRUE;
                    $msg .= '<li>Your password must be at least 8 characters long.</li>';
                }
                if(preg_match('/[\'\"]+/',$password1)===1){
                    $stop = TRUE;
                    $msg .= '<li>Your password may not contain single or double quotes.</li>';
                }
                if($password1 != $password2){
                    $stop = TRUE;
                    $msg .= '<li>Your password did not match.</li>';
                }
            }
            if($stop){
                break;
            }


            // create user
    		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
    		try{
                $hash = $user->new_hash($password1);

        	    //$currentVer = siteSetting('currentVer');
        		$currentVer = 0;

    			$cust = \Stripe\Customer::create(array(
    				"description"	=> $username,
    				"email"			=> $email
    			));
    			$db_auth->query("INSERT INTO users(username, saltedHash, lastUsedVer) VALUES('$username', '$hash', '$currentVer')");
    			$userID = $db_auth->insert_id;
    			$stmt = $db_main->prepare("INSERT INTO users(userID, username, firstname, lastname, email, stripeID) VALUES(?,?,?,?,?,?)");
    			$stmt->bind_param("ssssss", $userID, $username, $firstname, $lastname, $email, $cust['id']);
    			$stmt->execute();
    			$stmt->close();
    			$msg .= '<li>You have successfully registerd.</li>';
    		}catch(Stripe_AuthenticationError $e){
    			$msg .= '<li>There may have been an error with your registration. <a href="'.$protocol.$site.'/contact/">Contact us</a>. (ref: stripe 1)</li>';
                break;
    		}catch(Stripe_InvalidRequestError $e){
    			$msg .= '<li>There may have been an error with your registration. <a href="'.$protocol.$site.'/contact/">Contact us</a>. (ref: stripe 2)</li>';
                break;
    		}catch(Stripe_AuthenticationError $e){
    			$msg .= '<li>There may have been an error with your registration. <a href="'.$protocol.$site.'/contact/">Contact us</a>. (ref: stripe 3)</li>';
                break;
    		}catch(Stripe_ApiConnectionError $e){
    			$msg .= '<li>There may have been an error with your registration. <a href="'.$protocol.$site.'/contact/">Contact us</a>. (ref: stripe 4)</li>';
                break;
    		}catch(Stripe_Error $e){
    			$msg .= '<li>There may have been an error with your registration. <a href="'.$protocol.$site.'/contact/">Contact us</a>. (ref: stripe 5)</li>';
                break;
    		}catch(mysqli_sql_exception $e){
    			$msg .= '<li>There msy have been an error with your registration. <a href="'.$protocol.$site.'/contact/">Contact us</a>. (ref: database)</li>';
    			/* Clean up in case of failure */
    			$cu = \Stripe\Customer::retrieve($cust['id']);
    			$cu->delete();
    			if($userID !== null){
    				$db_auth->query("DELETE FROM users WHERE userID=$userID LIMIT 1");
    				$db_main->query("DELETE FROM users WHERE userID=$userID LIMIT 1");
    			}
                break;
    		}catch(Exception $e){
                $msg .= '<li>There may have been an error with your registration. <a href="'.$protocol.$site.'/contact/">Contact us</a>. (ref: generic)</li>';
                /* Clean up in case of failure */
                if($userID !== null){
    				$db_auth->query("DELETE FROM users WHERE userID=$userID LIMIT 1");
    				$db_main->query("DELETE FROM users WHERE userID=$userID LIMIT 1");
    			}
                break;
            }
    		mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries


            // do the login
            $user->username = $username;
            $user->newlogin();
            $db_auth->close();
            $db_main->close();
            if(isset($redir)){
                header('Location: '.$protocol.$site.'/'.$redir,TRUE,303);
                ob_end_flush();
                exit;
                break;
            }else{
                header('Location: '.$protocol.$site.'/subscription/',TRUE,303);
                ob_end_flush();
                exit;
                break;
            }

        // What Happened?
        default:
            $msg = '<li>There was an error.</li>';
    }




    ob_end_flush();
    /* <HEAD> */ $head=''; // </HEAD>
    /* PAGE TITLE */ $title='Helmar Brewing Co';
    /* HEADER */ require('layout/header0.php');


    /* HEADER */ require('layout/header2.php');
    /* HEADER */ require('layout/header1.php');

    /* FOCUS CURSOR */ print'<script type="text/javascript">$(document).ready(function(){$("#firstname").focus()});</script>';

    print'
		<div class="sideimage login">
			<div class="images-wrapper"></div>
			<div class="side-image-content">
				<h4>Account</h4>
				<h1>Register</h1>
				<p>You are registering for a free membership. After creating an account, you will be able to
				select a paid subscription or remain with the free membership. <a href="'.$protocol.$site.'/subscriptions">Click to view the available subscription plans.</a></p>
		        <form method="post">
    ';
    if(isset($redir)){
        print'
		            <input type="hidden" name="redir" value="'.$redir.'" />
        ';
    }
	if($msg != ''){
		print'
					<ul>'.$msg.'</ul>
		';
	}
    print'
                    <label for="firstname">First Name</label>
                    <input type="text" name="firstname" id="firstname" tabindex="1" value="'.$firstname.'"/>
                    <label for="lastname">Last Name</label>
                    <input type="text" name="lastname" id="lastname" tabindex="2" value="'.$lastname.'"/>
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" tabindex="3" value="'.$username.'"/>
                    <label for="email">Email Address</label>
                    <input type="text" name="email" id="email" tabindex="4" value="'.$email.'"/>
                    <label for="password1">Password</label>
                    <input type="password" name="password1" id="password1" tabindex="5" />
                    <label for="password2">Confirm Password</label>
                    <input type="password" name="password2" id="password2" tabindex="6" />
                    <input type="submit" value="Register" tabindex="7" />
		            <div class="login-footer">
		                Already have an account? <a href="'.$protocol.$site.'/account/login?redir=account/">Log in</a>
		            </div>
		        </form>
			</div>
		</div>
    ';

/*		                Need an account? <a href="'.$protocol.$site.'/account/register/">Register</a> | Having trouble logging in? <a href="'.$protocol.$site.'/account/recover/">Account Recovery</a>
*/

    /* FOOTER */ require('layout/footer1.php');

    $db_auth->close();
    $db_main->close();
}

?>
