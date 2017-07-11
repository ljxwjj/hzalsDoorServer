<?php
namespace Api\Controller;

class PublicController extends CommonRestController {

    public function login($account = '', $password = ''){
        $result = array();
        $User = M('User');  // D('User');
        $map['account'] = $account;
        $map['status'] = array('EQ', 1);
        $user = $User->where($map)->find();
        if ($user && $user['password'] == md5($password)) {
            // 更新最后登录时间
            $user['last_login_time'] = time();
            $user['last_login_ip'] = get_client_ip();
            $User->save($user);

            extract($user);   // 把数组拆分成变量
            $token = createToekn();

            $companyData = M('Company')->where(array('id'=>$user['company_id']))->find();
            $company = $companyData?$companyData['name']:'';

            $result['code'] = 200;
            $result['message'] = '登录成功';
            $result['data'] = compact('id', 'token', 'account', 'email', 'mobile', 'nickname', 'sex', 'company');

            $model = M('UserToken');
            $userToken = $model->where(array('user_id'=>$user['id']))->find();
            if ($userToken) {
                $userToken['token'] = $token;
                $userToken['update_time'] = time();
                $model->save($userToken);
            } else {
                $userToken = array('user_id'=>$user['id'], 'token'=>$token, 'update_time'=>time());
                $model->add($userToken);
            }
        } else {
            $result['code'] = 0;
            $result['message'] = '登录失败';
            $result['data'] = (object)array();
        }
        $this->response($result,'json');
    }

    public function register() {
        $result = array();
        $account = I('account');
        $password = I('password', '', 'md5');
        $smsCode = I('sms_code');

        $User = M('User');
        $map['account'] = $account;
        $user = $User->where($map)->find();

        if (!$user) {
            $result = createResult(1, '非系统用户');
        } else if (!empty($user['password'])) {
            $result = createResult(2, '该手机号已注册');
        } else if ($user['status'] === -1) {
            $result = createResult(3, '用户被禁用');
        } else {
            // 核实验证码
            $smsCode = M('SmsCode');
            $map["mobile"] = $account;
            $sms = $smsCode->where($map)->find();
            if ($sms && ($sms["send_time"] + $sms["delay"] * 1000) > time() && $sms["code"] === $smsCode) {
                $data['password'] = $password;
                $data['status'] = 1;
                $saveFlag = $User->save($data);

                if ($saveFlag) {
                    $result = createResult(200, '注册成功');
                } else {
                    $result = createResult(0, '注册失败');
                }
            } else {
                $result = createResult(4, '验证码错误');
            }
        }

        $this->response($result,'json');
    }

    public function sendSMS($mobile, $operation) {
        $operations = array("register", "findPassword");
        if (!in_array($operation, $operations)) {
            $result = $this->createResult(0, '参数错误');
            $this->response($result,'json');
            return;
        }
        if ('findPassword' === $operation) {
            $userData = M('User')->where(array('account'=>$mobile))->find();
            if (!$userData) {
                $result = $this->createResult(0, '非系统用户');
                $this->response($result,'json');
                return;
            }
            if (empty($userData['password'])) {
                $result = $this->createResult(0, '未注册用户');
                $this->response($result,'json');
                return;
            }
        }

        $smsCode = generate_code(4);
        if (!doSendSms($mobile, $smsCode)) {
            $result = $this->createResult(0, '发送失败');
            $this->response($result,'json');
            return;
        }
        $data = array(
            'mobile'=> $mobile,
            'code' => $smsCode,
            'send_time' => time(),
            'use_to' => $operation,
        );
        $smsCodeId = M('SmsCode')->data($data)->add();

        if ($smsCodeId) {
            $result = $this->createResult(200, '发送成功');
        } else {
            $result = $this->createResult(0, '发送失败');
        }

        $this->response($result,'json');
    }

    public function modifyPassword() {

    }
}