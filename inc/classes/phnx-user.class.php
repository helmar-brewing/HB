<?php

	class AuthException extends Exception{}

	class phnx_user{

		private $login;
		private $cookie;

		public $username;
		public $id;
		public $firstname;
		public $lastname;
		public $email;
		public $stripeID;
		public $ebay;
		public $subscription;
		public $address;
		public $error_cookie;
		public $error = array();
		public $loginID;




		/* RETURN CURRENT LOGIN STATE */
		function login(){
			return $this->login;
		}



		/* IMPORT LOGIN COOKIE TO CLASS */
		function get_cookie(){
			global $gv_login_cookie_name;
			if(isset($_COOKIE[$gv_login_cookie_name])){
				$this->cookie = $_COOKIE[$gv_login_cookie_name];
			}
		}



		/* KILL SESSION */
		function kill_session(){
			$_SESSION = array();
			if (ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
			}
			session_destroy();
		}



		/* RUN THE LOGIN CHECK */
		function checklogin($l){
			if($l === 1){
				$this->checklogin1();
			}elseif($l === 2){
				$this->checklogin2();
			}else{
				trigger_error("Invalid parameter specified for logincheck", E_USER_ERROR);
			}
		}



		/* MAKE A NEW LOGIN */
		function newlogin(){
			if(isset($this->username)){
				global $db_auth;
				$useragent = $_SERVER['HTTP_USER_AGENT'];
				$ipAddy = $_SERVER['REMOTE_ADDR'];
				$logintime = time();
				$sprinkles = '|' . $logintime . '-' . md5(uniqid(rand(),true)) . sha1(uniqid(rand(),true));
				$cookieString = $this->username . $sprinkles;
				$db_auth->query("INSERT INTO activeLogins (username, loginID, loginTime, IP, useragent) VALUES ('".$this->username."', '$sprinkles', '$logintime', '$ipAddy', '$useragent')");
				$this->cookieMonster('set',$cookieString);
				$db_auth->query("UPDATE users SET lastLogin='$logintime' WHERE username='".$this->username."' LIMIT 1");
				$this->loginID = $sprinkles;
			}else{
				trigger_error("UserMgmt tried to create a new login, and username is not set.", E_USER_ERROR);
			}
		}



		/* REGENERATE THE ACTIVE LOGIN */
		function regen(){
			global $db_auth;
			$sprinkles = substr($this->cookie, 0 - strlen($this->cookie) + strpos($this->cookie,'|'));
			$this->del_active_login($sprinkles);
			$this->cookieMonster('delete','logout');
			$this->newlogin();
		}

		/* LOGOUT */
		function logout($all = NULL){

			// can be simplifed if PHP is 5.4 or greater for sure
			if ( version_compare(phpversion(), '5.4.0', '>=') ){
				if(session_status() === PHP_SESSION_ACTIVE){
					$s = TRUE;
				}
			} else {
				if(session_id() === ''){
					$s = FALSE;
				}else{
					$s = TRUE;
				}
			}

			if(!$s){ session_start(); }


			global $db_auth;
			if($this->cookie == NULL){
				$this->get_cookie();
			}
			$sprinkles = substr($this->cookie, 0 - strlen($this->cookie) + strpos($this->cookie,'|'));
			$this->del_active_login($sprinkles);
			$this->cookieMonster('delete','logout');
			$this->kill_session();

			if($all === 'all'){
				$db_auth->query("DELETE FROM activeLogins WHERE username='".$this->username."'");
			}

			$this->username = NULL;
			$this->id = NULL;
			$this->login = 0;
			$this->firstname = NULL;
			$this->lastname = NULL;
			$this->email = NULL;
		}



		/* RETURN ARRAY OF ACTIVE LOGINS */
		function get_active_logins(){
			if($this->login === 2){
				global $db_auth;
				$activeLogins = array();
				$R_logins = $db_auth->query("SELECT * FROM activeLogins WHERE username = '".$this->username."' ORDER BY loginTime DESC");
				$R_logins->data_seek(0);
				while($login = $R_logins->fetch_assoc()){
					$browser = @get_browser($login['userAgent'],true);
					$activeLogins[] = array(
						'loginID'	=> $login['loginID'],
						'logintime'	=> $login['logintime'],
						'IP'		=> $login['IP'],
						'useragent'	=> $login['userAgent'],
						'browser'	=> $browser
					);
				}
				$R_logins->free();
				unset($R_logins);
				return($activeLogins);
			}else{
				trigger_error("UserMgmt tried to get the list of active logins without level 2 access.", E_USER_ERROR);
			}
		}



		/* CHECK TO SEE IF A USERNAME EXISTS */

		// this accepts user input directly, needs to be cleaned, or check before passing.

		function exists($username){
			global $db_auth;
			$a = db1($db_auth, "SELECT username FROM users WHERE username='$username' LIMIT 1");
			if($a !== FALSE){
				return TRUE;
			}else{
				return FALSE;
			}
		}



		/* CHECK FOR USER BASED ON FACEBOOK ID */
		function check_fb_id($fbid){
			if($fbid == '' || $fbid == NULL){
				trigger_error("UserMgmt tried to check login via facebook without a facebook ID.", E_USER_ERROR);
			}else{
				global $db_auth;
				$a = db1($db_auth, "SELECT username FROM users WHERE facebook='$fbid' LIMIT 1");
				if($a !== FALSE){
					$this->username = $a;
					return TRUE;
				}else{
					return FALSE;
				}
			}
		}



		/* CHECK PASSWORD */
		function comparepass(){
			if(isset($this->username)){
				$saltedHash = $this->getSaltedHash();
				if($saltedHash === FALSE){
					$this->error[] = 'Could not get salted hash. Error CMP.01';
					return FALSE;
				}else{
					if(isset($_POST['pass'])){
						$salt = substr($saltedHash,0,22);
						$saltedHash = '$2y$11$'.$saltedHash;
						$hash2test = crypt($_POST['pass'], '$2y$11$'.$salt.'$');
						if($hash2test === $saltedHash){
							return TRUE;
						}else{
							$this->error[] = 'Password does not match. Error CMP.03';
							return FALSE;
						}
					}else{
						$this->error[] = 'Submitted password is not set. Error CMP.02';
						return FALSE;
					}
				}
			}else{
				trigger_error("UserMgmt tried to verify password, and username is not set.", E_USER_ERROR);
			}
		}




		/* COOKIE MONSTER */
		function cookieMonster($action, $data, $exp = 864000, $name = NULL, $domain = NULL){

			$cookieError = 'Cookies are sometimes-food.';
			$successCreate = 'COOKIE!';
			$successDelete = 'nom nom nom';

			global $gv_login_cookie_name;
			global $gv_login_cookie_domain;
			if($name === NULL){
				$name = $gv_login_cookie_name;
			}
			if($domain === NULL){
				$domain = $gv_login_cookie_domain;
			}
			if(isset($name) && isset($domain) && isset($data) && isset($action)){
				if($action == 'set'){
					if(setcookie($name, $data, time() + $exp, '/', $domain, 0, 1)){
						$this->error_cookie = $successCreate;
					}else{
						$this->error_cookie = $cookieError . ' Error CKE.04';
					}
				}elseif($action == 'delete'){
					if(setcookie($name, $data, time() - 3600, '/', $domain, 0, 1)){
						$this->error_cookie = $successDelete;
						$this->cookie = NULL;
					}else{
						$this->error_cookie = $cookieError . ' Error CKE.03';
					}
				}else{
					$this->error_cookie = $cookieError  . ' Error CKE.02';
				}
			}else{
				$this->error_cookie = $cookieError  . ' Error CKE.01';
			}
		}




		/* DELETE A SPECIFIC ACTIVE LOGIN */
		function del_active_login($loginID){
			global $db_auth;
			if($db_auth->query("DELETE FROM activeLogins WHERE loginID='$loginID'")){
				return TRUE;
			}else{
				return FALSE;
			}
		}




		/* RUN THE LOGIN CHECK UP TO LEVEL 1 */
		private function checklogin1(){
			if($this->cookie == NULL){
				$this->get_cookie();
			}
			if(isset($this->cookie)){
				$this->level1();
			}else{
				$this->login = 0;
			}
		}



		/* RUN THE LOGIN CHECK UP TO LEVEL 2 */
		private function checklogin2(){
			if($this->login === 1){
				$this->level2();
			}else{
				$this->checklogin1();
				if($this->login === 1){
					$this->level2();
				}else{
					$this->login = 0;
				}
			}
		}



		/* GET SALTED HASH */
		private function getSaltedHash(){
			global $db_auth;
			$R_saltedHash = $db_auth->query("SELECT saltedHash FROM users WHERE username = '".$this->username."' LIMIT 1");
			if($R_saltedHash != FALSE){
				if($R_saltedHash->num_rows == 1){
					$A_saltedHash = $R_saltedHash->fetch_array(MYSQLI_NUM);
					if(count($A_saltedHash, COUNT_RECURSIVE)==1){
						$saltedHash = $A_saltedHash[0];
						// Strip the pepper
						$saltedHash = substr($saltedHash,0,53);
					}else{
						$saltedHash = FALSE;
						$this->error[] = 'getSaltedHash 03';
					}
				}else{
					$saltedHash = FALSE;
					$this->error[] = 'getSaltedHash 02';
				}
			}else{
				$saltedHash = FALSE;
				$this->error[] = 'getSaltedHash 01';
			}
			$R_saltedHash->free();
			unset($R_saltedHash);
			return $saltedHash;
		}



		/* POPULATE OR REFRESH USER DATA */
		public function updateInfo(){
			global $db_main;
			$R_info = $db_main->query("SELECT * FROM users WHERE username = '".$this->username."' LIMIT 1");
			if($R_info != FALSE){
				$info = $R_info->fetch_assoc();
				$this->id = $info['userid'];
				$this->firstname = $info['firstname'];
				$this->lastname = $info['lastname'];
				$this->email = $info['email'];
				$this->stripeID = $info['stripeID'];
				$this->ebay = $info['ebayID'];
				$this->address = array(
					'firmname' => $info['firmname'],
					'unit' => $info['unit'],
					'address' => $info['address'],
					'city' => $info['city'],
					'state' => $info['state'],
					'zip5' => $info['zip5'],
					'zip4' => $info['zip4']
				);
				$R_info->free();
				unset($R_info);
			}
		}



		/* THE LEVEL 1 CHECK */
		private function level1(){
			global $db_auth;
			global $db_main;
			$username = substr($this->cookie, 0, strpos($this->cookie, '|'));
			$sprinkles = substr($this->cookie, 0 - strlen($this->cookie) + strpos($this->cookie,'|'));
			$R_activeLogins = $db_auth->query("SELECT * FROM activeLogins WHERE username = '$username'");
			if($R_activeLogins != FALSE){
				$R_activeLogins->data_seek(0);
				while ($activeLogins = $R_activeLogins->fetch_assoc()) {
					// This loop checks all Active Logins for a match, if it finds one it sets login to 1 and breaks out of the loop
					if( $activeLogins['loginID'] == $sprinkles && $sprinkles != ''){
						$this->login = 1;
						$this->username = $username;
						break;
					}else{
						$this->login = 0;
					}
				}
			}else{
				$this->login = 0;
			}
			$R_activeLogins->free();
			unset($R_activeLogins);
			if($this->login === 1){
				$this->updateInfo();
			}
		}






		/* THE LEVEL 2 CHECK */
		private function level2(){
			session_start();
			if (!isset($_SESSION['level2session'])){
				session_regenerate_id();
			}
			if($_SESSION['level2session'] === TRUE && $_SESSION['sessionUsername'] === $this->username){
				$diff = time()-$_SESSION['last_activity'];
				if($diff < 1800 && $diff >= 0){
					$this->login = 2;
					$_SESSION['last_activity'] = time();
				}else{
					$this->kill_session();
					if($this->login !== 1){
						$this->login = 0;
					}
				}
			}else{
				$saltedHash =  $this->getSaltedHash();
				if($saltedHash === FALSE){
					$this->kill_session();
					if($this->login !== 1){
						$this->login = 0;
					}
				}else{
					if(isset($_POST['pass'])){
						$salt = substr($saltedHash,0,22);
						$saltedHash = '$2y$11$'.$saltedHash;
						$hash2test = crypt($_POST['pass'], '$2y$11$'.$salt.'$');
						if($hash2test === $saltedHash){
							// CREATE SESSION
							$_SESSION['level2session'] = TRUE;
							$_SESSION['sessionUsername'] = $this->username;
							$_SESSION['last_activity'] = time();
							session_write_close();
							$this->login = 2;
						}else{
							$this->kill_session();
							if($this->login !== 1){
								$this->login = 0;
							}
						}
					}else{
						$this->kill_session();
						if($this->login !== 1){
							$this->login = 0;
						}
					}
				}
			}
		}





		/* THE SUBSCRIPTION CHECK */
		function checksub($mode=NULL){
			session_start();
			if($mode === 'no-cache'){
				unset($_SESSION['sub']);
				$this->sub(false);
			}else{
				if(isset($_SESSION['sub'])){
					if($_SESSION['sub']['status'] == 'error'){
						unset($_SESSION['sub']);
						$this->sub(true);
					}
					$this->subscription = $_SESSION['sub'];
				}else{
					$this->sub(true);
				}
			}
		}
		private function sub($cache){
			try{
				$sub_response = \Stripe\Customer::retrieve($this->stripeID)->subscriptions->all();
				if(empty($sub_response->data)){
					$this->subscription = array(
						'status' => 'none'
					);
				}else{
					$good_sub = 0;
					foreach($sub_response->data as $sub_data){
						if($sub_data->plan['id'] === 'helmar16'){
							$this->subscription = array(
								'status' => $sub_data['status'],
								'id' => $sub_data['id'],
								'cancel_at_period_end' => $sub_data['cancel_at_period_end'],
								'current_period_end' => $sub_data['current_period_end'],
								'next_payment' => $sub_data->plan['amount']
							);
							$good_sub++;
						}
					}
					if($good_sub === 0){
						$this->subscription = array(
							'status' => 'none'
						);
					}elseif($good_sub === 1){
						// do nothing
					}elseif($good_sub > 1){
						$this->subscription = array(
							'status' => 'error',
							'msg'	 => 'Multiple subscriptions found, contact support.'
						);
					}
				}
			}catch(Stripe_CardError $e){
				$this->subscription = array(
					'status' => 'error',
					'msg'	 => $e->getMessage()
				);
			}catch (Stripe_InvalidRequestError $e){
				$this->subscription = array(
					'status' => 'error',
					'msg'	 => $e->getMessage()
				);
			}catch (Stripe_AuthenticationError $e){
				$this->subscription = array(
					'status' => 'error',
					'msg'	 => $e->getMessage()
				);
			}catch (Stripe_ApiConnectionError $e){
				$this->subscription = array(
					'status' => 'error',
					'msg'	 => $e->getMessage()
				);
			}catch (Stripe_Error $e){
				$this->subscription = array(
					'status' => 'error',
					'msg'	 => $e->getMessage()
				);
			}catch (Exception $e){
				$this->subscription = array(
					'status' => 'error',
					'msg'	 => $e->getMessage()
				);
			}
			if($cache){
				$_SESSION['sub'] = $this->subscription;
			}
		}





		/* PASSWORD HASH GENERATOR */
		function new_hash($pword = NULL){
			if($pword === NULL){
				throw new Exception("UserMgmt tried to create a new hash, and the password is not set.");
			}
			$salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
			$salt = base64_encode($salt);
			$salt = str_replace('+', '.', $salt);
			$pepper = md5(uniqid(rand(),true));
			$saltedHash = crypt($pword, '$2y$11$'.$salt.'$');
			$pepperedHash = substr($saltedHash,7) . $pepper;
			return $pepperedHash;
		}


	}
?>
