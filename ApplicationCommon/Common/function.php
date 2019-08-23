<?php
require_once 'door_control.php';

/**
 * 解决 strrev 函数中文乱码的问题
 * @param $str
 * @return string
 */
function utf8_strrev($str){
    preg_match_all('/./us', $str, $ar);
    return join('',array_reverse($ar[0]));
}

function isWorkDay($day) {
    $time = strtotime($day);
    $year = date("Y", $time);
    $mmdd = date("md", $time);
    $week = date("w", $time);

    if (is_array($GLOBALS["jiari_data"][$year])) {
        $jiari = $GLOBALS["jiari_data"][$year]["jiari"];
        $gongzuo = $GLOBALS["jiari_data"][$year]["gongzuo"];
    } else {
        $jiariFile = dirname(__FILE__)."/jiari/$year.txt";
        if (file_exists($jiariFile)) {
            $jiari = file_get_contents($jiariFile);
            $GLOBALS["jiari_data"][$year]["jiari"] = $jiari;
        } else {
            $jiari = file_get_contents("http://tool.bitefu.net/jiari/data/".$year.".txt");
            $GLOBALS["jiari_data"][$year]["jiari"] = $jiari;
            if (!empty($jiari)) file_put_contents($jiariFile, $jiari);
        }
        $gongzuoFile = dirname(__FILE__)."/jiari/".$year."_w.txt";
        if (file_exists($gongzuoFile)) {
            $gongzuo = file_get_contents($gongzuoFile);
            $GLOBALS["jiari_data"][$year]["gongzuo"] = $gongzuo;
        } else {
            $gongzuo = file_get_contents("http://tool.bitefu.net/jiari/data/".$year."_w.txt");
            $GLOBALS["jiari_data"][$year]["gongzuo"] = $gongzuo;
            if (!empty($gongzuo)) file_put_contents($gongzuoFile, $gongzuo);
        }
    }

    if (strpos($jiari, $mmdd) !== false) return false;
    if (strpos($gongzuo, $mmdd) !== false) return true;
    if ($week == "0" || $week == "6") return false;
    return true;
}

function dateToWeek($date) {
    $dateTime = strtotime($date);
    $week = date("w", $dateTime);
    switch ($week) {
        case "0":return "星期天";
        case "1":return "星期一";
        case "2":return "星期二";
        case "3":return "星期三";
        case "4":return "星期四";
        case "5":return "星期五";
        case "6":return "星期六";
    }
}

function showOpenDoorWay($way) {
    switch ($way) {
        case 1: return "远程";
        case 2: return "二维码";
        case 3: return "代理商授权";
        case 4: return "分享码";
        case 5: return "刷卡";
        case 6: return "密码";
        case 7: return "代理商授权-关门";
        case 8: return "人脸识别";
    }
}

function timeToTimeLong($hi) {// 时间转当天时长
    $hiTime = strtotime($hi);
    $todayTime = strtotime(date("Y-m-d", $hiTime));
    return $hiTime - $todayTime;
}

function dateToTimeLong($date) {
    $todayTime = strtotime(date("Y-m-d", $date));
    return $date - $todayTime;
}

function timeLongToDate($timeLong) {
    $todayTime = strtotime(date("Y-m-d"));
    return $todayTime + $timeLong;
}

function toWorkHours($time) {
    $h = intval($time/60/60);
    $i = intval(($time%(60*60))/60);
    if ($h > 0) return $h."时".$i."分";
    else if ($i > 0) return $i."分";
    else return "";
}

function serialNumberToEncoded($serialNumber, $length) {
    $encoded = md5($serialNumber.$length);
    $salt = substr($encoded, $length, 2);
    for ($i = 0; $i < $length; $i++) {
        $encoded = crypt($encoded, $salt);
        $encoded = md5($encoded);
        $resultArray[] = substr($encoded, $i, 1);
    }
    return implode("", $resultArray);
}

