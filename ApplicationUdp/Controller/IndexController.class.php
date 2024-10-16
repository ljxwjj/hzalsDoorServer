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
            $map['product_type'] = 2;
            $map['status'] = 0;
            $controllerData = $MDoorController->where($map)->find();
            if ($controllerData) {
                $controllerData['ip'] = $ip;
                $controllerData['port'] = $port;
                $controllerData['last_connect_time'] = time();
                if ($command == "0902") {// 心跳
                    $doorStatusStr = substr($appdata, 6, 2);
                    $doorStatusStr = sprintf("%08b", hexdec($doorStatusStr));
                    $controllerData['door_status'] = strrev($doorStatusStr);
                }
                $MDoorController->save($controllerData);

                if ($command == "0902") {// 心跳
                    $this->handleHeartbeatEvent($controllerData);
                }
            } else {
                $controllerData['serial_number'] = $addr;
                $controllerData['product_type'] = 2;
                $controllerData['ip'] = $ip;
                $controllerData['port'] = $port;
                $controllerData['door_count'] = substr($addr, 9, 1);
                $controllerData['last_connect_time'] = time();
                $controllerData['add_time'] = time();
                $controllerData['status'] = 0;
                $controllerData['wait_time'] = 3;
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
            $record['carad_number'] = ltrim(sprintf("%02x%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i], $unData[++$i]), "0");// 解析卡号
            $record['door_number'] = sprintf("%02x", $unData[++$i]);// 解析门编号
            $record['read_head_number'] = sprintf("%02x", $unData[++$i]);// 解析读头编号
            $record['in_out_channel_number'] = sprintf("%02x", $unData[++$i]);// 解析输入输出通道编号
            $record['password_type'] = sprintf("%02x", $unData[++$i]);// 解析密码类型
            $record['open_password'] = sprintf("%02x%02x%02x", $unData[++$i], $unData[++$i], $unData[++$i]);//解析开门密码
            $record['door_actual_status'] = sprintf("%02x", $unData[++$i]);//解析门实时状态
            $record['door_action_status'] = sprintf("%02x", $unData[++$i]);//解析门动作状态
            $door_id = intval($record['door_number']);

            $qrcode = $record['carad_number'];
            $codeType = hexdec($qrcode)&0b11;
            if ($codeType === 0) {
                $message = $this->swingQRCode($record, $controllerData, $door_id);
            } else if($codeType === 1) {
                $message = $this->swingShareQRCode($record, $controllerData, $door_id);
            }
        } else {
            $message = "unfond door contooler serial_number";
        }
        echo $message;
        \Think\Log::record("刷卡请求 处理结果：$message");
    }

    private function swingQRCode($record, $controllerData, $door_id) {
        $controller_id = $controllerData['id'];
        $company_id = $controllerData['company_id'];

        $MUser = M('User');
        $map = array();
        $map['status'] = 1;
        $map['token'] = $record['carad_number'];
        $tokenData = $MUser
            ->field("user.id AS user_id, user.company_id AS company_id, user.is_admin AS is_admin, user_qrcode.update_time AS update_time")
            ->join("join user_qrcode on user.id = user_qrcode.user_id")
            ->where($map)->find();
        if ($tokenData) {
            if ($tokenData['update_time'] + 30 >= time()) {
                $user_id = $tokenData['user_id'];
                $user_company = $tokenData['company_id'];

                if (!$tokenData['is_admin']) {
                    // 非系统管理员，验证权限
                    if ($user_company != $company_id) {
                        // 非系统管理员不能操作非本公司门禁
                        $message = "Non system admin, non operating, non company door ";
                        echo $message;
                        \Think\Log::record("刷卡请求 处理结果：$message");
                        exit;
                    }
                    $role_id = M('AuthRoleUser')->where(array('user_id' => $user_id))->getField('role_id');
                    if ($role_id > 21) { // > 21即非管理员用户
                        // 非公司管理员，验证权限
                        $userDoors = getUserDoors($user_id);
                        if (!$userDoors[$controller_id][$door_id]) {
                            $message = "Open door authorization failed ";
                            echo $message;
                            \Think\Log::record("刷卡请求 处理结果：$message");
                            exit;
                        }
                    }
                }

                $openRecord['company_id'] = $company_id;
                $openRecord['controller_id'] = $controller_id;
                $openRecord['door_id'] = $door_id;
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
        return $message;
    }

    private function swingShareQRCode($record, $controllerData, $door_id) {
        $controller_id = $controllerData['id'];
        $company_id = $controllerData['company_id'];

        $MUser = M('User');
        $map = array();
        $map['token'] = $record['carad_number'];
        $map['expiry_time'] = array("EGT", time());
        $tokenData = M('UserShareQrcode')->where($map)->order('create_time desc')->find();
        if ($tokenData) {
            $userData = $MUser->where(array('id'=>$tokenData['user_id'], 'status'=> 1))->find();
        }
        /*$tokenData = $MUser
            ->field("user.id AS user_id, user.company_id AS company_id, user.is_admin AS is_admin, user_share_qrcode.controller_id AS controller_id, user_share_qrcode.door_id AS door_id")
            ->join("join user_share_qrcode on user.id = user_share_qrcode.user_id")
            ->where($map)->find();*/
        if ($tokenData && $userData) {
            $user_id = $tokenData['user_id'];
            $user_company = $userData['company_id'];

            if ($tokenData['controller_id'] > -1 && $tokenData['controller_id'] != $controller_id) {
                // 未对此控制器授权
                $message = "Non authorize controller ";
                echo $message;
                \Think\Log::record("分享码刷卡请求 处理结果：$message");
                exit;
            }
            if ($tokenData['controller_id'] > -1 && $tokenData['door_id'] > -1 && $tokenData['door_id'] != $door_id) {
                // 未对此门授权
                $message = "Non authorize the door ";
                echo $message;
                \Think\Log::record("分享码刷卡请求 处理结果：$message");
                exit;
            }
            if (!$tokenData['is_admin']) {
                // 非系统管理员，验证权限
                if ($user_company != $company_id) {
                    // 非系统管理员不能操作非本公司门禁
                    $message = "Non system admin, non operating, non company door ";
                    echo $message;
                    \Think\Log::record("分享码刷卡请求 处理结果：$message");
                    exit;
                }
                $role_id = M('AuthRoleUser')->where(array('user_id' => $user_id))->getField('role_id');
                if ($role_id > 21) { // > 21即非管理员用户
                    // 非公司管理员，验证权限
                    $userDoors = getUserDoors($user_id);
                    if (!$userDoors[$controller_id][$door_id]) {
                        $message = "Open door authorization failed ";
                        echo $message;
                        \Think\Log::record("分享码刷卡请求 处理结果：$message");
                        exit;
                    }
                }
            }

            $openRecord['company_id'] = $company_id;
            $openRecord['controller_id'] = $controller_id;
            $openRecord['door_id'] = $door_id;
            $openRecord['open_time'] = time();
            $openRecord['user_id'] = $user_id;
            $openRecord['way'] = 4;
            M('OpenRecord')->add($openRecord);

            $wait = intval($controllerData['wait_time']);
            $this->sendOpenDoorUdpCode($controllerData['ip'], $controllerData['port'], $controllerData['serial_number'], $record['door_number'], $wait);

            $message = "check success, send open door";
        } else {
            $message = "unfond qrcode data OR user is left office OR qrcode has expired";
        }
        return $message;
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

    public function setDoorPasswordFeedback($ip = '', $port = '', $data = '') {
        \Think\Log::record("收到设置门禁密码反馈：ip: $ip data: $data");

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
            $UdpOperationModel = M('UdpOperation');
            $map['serial_number'] = $addr;
            $map['command_key'] = 'setDoorPassword';
            $map['create_time'] = array('GT', time()-2);
            $operation = $UdpOperationModel->where($map)->find();
            if ($operation) {
                $operation['result'] = $data;
                $operation['result_key'] = 'success';
                $operation['feedback_time'] = time();
                $UdpOperationModel->save($operation);
            }
        } else {
            $error = "set password time unupdate  ---- CRC ERROIR";
        }
        if ($error) {
            echo $error;
            \Think\Log::record($error);
        } else {
            echo "open record time update ---- CRC right";
            \Think\Log::record("open record time update ---- CRC right");
        }
    }

    public function setUserCardFeedback($ip = '', $port = '', $data = '') {
        \Think\Log::record("收到设置用户卡片开门反馈：ip: $ip data: $data");

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
            $UdpOperationModel = M('UdpOperation');
            $map['serial_number'] = $addr;
            $map['command_key'] = 'setUserCard';
            $map['create_time'] = array('GT', time()-5);
            $operation = $UdpOperationModel->where($map)->find();
            if ($operation) {
                $operation['result'] = $data;
                $operation['result_key'] = 'success';
                $operation['feedback_time'] = time();
                $UdpOperationModel->save($operation);

                $operationCommand = $operation["command"];
                $commandItems = explode("|", $operationCommand);
                $userCardModel = M("DoorControllerUserCard");
                foreach ($commandItems as $commandItem) {
                    $commandInfos = explode(",", $commandItem);
                    $controllerId = $commandInfos[0];
                    $userId = $commandInfos[1];
                    $cardNumber = $commandInfos[2];

                    $whereMap = array("controller_id"=>$controllerId,
                        "user_id"=>$userId,
                        "card_number"=>$cardNumber,
                        );
                    $cardItem = $userCardModel->where($whereMap)->find();
                    if ($cardItem) {
                        if (strlen($cardItem["doors"]) === 0) {
                            delCardItem($cardItem, $userCardModel);
                        } else {
                            $cardItem["status"] = 1;
                            saveCardItem($cardItem, $userCardModel);
                        }
                    }
                }
            }
        } else {
            $error = "set user card unupdate  ---- CRC ERROIR";
        }
        if ($error) {
            echo $error;
            \Think\Log::record($error);
        } else {
            echo "set user card time update ---- CRC right";
            \Think\Log::record("set user card time update ---- CRC right");
        }
    }

    private function handleHeartbeatEvent($controller) {
        $controllerId = $controller["id"];
        $ip = $controller["ip"];
        $port = $controller["port"];
        $serialNumber = $controller["serial_number"];
        $doorCount = $controller["door_count"];

        $now = time();
        $whereMap = array("controller_id"=>$controller["id"],
            "status"=>0,
            "last_sync_time"=> array("LT", $now-60),
            );
        $model = M("DoorControllerUserCard");
        $userCards = $model->where($whereMap)->limit(10)->select();
        if ($userCards) {
            $commandData = array();
            foreach ($userCards as $cardItem) {
                $cardItem["last_sync_time"] = $now;
                saveCardItem($cardItem, $model);
                $commandData[] = sprintf("%s,%s,%s", $cardItem["controller_id"], $cardItem["user_id"], $cardItem["card_number"]);
            }

            $UdpOperationModel = M('UdpOperation');
            $udpOperation['serial_number'] = $serialNumber;
            $udpOperation['command'] = implode("|", $commandData);
            $udpOperation['command_key'] = 'setUserCard';
            $udpOperation['create_time'] = $now;
            $addid = $UdpOperationModel->add($udpOperation);

            $handle = stream_socket_client("udp://127.0.0.1:9998", $errno, $errstr);
            if (!$handle) {
                die("ERROR: {$errno} - {$errstr}\n");
            }
            $sendMsg = "30030003"; // 下发名单
            $ips = explode(".", $ip);
            foreach ($ips as $i) {
                $sendMsg .= sprintf("%02x", $i);
            }
            $sendMsg .= sprintf("%04x", $port);
            $sendMsg .= "01";
            $sendMsg .= $serialNumber;
            $sendMsg .= sprintf("%02x", count($userCards));// 名单数量
            $sendMsg .= sprintf("%02x", 32);// 名单长度
            foreach ($userCards as $cardItem) {
                $sendMsg .= "0DFFFF"; // 名单内部标识，白名单
                $sendMsg .= sprintf("%08x", floatval($cardItem["card_number"]));// 卡号
                $sendMsg .= "000000";// 密码
                $sendMsg .= sprintf("%02x", $this->convertDoors($doorCount, $cardItem["doors"]));// doors 1个字节
                $sendMsg .= "00"; // 普通权限卡组别0
                $sendMsg .= "0000000000000000"; // 8字节 8个读头日期段
                $sendMsg .= "00"; // 节假日有效
                $sendMsg .= sprintf("%02x", date("Y", $now) - 2000 + 50); // 失效年份
                $sendMsg .= "01"; // 失效月份
                $sendMsg .= "01"; // 失效日
                $sendMsg .= "01"; // 失效小时
                $sendMsg .= "01"; // 失效分钟
                $sendMsg .= "FFFFFFFFFFFF"; // 剩余补足 6个字节 全F
            }
            $sendMsg = hex2bin($sendMsg);
            fwrite($handle, $sendMsg);
            fclose($handle);
        }
    }

    private function convertDoors($doorCount, $doors) {
        $doorArray = explode(",", $doors);
        if ($doorCount < 3) {// 注：1门 2门控制器是双向的，需要把门的位置射到读头上，一个门对应两个读头
            $readHeaderArray = array();
            foreach ($doorArray as $doorIndex) {
                $readHeaderIndex = $doorIndex * 2;
                $readHeaderArray[] = $readHeaderIndex;
                $readHeaderArray[] = $readHeaderIndex + 1;
            }
            $doorArray = $readHeaderArray;
        }
        $result = "";
        for ($i=0; $i < 8; $i++) {
            if (in_array("$i", $doorArray)) {
                $result .= "1";
            } else {
                $result .= "0";
            }
        }
        $result = strrev($result);
        return bindec($result);
    }

    /**
     * 正常刷卡事件
     * @param $serial_number
     * @param $data
     */
    public function swingCord2($serial_number, $data) {
        \Think\Log::record("收到刷卡请求2：serial number: $serial_number data: $data");
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
            $door_id = intval($record['door_number']);

            $message = $this->swingUserCard($record, $controllerData, $door_id);

        } else {
            $message = "unfond door contooler serial_number";
        }
        echo $message;
        \Think\Log::record("刷卡请求 处理结果：$message");
    }

    /**
     * 输密码开门
     * @param $serial_number
     * @param $data
     */
    public function swingCord3($serial_number, $data) {
        \Think\Log::record("收到密码开门请求：serial number: $serial_number data: $data");
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
            $door_id = intval($record['door_number']);

            $message = $this->swingDoorPwd($record, $controllerData, $door_id);
        } else {
            $message = "unfond door contooler serial_number";
        }
        echo $message;
        \Think\Log::record("刷卡请求 处理结果：$message");
    }

    /**
     * 0xA2：非法入侵事件
     * @param $serial_number
     * @param $data
     */
    public function invasionWarning($serial_number, $data) {
        \Think\Log::record("收到非法入侵事件：serial number: $serial_number data: $data");
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
            $door_id = intval($record['door_number']);

            $message = $this->doorWarningHandle($record, $controllerData, $door_id);
        } else {
            $message = "unfond door contooler serial_number";
        }
        echo $message;
        \Think\Log::record("非法入侵事件 处理结果：$message");
    }

    /**
     * 0xA5: 电锁被撬事件
     * @param $serial_number
     * @param $data
     */
    public function brokenWarning($serial_number, $data) {
        \Think\Log::record("收到电锁被撬事件：serial number: $serial_number data: $data");
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
            $door_id = intval($record['door_number']);

            $message = $this->doorWarningHandle($record, $controllerData, $door_id);
        } else {
            $message = "unfond door contooler serial_number";
        }
        echo $message;
        \Think\Log::record("电锁被撬事件 处理结果：$message");
    }

    /**
     * 读门当前的开关状态
     * @param $ip
     * @param $port
     * @param $data
     */
    public function queryDoorStatusFeedback($ip = '', $port = '', $data = '') {
        \Think\Log::record("读门当前的开关状态反馈：ip: $ip data: $data");

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
            $doorStatusStr = substr($appdata, 2, 2);
            $doorStatusStr = sprintf("%08b", hexdec($doorStatusStr));
            $dootStatus = strrev($doorStatusStr);

            $UdpOperationModel = M('UdpOperation');
            $map['serial_number'] = $addr;
            $map['command_key'] = 'queryDoorStatus';
            $map['create_time'] = array('GT', time()-2);
            $UdpOperationModel->where($map)->save(array('result'=>$data, 'result_key'=>$dootStatus, 'feedback_time'=>time()));
            /*$operation = $UdpOperationModel->where($map)->find();
            if ($operation) {
                $operation['result'] = $data;
                $operation['result_key'] = $dootStatus;
                $operation['feedback_time'] = time();
                $UdpOperationModel->save($operation);
            }*/
        } else {
            $error = "query door status unupdate  ---- CRC ERROIR";
        }
        if ($error) {
            echo $error;
            \Think\Log::record($error);
        } else {
            echo "query door status update ---- CRC right";
            \Think\Log::record("query door status update ---- CRC right");
        }
    }

    private function doorWarningHandle($record, $controllerData, $door_id) {// 处理警告
        $record['year'] = hexdec($record['year']);
        $record['month'] = hexdec($record['month']);
        $record['day'] = hexdec($record['day']);
        $record['hour'] = hexdec($record['hour']);
        $record['minute'] = hexdec($record['minute']);
        $record['second'] = hexdec($record['second']);

        $controller_id = $controllerData['id'];
        $company_id = $controllerData['company_id'];
        $notifiDate = strtotime($record['year']."-".$record['month']."-".$record['day']." ".$record['hour'].":".$record['minute'].":".$record['second']);
        $warningTime = date("Y年m月d日 H时i分s秒", $notifiDate);

        $doorName = M("Door")->where(array('controller_id'=>$controller_id, 'door_index'=>$door_id))->getField('name');
        if (!$doorName) {
            $doorName = $door_id."号门";
        }
        if ($record['event_name'] == "a2") {
            $pushContent = $controllerData['name']." ".$doorName." ".$warningTime."收到非法入侵事件！";
        } else if ($record['event_name'] == "a5") {
            $pushContent = $controllerData['name']." ".$doorName." ".$warningTime."收到电锁被撬事件！";
        }

        $MUser = M('User');
        $map = array();
        $map['status'] = 1;
        $map['company_id'] = $company_id;
        $map["jpush_register_id"] = array('neq','');
        $map['role_id'] = array("LT", 22);
        $userData = $MUser->join("auth_role_user on auth_role_user.user_id = user.id")->where($map)->select();
        $message = "通知以下用户:";
        foreach ($userData as $user) {
            $dataMap = array(
                "user_id" => $user['id'],
                "push_tag" => "door_warngin",
                "push_time" => $notifiDate,
            );
            $pushCount = M('JpushRecord')->where($dataMap)->count();

            if (!$pushCount) {
                $dataMap["push_content"] = $pushContent;
                $addResult = M('JpushRecord')->add($dataMap);
                if ($addResult) {
                    $message .= $user["id"]."(".$user["nickname"]."),";
                    jpushToUser($user["jpush_register_id"], $dataMap["push_content"]);
                }
            }
        }
        return $message;
    }

    private function swingUserCard($record, $controllerData, $door_id) {// 记录刷卡数据上报
        $controller_id = $controllerData['id'];
        $company_id = $controllerData['company_id'];

        $MUser = M('User');
        $map = array();
        $map['card_number'] = hexdec($record['carad_number']);
        $map['company_id'] = $company_id;
        $userData = $MUser->where($map)->find();
        if ($userData) {
            $openTime = sprintf("%d-%d-%d %d:%d:%d", hexdec($record['year']), hexdec($record['month']), hexdec($record['day']), hexdec($record['hour']), hexdec($record['minute']), hexdec($record['second']));
            $now = time();
            $user_id = $userData['id'];
            $openRecord['company_id'] = $company_id;
            $openRecord['controller_id'] = $controller_id;
            $openRecord['door_id'] = $door_id;
            $openRecord['open_time'] = strtotime($openTime);
            $openRecord['feedback_time'] = $now;
            $openRecord['user_id'] = $user_id;
            $openRecord['way'] = 5;
            $openRecord['mark'] = $record['carad_number'];
            M('OpenRecord')->add($openRecord);

            $message = "record swing user card success";
        } else {
            $message = "unfond card number";
        }
        return $message;
    }


    private function swingDoorPwd($record, $controllerData, $door_id) {// 记录输密码数据上报
        $controller_id = $controllerData['id'];
        $company_id = $controllerData['company_id'];
        $openTime = sprintf("%d-%d-%d %d:%d:%d", hexdec($record['year']), hexdec($record['month']), hexdec($record['day']), hexdec($record['hour']), hexdec($record['minute']), hexdec($record['second']));

        $openRecord['company_id'] = $company_id;
        $openRecord['controller_id'] = $controller_id;
        $openRecord['door_id'] = $door_id;
        $openRecord['open_time'] = strtotime($openTime);
        $openRecord['feedback_time'] = time();
        $openRecord['user_id'] = -999;
        $openRecord['way'] = 6;
        M('OpenRecord')->add($openRecord);

        return "record open door by door password success";
    }
}