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

        $owner_of_card_id = (isset($_GET['owner_of_card_id'])) ? $_GET['owner_of_card_id'] : null;
        if($owner_of_card_id === null){
            $code = 400;
            break;
        }

        $owner_of_card = get_user_sending_message_to($owner_of_card_id);
        if($owner_of_card === false){
            $code = 404;
            break;
        }

        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            $to_line = '';
            $to_line .= ($owner_of_card->firstname !== '' && $owner_of_card->firstname !== null) ? $owner_of_card->firstname : '';
            $to_line .= ($to_line !== '' && $owner_of_card->state !== '' && $owner_of_card->state !== null) ? ' from ' . $owner_of_card->state : '';

            $json = array(
                'from_name' => $user->firstname.' '.$user->lastname,
                'from_email' => $user->email,
                'to_line' => $to_line
            );
            $code = 200;
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
