<?php

if (DIRECTORY_SEPARATOR === "/") {
    require_once '/data/wwwroot/workerman/Autoloader.php';
} else {
    require_once 'C:/AppServ/workerman/Autoloader.php';
}
require_once './ApplicationUdp/Common/function.php';

use Workerman\Worker;

// 将屏幕打印输出到Worker::$stdoutFile指定的文件中
Worker::$stdoutFile = './log/stdout.log';

// 创建一个Worker监听2346端口，使用websocket协议通讯
//$ws_worker = new Worker("udp://0.0.0.0:9998");
$ws_worker = new Worker("udp://0.0.0.0:9998");
$udpConnectionsCache = array();

// 启动4个进程对外提供服务
$ws_worker->count = 4;

// 当收到客户端发来的数据后返回hello $data给客户端
$ws_worker->onMessage = function($connection, $data)
{
    $ip = $connection->getRemoteIp();
    $port = $connection->getRemotePort();

    $unpackData = unpack("H*", $data);
    var_dump($unpackData);
    _log($unpackData[1]);

    if (strpos($unpackData[1], "3aa3") === 0) {
        paresRemoteMessage($connection, $ip, $port, $unpackData[1]);

        global $udpConnectionsCache;
        $udpConnectionsCache[$ip] = $connection;
    } else if (strpos($unpackData[1], "3003") === 0 && strcmp($ip, "127.0.0.1") === 0) {
        paresLocalMessage($data);
    }
    // 向客户端发送hello $data
    //$connection->send('hello' . $data."\n");
};

function paresRemoteMessage($connection, $ip, $port, $data) {
    $binData = hex2bin($data);

    $unData = unpack("C*", $binData);
    $i = 0;
    $syn = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);
    $res = sprintf("%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i]);
    $ptrol = sprintf("%02x", $unData[++$i]);
    if ($ptrol === '01') {
        $addr = sprintf("%02x%02x%02x%02x%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);
    }
    $slen = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);
    $commondLength = hexdec($slen);
    $command = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);
    $appdata = "";
    for ($l = 0; $l < $commondLength - 2; $l++) {
        $appdata .= sprintf("%02x", $unData[++$i]);
    }
    $crc16 = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);

    $crcstr = "";
    for ($m = 0; $m < count($unData) - 2; $m++) {
        $crcstr .= chr($unData[$m+1]);
    }
    $crcstr = getCRChex($crcstr);

    if ($crcstr === $crc16) {echo "begin login";
        if ($command == "0901") {// 登录
            $binData = hex2bin($data);

            $unData = unpack("C*", $binData);
            $i = 0;
            $deviceType = sprintf("%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);
            $softVerson = sprintf("%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);
            $deviceAdd = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);
            $serialNumber = sprintf("%02x%02x%02x%02x%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);
            $accessToken = sprintf("%02x%02x%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);

            $cmd = "090100";// 登录成功
            $msg = "3aa3000000".$ptrol.$addr.sprintf("%04x", strlen($cmd)/2).$cmd;
            echo "\n";echo $msg;echo "\n";
            $msg = hex2bin($msg);
            $connection->send($msg);
            echo "login success sended";
        }

        exec("php door/udp.php /Index/index/ip/$ip/port/$port/data/$data", $info);
        _log($info[0]);
    } else {
        _log("CRC ERROR--");
    }
}

function paresLocalMessage($data) {
    $unData = unpack("C*", $data);
    //var_dump($unData);
    $i = 0;
    $syn = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);
    $command = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);
    if (strcmp($command, "0001") === 0) {//开门指令  300300017F000001270E015209150810000026010000
        $ip = sprintf("%d.%d.%d.%d", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);
        $port = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);
        $port = hexdec($port);
        $ptrol = sprintf("%02x", $unData[++$i]);
        if ($ptrol === '01') {
            $addr = sprintf("%02x%02x%02x%02x%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);
        }
        $doorId = sprintf("%02x", $unData[++$i]);
        $openTime = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);

        $cmd = "02C003".$doorId.$openTime;// 开门
        $msg = "3aa3000000".$ptrol.$addr.sprintf("%04x", strlen($cmd)/2).$cmd;

        $crc = strCRCHex($msg);

        echo "open door cmd:".$msg.$crc;
        $msg = hex2bin($msg.$crc);

        global $udpConnectionsCache;
        if (array_key_exists($ip, $udpConnectionsCache)) {
            $connection = $udpConnectionsCache[$ip];
            if ($connection) {
                $connection->send($msg);
                echo "open door cmd sended";
            }
        } else {
            echo "door is not on line!!!";
        }
    }
}

function _log($msg) {
    $date = date("Ymd");
    $logfile = dirname(__FILE__).DIRECTORY_SEPARATOR ."log".DIRECTORY_SEPARATOR. "$date-udp.log";
    file_put_contents($logfile, $msg."\n", FILE_APPEND);
}

// 运行
Worker::runAll();


