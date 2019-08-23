<?php


function sendOpenDoorUdpCode($ip, $port, $serialNumber, $doorId, $wait) {
    $handle = stream_socket_client("udp://127.0.0.1:9998", $errno, $errstr);
    if( !$handle ){
        die("ERROR: {$errno} - {$errstr}\n");
    }
    $sendMsg = "30030001"; // 开门指令
    $ips = explode(".", $ip);
    foreach ($ips as $i) {
        $sendMsg .= sprintf("%02x", $i);
    }
    $sendMsg .= sprintf("%04x", $port);
    $sendMsg .= "01";
    $sendMsg .= $serialNumber;
    $sendMsg .= sprintf("%02x", $doorId);
    $sendMsg .= sprintf("%04x", $wait);
    $sendMsg = hex2bin($sendMsg);
    fwrite($handle, $sendMsg);
    fclose($handle);
}

function sendOpenDoorHttp($ip, $port, $doorId, $password) {
    $opts = array(
        'http'=>array('method'=>'GET', 'timeout'=>5)
    );
    //创建数据流上下文
    $context = stream_context_create($opts);
    if (empty($password)) {
        $url = "http://$ip:$port/t.cgi?T,access_io,door,$doorId,1";
    } else {
        $url = "http://$ip:$port/t.cgi?T$password,access_io,door,$doorId,1";
    }
    $html =file_get_contents($url, false, $context);
    if (strcasecmp($html, "ok") === 0) {
        return true;
    }
    return false;
}

