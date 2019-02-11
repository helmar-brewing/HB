<?php
    require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');
    forcehttps();
    $db2use = array(
    	'db_auth' 	=> TRUE,
    	'db_main'	=> TRUE
    );
    require($path_to_keys);
    ob_start();

    require_once('classes/phnx-user.class.php');
    require_once('libraries/stripe/init.php');
    \Stripe\Stripe::setApiKey($apikey['stripe']['secret']);
    $user = new phnx_user;
    $user->checklogin(1);

    function get_user_sending_message_to($id){
        global $db_main;
        try{
            $user_sending_message_to = $db_main->query("
                SELECT *
                FROM users
                WHERE userid = $id
            ");
        }catch(Exception $e){
            return FALSE;
        }
        if($user_sending_message_to->num_rows === 1){
            return $user_sending_message_to->fetch_object();
        }else{
            return FALSE;
        }
    }

    do{
        $json = array();
        $code = 200;

        if($user->login() !== 1){
            $code = 401;
            break;
        }

        $send_to_user_id = (isset($_REQUEST['send_to_user_id'])) ? $_REQUEST['send_to_user_id'] : null;
        if($send_to_user_id === null){
            $code = 400;
            $json = array(
                'error_msg' => 'you did not tell us who to send the message to',
            );
            break;
        }

        $user_to_send_to = get_user_sending_message_to($send_to_user_id);
        if($user_to_send_to === false){
            $code = 404;
            break;
        }

        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            $to_line = '';
            $to_line .= ($user_to_send_to->firstname !== '' && $user_to_send_to->firstname !== null) ? $user_to_send_to->firstname : 'No Name Provided';
            $to_line .= ($to_line !== '' && $user_to_send_to->state !== '' && $user_to_send_to->state !== null) ? ' from ' . $user_to_send_to->state : '';
            $json = array(
                'from_name' => $user->firstname.' '.$user->lastname,
                'from_email' => $user->email,
                'to_line' => $to_line
            );
            $code = 200;
        }elseif($_SERVER['REQUEST_METHOD'] === 'POST'){
            $body = (isset($_POST['body'])) ? $_POST['body'] : '';
            if($body === ''){
                $code = 400;
                $json = array(
                    'error_msg' => 'no message body',
                );
                break;
            }
            $subject = (isset($_POST['subject'])) ? $_POST['subject'] : '';
            $name = (isset($_POST['name'])) ? $_POST['name'] : null;
            $name = ($name === '') ? 'Helmar Brewing Markplace User' : $name;
            $body .= '
                    <hr>
                    <p style="color:#444; font-size:0.9em; line-height:1.5">You are receiving this email because you have a card listed on the helmarbrewing.com marketplace and another user is trying to contact you. This user DOES NOT have your email. You are free to reply to this email to respond to the user. At that point, they would have your contact information. Any communication would be occuring outside of helmarbrewing.com and Helmar Brewing cannnot be held responsible for any issues that may arise. We expect you to to be respectful to other members and not send obscene or explicit communication. We recommend you follow safe trading practices.</p>
            ';
            require_once('libraries/drill/drill.php');
            $args = array(
                'key' => $apikey['mandrill'],
                'message' => array(
                    'html' => $body,
                    'from_email' => 'marketplace@helmarbrewing.com',
                    'from_name' => $name,
                    'subject' => $subject,
                    'to' => array(
                        array(
                            'email' => $user_to_send_to->email
                        )
                    ),
                    'headers' => array(
                        'Reply-To' => $user->email
                    ),
                    'track_opens' => true,
                    'track_clicks' => false,
                    'auto_text' => true
                )
            );
            try{
                $mandrill = new \Gajus\Drill\Client($apikey['mandrill']);
                $mandrill_response = $mandrill->api('messages/send', $args);
                $code = 200;
            } catch (\Gajus\Drill\Exception\RuntimeException\ValidationErrorException $e) {
                // @see https://mandrillapp.com/api/docs/messages.html
                $code = 500;
                $json = array(
                    'error_msg' => 'madrill ValidationError'
                );
            } catch (\Gajus\Drill\Exception\RuntimeException\UserErrorException $e) {
                // @see https://mandrillapp.com/api/docs/messages.html
                $code = 500;
                $json = array(
                    'error_msg' => 'madrill UserError'
                );
            } catch (\Gajus\Drill\Exception\RuntimeException\UnknownSubaccountException $e) {
                // @see https://mandrillapp.com/api/docs/messages.html
                $code = 500;
                $json = array(
                    'error_msg' => 'madrill UnknownSubaccount'
                );
            } catch (\Gajus\Drill\Exception\RuntimeException\PaymentRequiredException $e) {
                // @see https://mandrillapp.com/api/docs/messages.html
                $code = 500;
                $json = array(
                    'error_msg' => 'mandrill PaymentRequired'
                );
            } catch (\Gajus\Drill\Exception\RuntimeException\GeneralErrorException $e) {
                // @see https://mandrillapp.com/api/docs/messages.html
                $code = 500;
                $json = array(
                    'error_msg' => ''
                );
            } catch (\Gajus\Drill\Exception\RuntimeException\ValidationErrorException $e) {
                // @see https://mandrillapp.com/api/docs/messages.html
                $code = 500;
                $json = array(
                    'error_msg' => 'mandrill GeneralError'
                );
            } catch (\Gajus\Drill\Exception\RuntimeException $e) {
                // All possible API errors.
                $code = 500;
                $json = array(
                    'error_msg' => 'mandrill apiError'
                );
            } catch (\Gajus\Drill\Exception\InvalidArgumentException $e) {
                // Invalid SDK use errors.
                $code = 500;
                $json = array(
                    'error_msg' => 'mandrill InvalidArgument'
                );
            } catch (\Gajus\Drill\Exception\DrillException $e) {
                // Everything.
                $code = 500;
                $json = array(
                    'error_msg' => 'mandrill DrillException'
                );
            }
        }else{
            $code = 400;
            $json = array(
                'error_msg' => 'bad request method'
            );
        }

    }while(false);

    header_remove();
    http_response_code($code);
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-type: application/json');
    header('Status: '.$code);
    print json_encode($json);
    ob_end_flush();
?>
