<?php
    $img = base64_decode($_GET['img']);
    $domain = substr($img, 0, 20);
    $ext = reset(explode('?', end(explode('.', strtolower($img)))));
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

    }else{
        http_response_code(404);
        echo 'unsupported domain';
        exit;
    }
?>
