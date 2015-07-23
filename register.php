<?php
require_once('/home/sean/proj/common/orm.php');

header('Content-Type: application/json');

$accepted_mime_types = array(
    'image/jpeg',
    'image/png'
);

//Need to extend exception to accept http response code parameter
//Continue sanitizing/checking everything

try {
    if (!isset($_FILES['media'])) {
        throw new Exception('File element media not found".');
    }
    
    $media = $_FILES['media'];

    if ($media['error'] !== 0) {
        throw new Exception('Error in file media: "'.$media['error'].'".');
    }
    if (!in_array($media['type'], $accepted_mime_types)) {
        //http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
        //10.4.16 415 Unsupported Media Type
        //The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method.
        http_response_code(415);
        throw new Exception('Unsupported file type. Supported MIME types: "'. print_r($accepted_mime_types).'"');
    }

    //This was originally md5_file(), but that looks like it's quadratic expensive or something.
    //This looks like it's a few orders of magnitude faster, although the output is only 36^8 (2.8e12) (2.8 trillion).
    //Good enough for now! This will surely never be an issue in the future!
    $hash = hash_file('fnv132', $media['tmp_name']);
    $photo_data = file($media['tmp_name']);
    //file_put_contents('fnord', $photo_data);
    print_r($media);
    print_r($_REQUEST);

    //insert into media (user_id, x, y, checkmark, file_hash, file_extension) values ();
} catch (Exception $e) {
    echo json_encode(array(
        'msg' => $e->getMessage()
    ));
}