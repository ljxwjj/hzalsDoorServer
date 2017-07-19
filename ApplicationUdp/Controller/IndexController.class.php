<?php
namespace Udp\Controller;
use Think\Controller;
class IndexController extends Controller\RestController {
    public function index($ip = '', $port = '', $data = ''){
        //$cmd = "3aa3000000000015000602c003000000";//b175
        //echo strCRCHex($cmd);exit;

        \Think\Log::record("收到信息：ip: $ip data: $data");

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
            echo "db save ---- CRC right";
            \Think\Log::record("last connect time db save ---- CRC right");
        } else {
            echo "db no save ---- CRC ERROIR";
            \Think\Log::record("last connect time db no save ---- CRC ERROIR");
        }
    }

    public function openDoorFeedback($ip = '', $port = '', $data = '') {
        \Think\Log::record("收到开门反馈：ip: $ip data: $data");

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
            $MDoorController = M('DoorController');
            $map['serial_number'] = $addr;
            $map['status'] = 0;
            $controllerData = $MDoorController->where($map)->find();
            $controller_id = $controllerData['id'];
            if ($controllerData) {
                $Model = M('OpenRecord');
                $openRecordData = $Model->where(array('controller_id'=>$controller_id))->order('id desc')->find();
                $nowtime = time();
                if ($openRecordData) {
                    if (($openRecordData['open_time'] + 5) > $nowtime) {
                        $openRecordData['feedback_time'] = time();
                        $Model->save($openRecordData);
                    } else {
                        $error = "feedback timeout ---- CRC right";
                    }
                } else {
                    $error = "openRecord data unfond ---- CRC right";
                }
            } else {
                $error = "controller data unfond ---- CRC right";
            }
        } else {
            $error = "open record time unupdate  ---- CRC ERROIR";
        }
        if ($error) {
            echo $error;
            \Think\Log::record($error);
        } else {
            echo "open record time update ---- CRC right";
            \Think\Log::record("open record time update ---- CRC right");
        }
    }

}