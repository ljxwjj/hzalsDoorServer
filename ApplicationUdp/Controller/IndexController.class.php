<?php
namespace Udp\Controller;
use Think\Controller;
class IndexController extends Controller\RestController {
    public function index($ip = '', $port = '', $data = ''){
        //$cmd = "3aa3000000000015000602c003000000";//b175
        //echo strCRCHex($cmd);exit;

        echo "收到信息：ip: $ip data: $data";

        $binData = hex2bin($data);

        $unData = unpack("C*", $binData);
        $i = 0;
        //dechex($unData[1]) . dechex($unData[2]);
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
        if ($crcstr === $crc16) {
            echo "----CRC right";
            $MDoorController = M('DoorController');
            $map['serial_number'] = $addr;
            $map['status'] = 0;
            $controllerData = $MDoorController->where($map)->find();
            if ($controllerData) {
                $controllerData['ip'] = $ip;
                $controllerData['port'] = $port;
                $controllerData['last_connect_time'] = time();
                $MDoorController->save($controllerData);
            } else {
                $controllerData['serial_number'] = $addr;
                $controllerData['ip'] = $ip;
                $controllerData['port'] = $port;
                $controllerData['last_connect_time'] = time();
                $controllerData['add_time'] = time();
                $controllerData['status'] = 0;
                $MDoorController->add($controllerData);
            }
        } else {
            \Think\Log::record("CRC ERROIR--  ip: $ip  port: $port  data: $data");
        }
    }

}