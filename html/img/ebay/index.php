<?php
    $img = base64_decode($_GET['img']);
    $domain = substr($img, 0, 20);

    $ext = strtolower($img);
    $ext = explode('.', $ext);
    $ext = end($ext);
    $ext = explode('?', $ext);
    $ext = $ext[0];

    if($domain == 'http://i.ebayimg.com'){

        switch($ext){
            case 'jpg':
                header('Content-Type: image/jpeg');
                break;

            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;

            case 'png':
                header('Content-Type: image/png');
                break;

            case 'gif':
                header('Content-Type: image/gif');
                break;

            default:
            http_response_code(404);
            echo 'unsupported type';
            exit;
        }

        echo file_get_contents($img);
        exit;

    }else{
        http_response_code(404);
        echo 'unsupported domain';
        exit;
    }
?>
