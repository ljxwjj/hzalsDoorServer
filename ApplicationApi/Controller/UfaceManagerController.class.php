<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/13
 * Time: 8:43
 */
namespace Api\Controller;

use Think\Controller\RestController;

class UfaceManagerController extends RestController {
    protected $allowMethod    = array('post'); // REST允许的请求类型列表
    protected $allowType      = array('html','xml','json'); // REST允许请求的资源类型列表

    public function faceSpotCallback() {
        $postBody = file_get_contents("php://input");
        _log($postBody);
        $json = json_decode($postBody);

        $personGuid = $json->personGuid;
        $deviceKey = $json->deviceKey;
        $showTime = $json->showTime;

        $user_id = D("UfaceUser")->where(array("uface_guid"=>$personGuid))->getField("user_id");
        $ufaceDevice = D("UfaceDevice")->where(array("device_key"=>$deviceKey))->find();
        $controller_id = $ufaceDevice['controller_id'];
        $door_id = $ufaceDevice['door_id'];
        if ($user_id && $ufaceDevice && $controller_id) {
            $data = M('DoorController')->find($controller_id);
            if ($data) {
                $openRecord['controller_id'] = $controller_id;
                $openRecord['door_id'] = $door_id;
                $openRecord['open_time'] = time();
                $openRecord['user_id'] = session(C('USER_AUTH_KEY'));
                $openRecord['way'] = 8;
                $openRecord['mark'] = $postBody;
                $OpenRecord = M('OpenRecord');
                $OpenRecord->create($openRecord);
                $addid = $OpenRecord->add();

//                $currentTime = time();
//                if ($showTime > $currentTime - 10000 && $showTime < $currentTime + 10000) {
                    $doorController = A("DoorController");
                    if ($data['product_type'] == 1) {
                        $rv = $doorController->sendOpenDoorHttp($data['ip'], $data['port'], $door_id, $data['password']);
                        if ($rv) {
                            $OpenRecord->where("id=$addid")->setField("feedback_time",time());
                            $result = $this->createResult(201, "开门成功");
                        } else {
                            $result = $this->createResult(0, "开门失败");
                        }
                    } else {
                        $wait = intval($data['wait_time']);
                        $doorController->sendOpenDoorUdpCode($data['ip'], $data['port'], $data['serial_number'], $door_id, $wait);
                        $result = $this->createResult(200, "开门成功", array("id"=>$addid));
                    }
//                } else {
//                    $result = $this->createResult(0, "刷脸时间不匹配");
//                }
            } else {
                $result = $this->createResult(0, "开门失败");
            }
        } else {
            $result = $this->createResult(0, "未找到对象！");
        }

        $this->response($result,'json');
    }

    public function faceAuthCallback() {
        $postBody = file_get_contents("php://input");
        $json = json_decode($postBody);
        $result = $this->createResult(200, "数据已接收");
        $this->response($result,'json');
    }

    protected function createResult($code, $message, $data = array()) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        $result['data'] = $data;
        return $result;
    }
}

function _log($msg) {
    $logfile = APP_PATH .DIRECTORY_SEPARATOR. "uface_callback.log";
    file_put_contents($logfile, "--------------\n".$msg."\n", FILE_APPEND);
}