<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/26
 * Time: 14:59
 */

namespace Api\Controller;
use Think\Controller\RestController;


/**
 * 专门为第三方提供接口用
 */

class SmartDoorController extends RestController {

    // 通过公司秘钥生成分享二维码
    public function qrcodeBySecret() {
        $serial_number = I('serial_number');
        $door_id = I("door_id", -1, "int");
        $secret = I('secret_key');
        $validity_time = I("validity_time", 0, "int"); // 秒
        if ($validity_time > 24 * 60 * 60) {
            $validity_time = 24 * 60 * 60;
        }

        $company_id = M('Company')->where(array('secret_key'=>$secret))->getField("id");
        if (!$company_id) {
            $result = $this->createResult(0, "用户密钥错误");
            $this->response($result,'json');
        }
        $user_id = D('User')->where(array('company_id'=>$company_id))->getField("id");

        $map = array(
            'serial_number'=>$serial_number,
            'status'=>0,
            'company_id' => $company_id,
        );
        $controller_id = M('DoorController')->where($map)->getField('id');
        if (!$controller_id) {
            $result = $this->createResult(0, "序列号不存在");
            $this->response($result,'json');
        }


        $token = createShareQR();
        $create_time = time();
        $expiry_time = $create_time + $validity_time;
        $model = D('UserShareQrcode');
        $data = compact('user_id', 'token', 'controller_id', 'door_id', 'create_time', 'expiry_time', 'validity_time');
        $result = $model->add($data);

        if ($result) {
            $text = hexdec($token);
            Vendor('phpqrcode/phpqrcode', COMMON_PATH . 'Vendor/', '.php');
            \QRcode::png($text);
        } else {
            $this->response($this->createResult(0, "系统错误"), "json");
        }

    }

    protected function createResult($code, $message, $data = array()) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        $result['data'] = $data;
        return $result;
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @param integer $code HTTP状态
     * @return void
     */
    protected function response($data,$type='',$code=200) {
        $this->sendHttpStatus($code);
        exit($this->encodeData($data,strtolower($type)));
    }
}