<?php

    $seconds_to_cache = 60*60*24*7;
    $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";

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
                header("Expires: $ts");
                header("Pragma: cache");
                header("Cache-Control: max-age=$seconds_to_cache");
                header('Content-Type: image/jpeg');
                break;

            case 'jpeg':
                header("Expires: $ts");
                header("Pragma: cache");
                header("Cache-Control: max-age=$seconds_to_cache");
                header('Content-Type: image/jpeg');
                break;

            case 'png':
                header("Expires: $ts");
                header("Pragma: cache");
                header("Cache-Control: max-age=$seconds_to_cache");
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
