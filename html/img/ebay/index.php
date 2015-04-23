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

        $ch = curl_init($img);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        $rawdata=curl_exec ($ch);
        curl_close ($ch);

        echo $rawdata;

    }else{
        http_response_code(404);
        echo 'unsupported domain';
        exit;
    }
?>
