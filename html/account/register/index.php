<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ if($use_https === TRUE){if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;}}

/* WHICH DATABASES DO WE NEED */
	$db2use = array(
		'db_auth' 	=> TRUE,
		'db_main'	=> TRUE
	);
//

/* GET KEYS TO SITE */ require($path_to_keys);


/* LOAD FUNC-CLASS-LIB */
	require_once('classes/phnx-user.class.php');
//

/* PAGE VARIABLES */
$currentpage = 'account/';
$redir = $_GET['redir'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$username = $_POST['username'];
$email = $_POST['email'];
$password1 = $_POST['password1'];
$password2 = $_POST['password2'];
//



$user = new phnx_user;
$user->checklogin(2);

/* <HEAD> */ $head=''; // </HEAD>
/* PAGE TITLE */ $title='Helmar - Register';



if($user->login() === 1){
	$user->regen();
	$db_auth->close();
	$db_main->close();
	header("Location: http://$site/",TRUE,303);
	exit;
}else{
	ob_end_flush();
	
	
	// 0. CHECK TO SEE IF REGISTRATION IS OPEN
	if($gv_registraion_status == FALSE){
		$message = 'Registration on this site is closed.';
		$show = FALSE;
	}else{
		if(empty($_POST)){
			$go = FALSE;
		}else{
			$go = TRUE;
		}
		$message = '';
		$show = TRUE;
	}
	
	// 1. CHECK DATA
	if($go){
		$username = strtolower($username);
		$email = strtolower($email);
		if($username == ''){
			$go = FALSE;
			$message .= 'You did not choose a username<br/>';
		}elseif(strlen($username)<3 || strlen($username)>32){
			$go = FALSE;
			$message .= 'Usernames must be between 3 and 32 characters.<br/>';
		}
		if(preg_match('/[^a-z0-9.]+/',$username)===1){
			$go = FALSE;
			$message .= 'Username can only contain letters and numbers.<br/>';
		}
		if($user->exists($username)===TRUE){
			$go = FALSE;
			$message .= 'Username already taken<br/>';
		}
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){}else{
			$go = FALSE;
			$message .= 'You did not enter a valid email address.<br/>';
		}
		if(db1($db_main, "SELECT email FROM users WHERE email ='$email' LIMIT 1")){
			$go = FALSE;
			$message .= 'A user with that email address already exists. <a href="https://'.$site.'/account/recover/">Account Recovery</a><br/>';
		}
		if($password1 == ''){
			$go = FALSE;
			$message .= 'You did not enter a password. (passwords, must be at least 8 characters long)<br />';
		}else{
			if(strlen($password1)<8){
				$go = FALSE;
				$message .= 'Your password must be at least 8 characters long.<br />';
			}
			if(preg_match('/[\'\"]+/',$password1)===1){
				$go = FALSE;
				$message .= 'Your password may not contain single or double quotes.<br />';
			}
			if($password1 != $password2){
				$go = FALSE;
				$message .= 'Your password did not match.<br/>';
			}
		}
	}
	
	// 2. PROCESS DATA
	if($go){
	
		$salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
		$salt = base64_encode($salt);
		$salt = str_replace('+', '.', $salt);
		$pepper = md5(uniqid(rand(),true));
		
		$saltedHash = crypt($password1, '$2y$11$'.$salt.'$');
		$pepperedHash = substr($saltedHash,7) . $pepper;

		$emailtoken = substr(md5(uniqid(rand(),true)), 0, 25);
		
		//$currentVer = siteSetting('currentVer');
		$currentVer = 0;
		
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
		try{
			$db_auth->query("INSERT INTO users(username, saltedHash, lastUsedVer) VALUES('$username', '$pepperedHash', '$currentVer')");
			$userID = $db_auth->insert_id;
			$stmt = $db_main->prepare("INSERT INTO users(userID, username, firstname, lastname, email) VALUES(?,?,?,?,?)");
			$stmt->bind_param("sssss", $userID, $username, $firstname, $lastname, $email);
			$stmt->execute();
			$stmt->close;
			$message = '<p>You have successfully registerd.</p>';
		}catch(mysqli_sql_exception $e){
			$message = '<p>There my have been an error with your registration. <a href="http://'.$site.'/contact/">Contact us</a>.</p>';
			//$message.= '<p>'.$e->getMessage().'</p>';		
			/* Clean up in case of failure */
			$db_auth->query("DELETE FROM users WHERE userID=$userID LIMIT 1");
			$db_main->query("DELETE FROM users WHERE userID=$userID LIMIT 1");
		}
		mysqli_report(MYSQLI_REPORT_ERROR ^ MYSQLI_REPORT_STRICT); // remove this if you already use exceptions for all mysqli queries
		
		$show = FALSE;
	}

	print'
		<div class="register">
			<h2>Register</h2>
			<div class="message">'.$message.'</div>
	';
	if($show){
		print'
			<div class="reg-info">
				<p>Fill out this form to register.</p>
			</div>
			<form method="post">
				<input type="hidden" name="submitted" value="yes"/>
				<div class="register-left no-border">
					<label for="firstname">First Name</label>
					<input type="text" name="firstname" id="firstname" tabindex="1" value="'.$firstname.'"/>
				</div>
				<div class="register-right no-border">
					<label for="lastname">Last Name</label>
					<input type="text" name="lastname" id="lastname" tabindex="2" value="'.$lastname.'"/>
				</div>
				<div class="register-left">
					<label class="nudge" for="username">Username</label>
					<input type="text" name="username" id="username" tabindex="3" value="'.$username.'"/>
					<label for="email">Email Address</label>
					<input type="text" name="email" id="email" tabindex="4" value="'.$email.'"/>
				</div>
				<div class="register-right">
					<label class="nudge" for="password1">Password</label>
					<input type="password" name="password1" id="password1" tabindex="5" />
					<label for="password2">Confirm Password</label>
					<input type="password" name="password2" id="password2" tabindex="6" />
				</div>
				<input type="submit" value="Register" tabindex="7" />
			</form>
		';
	}
	print'
		</div>
	';
}




$db_auth->close();
$db_main->close();
?>