function jpush($alert = 'Hello, JPush', $uri = false) {
    Vendor('jpush-api/autoload', COMMON_PATH . 'Vendor/', '.php');
    $client = new \JPush\Client(C('jpush_appkey'), C('jpush_secret'));
    $pusher = $client->push();
    $pusher->setPlatform('all')
        ->addAllAudience()
        //->addTag('all')
        ->options(['apns_production'=>true])
        ->setNotificationAlert($alert);
    if ($uri) {
        $pusher->androidNotification($alert, array("extras"=>array("uri"=>$uri)));
        $pusher->iosNotification($alert, array("extras"=>array("uri"=>$uri)));
    }
    try {
        $pusher->send();
    } catch (\JPush\Exceptions\JPushException $e) {
        print $e;
    }
}

function jpushToUser($rid, $alert = 'Hello, JPush', $uri = false) {
    Vendor('jpush-api/autoload', COMMON_PATH . 'Vendor/', '.php');
    $client = new \JPush\Client(C('jpush_appkey'), C('jpush_secret'));
    $pusher = $client->push();
    $pusher->setPlatform('all')
        ->addRegistrationId($rid)
        ->options(['apns_production'=>true])
        ->setNotificationAlert($alert);
    if ($uri) {
        $pusher->androidNotification($alert, array("extras"=>array("uri"=>$uri)));
        $pusher->iosNotification($alert, array("extras"=>array("uri"=>$uri)));
    }
    try {
        $pusher->send();
    } catch (\JPush\Exceptions\JPushException $e) {
        print $e;
    }
}

function queryDoorStatusByUdp($ip, $port, $serialNumber, $timeout = 1) {
    $starttime = explode(' ', microtime());
    $handle = stream_socket_client("udp://127.0.0.1:9998", $errno, $errstr);
    if( !$handle ){
        die("ERROR: {$errno} - {$errstr}\n");
    }
    $sendMsg = "30030005"; // 查询门禁状态指令
    $ips = explode(".", $ip);
    foreach ($ips as $i) {
        $sendMsg .= sprintf("%02x", $i);
    }
    $sendMsg .= sprintf("%04x", $port);
    $sendMsg .= "01";
    $sendMsg .= $serialNumber;
    $binMsg = hex2bin($sendMsg);
    fwrite($handle, $binMsg);
    fclose($handle);

    $UdpOperationModel = M('UdpOperation');
    $udpOperation['serial_number'] = $serialNumber;
    $udpOperation['command'] = $sendMsg;
    $udpOperation['command_key'] = 'queryDoorStatus';
    $udpOperation['create_time'] = time();
    $addid = $UdpOperationModel->add($udpOperation);

    if (!$addid) {
        return false;
    }
    do {
        usleep(0.2 * 1000 * 1000);
        $data = $UdpOperationModel->getById($addid);
        if ($data['feedback_time'] > 0) {
            return $data['result_key'];
        }
        $endtime = explode(' ', microtime());
        $usetime = $endtime[0] + $endtime[1] - $starttime[0] - $starttime[1];
    } while($usetime < $timeout);

    return false;
}

function base64EncodeImage($filePath) {
    $image_info = getimagesize($filePath);
    $image_data = fread(fopen($filePath, 'r'), filesize($filePath));
    //$base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    $base64_image = chunk_split(base64_encode($image_data));
    return $base64_image;
}

function callAPI_post($baseUrl, $path, $headers, $data) {
    Vendor('Requests', COMMON_PATH . 'Vendor/Requests-1.7.0/library/', '.php');
    Requests::register_autoloader();

    if (is_string($path)) {
        $url = $baseUrl.$path;
    } else if (is_array($path)) {
        $url = $baseUrl.implode("", $path);
    }
    if ($url) {
        $response = Requests::post($url, $headers, $data);
//        var_dump($response->body);
        return $response->body;
    }
}

function callAPI_delete($baseUrl, $path, $headers, $data) {
    Vendor('Requests', COMMON_PATH . 'Vendor/Requests-1.7.0/library/', '.php');
    Requests::register_autoloader();

    $url = $baseUrl;
    if (is_string($path)) {
        $url .= $path;
    } else if (is_array($path)) {
        $url .= implode("", $path);
    }
    if ($data) {
        $url .= '?'.http_build_query($data);
    }
    if ($url) {
        $response = Requests::delete($url, $headers);
        return $response->body;
    }
}

