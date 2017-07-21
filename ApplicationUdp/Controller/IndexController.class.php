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
            \Think\Log::record("db save ---- CRC right");
        } else {
            echo "db no save ---- CRC ERROIR";
            \Think\Log::record("db no save ---- CRC ERROIR");
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

    public function swingCord($serial_number, $data) {
        \Think\Log::record("收到刷卡请求：serial number: $serial_number data: $data");
        $MDoorController = M('DoorController');
        $map = array();
        $map['serial_number'] = $serial_number;
        $map['status'] = 0;
        $controllerData = $MDoorController->where($map)->find();

        if ($controllerData) {
            $controller_id = $controllerData['id'];
            $company_id = $controllerData['company_id'];

            $binData = hex2bin($data);

            $unData = unpack("C*", $binData);
            $i = 0;
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

            $MUser = M('User');
            $map = array();
            $map['company_id'] = $company_id;
            $map['status'] = 1;
            $map['token'] = $record['carad_number'];
            $tokenData = $MUser
                ->field("user.id AS user_id, user_qrcode.update_time AS update_time")
                ->join("join user_qrcode on user.id = user_qrcode.user_id")
                ->where($map)->find();
            if ($tokenData) {
                if ($tokenData['update_time'] + 30 >= time()) {
                    $user_id = $tokenData['user_id'];

                    $openRecord['controller_id'] = $controller_id;
                    $openRecord['door_id'] = $record['door_number'];
                    $openRecord['open_time'] = time();
                    $openRecord['user_id'] = $user_id;
                    $openRecord['way'] = 2;
                    M('OpenRecord')->add($openRecord);

                    $wait = intval($controllerData['wait_time']);
                    $this->sendOpenDoorUdpCode($controllerData['ip'], $controllerData['port'], $controllerData['serial_number'], $record['door_number'], $wait);

                    $message = "check success, send open door";
                } else {
                    $message = "The verification code has expired";
                }
            } else {
                $message = "unfond qrcode data OR user is left office";
            }
        } else {
            $message = "unfond door contooler serial_number";
        }
        echo $message;
        \Think\Log::record("刷卡请求 处理结果：$message");
    }

    protected function sendOpenDoorUdpCode($ip, $port, $serialNumber, $doorId, $wait) {
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
}