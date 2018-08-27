<?php

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
        //->addAllAudience()
        ->addTag('all')
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