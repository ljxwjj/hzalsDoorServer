<?php

if (DIRECTORY_SEPARATOR === "/") {
    require_once '/data/wwwroot/workerman/Autoloader.php';
} else {
    require_once 'C:/AppServ/workerman/Autoloader.php';
}

use Workerman\Worker;

// 创建一个Worker监听2346端口，使用websocket协议通讯
//$ws_worker = new Worker("udp://0.0.0.0:9998");
$ws_worker = new Worker("udp://0.0.0.0:9998");

// 启动4个进程对外提供服务
$ws_worker->count = 4;

// 当收到客户端发来的数据后返回hello $data给客户端
$ws_worker->onMessage = function($connection, $data)
{
    $date = date("Ymd");
    $logfile = dirname(__FILE__).DIRECTORY_SEPARATOR ."log".DIRECTORY_SEPARATOR. "$date-udp.log";
    $unpackData = unpack("H*", $data);
    file_put_contents($logfile, $unpackData[1]."\n", FILE_APPEND);
    exec("php door/udp.php /Index/index/data/$data", $info);
    file_put_contents($logfile, $info[0]."\n", FILE_APPEND);
    // 向客户端发送hello $data
    $connection->send('hello' . $data."\n");
};

// 运行
Worker::runAll();
