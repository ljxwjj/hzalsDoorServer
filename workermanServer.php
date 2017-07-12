<?php
//require_once 'C:/AppServ/workerman/Autoloader.php';
require_once '/data/webroot/workerman/Autoloader.php';
use Workerman\Worker;

// 创建一个Worker监听2346端口，使用websocket协议通讯
//$ws_worker = new Worker("udp://0.0.0.0:9998");
$ws_worker = new Worker("udp://139.196.97.237:9998");

// 启动4个进程对外提供服务
$ws_worker->count = 4;

// 当收到客户端发来的数据后返回hello $data给客户端
$ws_worker->onMessage = function($connection, $data)
{
    // 向客户端发送hello $data
    $connection->send('hello ' . $data."\n");
};

// 运行
Worker::runAll();