function callAPI_get($baseUrl, $path, $headers, $data) {
    Vendor('Requests', COMMON_PATH. "Vendor/Requests-1.7.0/library/", '.php');
    Requests::register_autoloader();

    $url = $baseUrl;
    if (is_string($path)) {
        $url .= $path;
    } else if (is_array($path)) {
        $url .= implode("", $path);
    }
    if ($data) {
        $url .= '?'.http_build_query($data);
    }
    if ($url) {
        $response = Requests::get($url, $headers);
        return $response->body;
    }
}

function callAPI_put($baseUrl, $path, $headers, $data) {
    Vendor('Requests', COMMON_PATH . 'Vendor/Requests-1.7.0/library/', '.php');
    Requests::register_autoloader();

    if (is_string($path)) {
        $url = $baseUrl.$path;
    } else if (is_array($path)) {
        $url = $baseUrl.implode("", $path);
    }
    if ($url) {
        $response = Requests::put($url, $headers, $data);
        return $response->body;
    }
}

function ufacePostApi($path, $data) {
    $baseUrl = "http://gs-api.uface.uni-ubi.com/v1/";
    return callAPI_post($baseUrl, $path, array(), $data);
}

function ufaceDeleteApi($path, $data) {
    $baseUrl = "http://gs-api.uface.uni-ubi.com/v1/";
    return callAPI_delete($baseUrl, $path, array(), $data);
}

function ufaceGetApi($path, $data) {
    $baseUrl = "http://gs-api.uface.uni-ubi.com/v1/";
    return callAPI_get($baseUrl, $path, array(), $data);
}

function ufacePutApi($path, $data) {
    $baseUrl = "http://gs-api.uface.uni-ubi.com/v1/";
    return callAPI_put($baseUrl, $path, array(), $data);
}

function ufaceApi($method, $path, $data) {
    $m = "callAPI_$method";
    $baseUrl = "http://gs-api.uface.uni-ubi.com/v1/";
    return $m($baseUrl, $path, array(), $data);
}

function ufaceAuth() {
    $appId = C('UFACE_APP_ID');
    $data = array(
        'appId' => $appId,
        'appKey' => C('UFACE_APP_KEY'),
        'timestamp' => C('UFACE_CREATE_TIME'),
    );
    $sign = md5($data['appKey'].$data['timestamp'].C('UFACE_APP_SECRET'));
    $data['sign'] = $sign;
    $body = ufacePostApi(array($appId, "/auth"), $data);
    $jsonArray = json_decode($body);
    if ($jsonArray->result === 1) {
        $token = $jsonArray->data;
        S('UFACE_AUTH_TOKEN', $token, 10*60*60);
    }
}

function ufaceApiAutoParams($method, $path, $data) {
    $token = S('UFACE_AUTH_TOKEN');
    if (!$token) {
        ufaceAuth();
        $token = S('UFACE_AUTH_TOKEN');
    }
    if (!$data['appId']) {
        $data['appId'] = C('UFACE_APP_ID');
    }
    $data['token'] = $token;
    $body = ufaceApi($method, $path, $data);
    $jsonArray = json_decode($body);
    if ($jsonArray->code == 'GS_EXP-102') {
        ufaceAuth();
        $token = S('UFACE_AUTH_TOKEN');
        $data['token'] = $token;
        $body = ufaceApi($method, $path, $data);
        $jsonArray = json_decode($body);
    }
    return $jsonArray;
}

function deleteUfaceUser($userId) {
    $model = M("UfaceUser");
    $guid = $model->where("user_id=$userId")->getField("uface_guid");
    if ($guid) {
        $response = ufaceApiAutoParams("delete", array(
            C('UFACE_APP_ID'), "/person/", $guid
        ), array(
            'appId' => C('UFACE_APP_ID'),
            'guid'  => $guid
        ));

        if ($response->result == 1) {
            $model->where("user_id=$userId")->delete();
        }
    }

}
