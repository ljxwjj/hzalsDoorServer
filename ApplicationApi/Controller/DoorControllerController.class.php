<?php
namespace Api\Controller;

class DoorControllerController extends CommonRestController {

    public function _filter(&$map) {
        if (session(C('ADMIN_AUTH_KEY'))) {

        } else {
            $map['company_id'] = session("user")["company_id"];
        }
    }

    // 公司门禁清单
    public function lists() {
        parent::lists("DoorControllerView");
    }

    public function detail(){
        parent::detail();
    }

    // 添加控制器
    public function add() {
        parent::add();
    }

    // 删除控制器
    public function del() {
        parent::del();
    }

    // 开门
    public function openDoor() {
        $user_id = I('user_id');
        $controller_id = I('controller_id');
        $door_id = I('door_id');
        $userDoors = getUserDoors($user_id);
        if (!$userDoors[$controller_id][$door_id]) {
            $result = $this->createResult(0, "授权失败");
            $this->response($result,'json');
        } else {

            $data = M('DoorController')->find($controller_id);
            if ($data) {
                $openRecord['controller_id'] = $controller_id;
                $openRecord['door_id'] = $door_id;
                $openRecord['open_time'] = time();
                $openRecord['user_id'] = session(C('USER_AUTH_KEY'));
                $openRecord['way'] = 1;
                M('OpenRecord')->add($openRecord);

                $wait = intval($data['wait_time']);
                $this->sendOpenDoorUdpCode($data['ip'], $data['port'], $data['serial_number'], $door_id, $wait);
                $result = $this->createResult(200, "开门成功");
                $this->response($result,'json');
            } else {
                $result = $this->createResult(0, "开门失败");
                $this->response($result,'json');
            }
        }
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