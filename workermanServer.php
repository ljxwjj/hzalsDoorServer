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
    _log("-----------------------------------");
    _log($unpackData[1]);

    if (strpos($unpackData[1], "3aa3") === 0) {
        paresRemoteMessage($connection, $ip, $port, $unpackData[1]);
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

    _log("++++++ remote:  ".$addr. "  ++++++");
    if ($crcstr === $crc16) {

        if ($command == "0901") {// 登录请求
            _log("begin login  ");
            $binData = hex2bin($appdata);

            $unData = unpack("C*", $binData);
            $i = 0;
            $deviceType = sprintf("%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);
            $softVerson = sprintf("%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);
            $deviceAdd = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);
            $serialNumber = sprintf("%02x%02x%02x%02x%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);
            $accessToken = sprintf("%02x%02x%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);

            $cmd = "090100";// 登录成功
            $msg = "3aa3000000".$ptrol.$addr.sprintf("%04x", strlen($cmd)/2).$cmd;
            $crc = strCRCHex($msg);
            echo "\n";echo "login feedback cmd: ".$msg.$crc;echo "\n";
            $msg = hex2bin($msg.$crc);
            $connection->send($msg);
            _log("login success sended \n") ;
        } else if ($command == "0902") { // 心跳包
            _log("budong budong budong ..... \n");
        } else if ($command == "0903") { // 主动数据上报
            _log("upload data data data .....\n");

            $binData = hex2bin($appdata);

            $unData = unpack("C*", $binData);
            $i = 0;
            $transactionNo = sprintf("%02x", $unData[++$i]);// 上传流水号
            $recordCount = sprintf("%02x", $unData[++$i]); // 返回记录数
            $recordLength = sprintf("%02x", $unData[++$i]); // 单条记录长度
            if ($recordLength == '1a') {
                $recodeArray = array();
                for ($m = 0; $m < $recordCount; $m++) {
                    $record = array();
                    $dakaResult = sprintf("%02x", $unData[++$i]);
                    $record['swing_card_result'] = substr($dakaResult, 1, 1); // 打卡结果
                    $record['swing_card_flag'] = substr($dakaResult, 0, 1); // 内部标识
                    $record['event_name'] = sprintf("%02x", $unData[++$i]);// 事件名称
                    $record['transaction_number'] = sprintf("%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);// 解析终端机交易流水号
                    $record['year'] = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);// 解析年份
                    $record['month'] = sprintf("%02x", $unData[++$i]);// 解析月份
                    $record['day'] = sprintf("%02x", $unData[++$i]);// 解析日
                    $record['hour'] = sprintf("%02x", $unData[++$i]);// 解析小时
                    $record['minute'] = sprintf("%02x", $unData[++$i]);// 解析分钟
                    $record['second'] = sprintf("%02x", $unData[++$i]);// 解析秒
                    $record['carad_number'] = sprintf("%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]);// 解析卡号
                    $record['door_number'] = sprintf("%02x", $unData[++$i]);// 解析门编号
                    $record['read_head_number'] = sprintf("%02x", $unData[++$i]);// 解析读头编号
                    $record['in_out_channel_number'] = sprintf("%02x", $unData[++$i]);// 解析输入输出通道编号
                    $record['password_type'] = sprintf("%02x", $unData[++$i]);// 解析密码类型
                    $record['open_password'] = sprintf("%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i]);//解析开门密码
                    $record['door_actual_status'] = sprintf("%02x", $unData[++$i]);//解析门实时状态
                    $record['door_action_status'] = sprintf("%02x", $unData[++$i]);//解析门动作状态
                    $recodeArray[] = $record;
                }

                $cmd = "0903";// 主动数据上报接收成功
                $cmd .= $transactionNo;
                $cmd .= $recordCount;
                $cmd .= "00"; // 成功
                $msg = "3aa3000000".$ptrol.$addr.sprintf("%04x", strlen($cmd)/2).$cmd;
                $crc = strCRCHex($msg);
                echo "\n";echo "data upload feedback cmd: ".$msg.$crc;echo "\n";
                $msg = hex2bin($msg.$crc);
                $connection->send($msg);
                _log("data upload feedback send success \n") ;

            } else {
                _log("unknow record length \n");
            }
        } else if ($command == "02c0") { // 开门反馈
            _log("open door success feedback \n");
            exec("php door/udp.php /Index/openDoorFeedback/ip/$ip/port/$port/data/$data", $info);
            _log($info[0]);
        } else {
            _log("unknow command !!!\n");
        }

        global $udpConnectionsCache;
        $udpConnectionsCache[$addr] = $connection;

        $info = array();
        exec("php door/udp.php /Index/index/ip/$ip/port/$port/data/$data", $info);
        _log($info[0]);
    } else {
        _log("CRC ERROR--  $crcstr === $crc16 \n");
    }
}

function paresLocalMessage($data) {
    $unData = unpack("C*", $data);
    $i = 0;
    $syn = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);
    $command = sprintf("%02x%02x", $unData[++$i], $unData[++$i]);
    if (strcmp($command, "0001") === 0) {//开门指令  300300017F000001270E015209150810000026010000
        _log( "received open door command ");
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

        _log( "++ serial_number ：$addr  ++ ");
        $crc = strCRCHex($msg);

        echo "open door cmd:".$msg.$crc;
        $msg = hex2bin($msg.$crc);

        global $udpConnectionsCache;
        if (array_key_exists($addr, $udpConnectionsCache)) {
            $connection = $udpConnectionsCache[$addr];
            if ($connection) {
                $connection->send($msg);
                _log( "open door cmd sended \n");
            }
        } else {
            _log("door is not on line!!! \n");
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


