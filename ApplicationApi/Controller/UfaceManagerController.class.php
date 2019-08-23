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

    public function _initialize() {

    }

    public function faceSpotCallback() {
        $postBody = json_encode($_POST);
        _log($postBody);

        $personGuid = $_POST['personGuid'];
        $deviceKey = $_POST['deviceKey'];
        $showTime = strtotime($_POST['showTime']);

        $user_id = D("UfaceUser")->where(array("uface_guid"=>$personGuid))->getField("user_id");
        $ufaceDevice = D("UfaceDevice")->where(array("device_key"=>$deviceKey))->find();
        $controller_id = $ufaceDevice['controller_id'];
        $door_id = $ufaceDevice['door_id'];
        if ($user_id && $ufaceDevice) {
            $openRecord = array();
            $openRecord['company_id'] = $ufaceDevice['company_id'];
            $openRecord['controller_id'] = $controller_id;
            $openRecord['door_id'] = $door_id;
            $openRecord['open_time'] = time();
            $openRecord['user_id'] = $user_id;
            $openRecord['uface_device_key'] = $deviceKey;
            $openRecord['uface_device_name'] = $ufaceDevice['name'];
            $openRecord['way'] = 8;
            $openRecord['mark'] = $postBody;
            $OpenRecord = M('OpenRecord');
            $OpenRecord->create($openRecord);
            $addid = $OpenRecord->add();

            if ($controller_id) {
                $data = M('DoorController')->find($controller_id);
                $currentTime = time();
                if ($showTime > $currentTime - 60 && $showTime < $currentTime + 60) {
                    if ($data['product_type'] == 1) {
                        $rv = sendOpenDoorHttp($data['ip'], $data['port'], $door_id, $data['password']);
                        if ($rv) {
                            $OpenRecord->where("id=$addid")->setField("feedback_time",time());
                            $result = $this->createResult(201, "开门成功");
                        } else {
                            $result = $this->createResult(0, "开门失败");
                        }
                    } else {
                        $wait = intval($data['wait_time']);
                        sendOpenDoorUdpCode($data['ip'], $data['port'], $data['serial_number'], $door_id, $wait);
                        $result = $this->createResult(200, "开门成功", array("id"=>$addid));
                    }
                } else {
                    $result = $this->createResult(0, "刷脸时间不匹配");
                }
            } else { // 人脸识别未绑定阿里山门禁，认为刷脸直开
                $OpenRecord->where("id=$addid")->setField("feedback_time",time());
                $result = $this->createResult(0, "开门成功");
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
//    $logfile = APP_PATH .DIRECTORY_SEPARATOR. "uface_callback.log";
//    file_put_contents($logfile, "--------------\n".$msg."\n", FILE_APPEND);
}