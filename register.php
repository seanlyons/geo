<?php
require_once('/home/sean/proj/common/orm.php');

header('Content-Type: application/json');

$accepted_mime_types = array(
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'video/mp4' => 'mp4',
);

class Helper {
    
    function strpos_each($haystack, $needle) {
        if (!is_string($haystack)) {
            $haystack = (string) $haystack;
        } elseif (!is_scalar($haystack)) {
            throw new GeoException('Haystack is not a scalar.');
        }
        if (is_scalar($needle)) {
            $needle = array($needle);
        }
        $pos = array();
        $length = strlen($haystack);
        for($i = 0; $i <= $length; $i++) {
            if (in_array($haystack[$i], $needle)) {
                $pos[] = $i;
            }
        }
        if (empty($pos)) {
            $pos = NULL;
        }
        return $pos;
    }
    
    //Start and end are inclusive!
    function substr_between_pos($str, $start, $end) {
        if (!is_string($str)) {
            throw new Exception();
        }
        if (!is_integer($start) || !is_integer($end)) {
            throw new Exception();
        }
        if ($start >= $end) {
            throw new Exception();
        }
        $substr = substr($str, $start, $end - $start);
        
        return $substr;
    }
    
    function nth_api_arg() {
        $request_uri = $_SERVER['REQUEST_URI'];
    }
    
}

class GeoException extends Exception {
    public $response_code = 500;
    public $message = 'GeoException';
    
    public function __construct($message = 'GeoException', $response_code = 500) {
        $this->response_code = $response_code;
        $this->message = $message;
        parent::__construct($message);
    }
    
    public function display() {
        $display = array();
        
        $display['response_code'] = $this->return_response_code();
        $display['message'] = $this->display_message();
        
        echo json_encode($display);
    }
    
    public function return_response_code() {
        http_response_code($this->response_code);
        return $this->response_code;
    }
    
    public function display_message() {
        return $this->message;
    }
}

//Need to extend exception to accept http response code parameter
//Continue sanitizing/checking everything

try {
    print_r($_REQUEST);
    print_r($_SERVER);
    //TODO: sanitize/scrub request
    extract($_REQUEST);
    
    $checkmark = $neighborhood;
    $user_id = $uid;
    
    if (!isset($_FILES['media'])) {
        throw new GeoException('File element media not found".');
    }
    
    $media = $_FILES['media'];

    if ($media['error'] !== 0) {
        throw new GeoException('Error in file media: "'.$media['error'].'".');
    }
    if (!array_key_exists($media['type'], $accepted_mime_types)) {
        //http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
        //10.4.16 415 Unsupported Media Type
        //The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method.
        throw new GeoException('Unsupported file type. Supported MIME types: "'. print_r($accepted_mime_types).'"', 415);
    } else {
        $file_extension = $accepted_mime_types[$media['type']];
    }

    //This was originally md5_file(), but that looks like it's quadratic expensive or something.
    //This looks like it's a few orders of magnitude faster, although the output is only 36^8 (2.8e12) (2.8 trillion).
    //Good enough for now! This will surely never be an issue in the future!
    $file_hash = hash_file('fnv132', $media['tmp_name']);
    $photo_data = file($media['tmp_name']);
    $media_file_location = 'uploads/'.$file_hash.'.'.$file_extension;
    file_put_contents($media_file_location, $photo_data);
        
    $db = new Db;
    
    $details['verb'] = 'insert';
    $details['actor'] = 'geo_media';
    $details['relevant'] = array(
        'user_id' => $user_id,
        'x' => $x,
        'y' => $y,
        'checkmark' => $checkmark,
        'file_hash' => $file_hash,
        'file_extension' => $file_extension
    );
    $details['limit'] = 1;
    $outcome = $db->cmd( $details );    
} catch (GeoException $e) {
    $e->display();
}/* catch (Exception $e) {
    echo json_encode(array(
        'message' => $e->getMessage(),
        'response_code' => -1
    ));
}*/