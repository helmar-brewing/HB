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

        $send_to_user_id = (isset($_GET['send_to_user_id'])) ? $_GET['send_to_user_id'] : null;
        if($send_to_user_id === null){
            $code = 400;
            break;
        }

        $user_to_send_to = get_user_sending_message_to($send_to_user_id);
        if($user_to_send_to === false){
            $code = 404;
            break;
        }

        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            $to_line = '';
            $to_line .= ($user_to_send_to->firstname !== '' && $user_to_send_to->firstname !== null) ? $user_to_send_to->firstname : '';
            $to_line .= ($to_line !== '' && $user_to_send_to->state !== '' && $user_to_send_to->state !== null) ? ' from ' . $user_to_send_to->state : '';
            $json = array(
                'from_name' => $user->firstname.' '.$user->lastname,
                'from_email' => $user->email,
                'to_line' => $to_line
            );
            $code = 200;
        }elseif($_SERVER['REQUEST_METHOD'] === 'POST'){

            require_once('libraries/drill/drill.php');

            $args = array(
                'key' => $apikey['mandrill'],
                'message' => array(
                    'html' => '<p>Test.</p>',
                    'from_email' => 'marketplace@helmarbrewing.com',
                    'from_name' => 'TestName',
                    'subject' => 'Test Subject',
                    'to' => $user_to_send_to->email,
                    'headers' => array(
                        'Reply-To' => $user->email
                    ),
                    'track_opens' => true,
                    'track_clicks' => false,
                    'auto_text' => true
                )
            );

            $mandrill = new \Gajus\Drill\Client($apikey['mandrill']);
            $mandrill_response = $mandrill->api('messages/send', $args);

            if($mandrill_response['status'] === 'error'){
                $code = 500;
            }else{
                $code = 200;
            }


        }else{
            $code = 400;
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
