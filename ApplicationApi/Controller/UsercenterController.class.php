<?php
namespace Api\Controller;
use Think\Controller\RestController;
class UsercenterController extends RestController {
    protected $allowMethod    = array('get','post','put'); // REST允许的请求类型列表
    protected $allowType      = array('html','xml','json'); // REST允许请求的资源类型列表

    public function login($mobile = '', $password = ''){
        $result = array();
        if ($password) $password = md5($password);
        $User = M('User');  // D('User');
        $map['mobile'] = $mobile;
        $user = $User->where($map)->find();
        if ($user && $user['password'] == $password) {
            $result['code'] = 200;
            $result['message'] = '登录成功';
            $result['data'] = $user;
        } else {
            $result['code'] = 0;
            $result['message'] = '登录失败';
            $result['data'] = (object)array();
        }
        $this->response($result,'json');
    }

    public function register() {
        $result = array();
        $mobile = I('mobile');
        $password = I('mobile', '', 'md5');
        $smsCode = I('sms_code');

        $User = M('User');
        $map['mobile'] = $mobile;
        $user = $User->where($map)->find();
        if ($user) {
            $result['code'] = 0;
            $result['message'] = '该手机号已注册';
            $result['data'] = (object)array();
        } else {
            $smsCode = M('SmsCode');
            $map["mobile"] = $mobile;
            $sms = $smsCode->where($map)->find();
            if ($sms && ($sms["send_time"] + $sms["delay"] * 1000) > time() && $sms["code"] === $smsCode) {
                $data = $User->create($_POST, Model::MODEL_INSERT);
                $User->add($data);

                $result['code'] = 200;
                $result['message'] = '注册成功';
                $result['data'] = (object)array();
            } else {
                $result['code'] = 0;
                $result['message'] = '验证码错误';
                $result['data'] = (object)array();
            }
        }


        $this->response($result,'json');
    }

    public function sendSMS($mobile, $operation) {
        $result = array();
        $operations = array("register", "findPassword");
        if (in_array($operation, $operations)) {
            $result['code'] = 0;
            $result['message'] = '参数错误';
            $result['data'] = (object)array();
        } else {
            $result['code'] = 200;
            $result['message'] = '发送成功';
            $result['data'] = (object)array();
        }
        $this->response($result,'json');
    }

    public function modifyPassword() {

    }
}