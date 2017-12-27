<?php
namespace Api\Controller;

class PublicController extends CommonRestController {

    public function login(){
        \Think\Log::record('public login....');
        $account = I('account');
        $password = I('password');

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
            $token = createToeken();

            $companyData = M('Company')->find($user['company_id']);
            $company = $companyData?$companyData['name']:'';

            if ($head_image) {
                $head_image = getHttpRooDir().'/Public'.$head_image;
            }
            $role_id = M('AuthRoleUser')->where("user_id=$id")->getField('role_id');
            $data = compact('id', 'token', 'account', 'email', 'mobile', 'nickname', 'sex', 'company', 'head_image', 'role_id');
            $result = $this->createResult(200, '登录成功', $data);

            $model = M('UserToken');
            $userToken = $model->find($user['id']);
            if ($userToken) {
                $userToken['token'] = $token;
                $userToken['update_time'] = time();
                $model->save($userToken);
            } else {
                $userToken = array('user_id'=>$user['id'], 'token'=>$token, 'update_time'=>time());
                $model->add($userToken);
            }
        } else {
            $result = $this->createResult(0, '登录失败');
        }
        $this->response($result,'json');
    }

    public function register() {
        $account = I('account');
        $password = I('password', '', 'md5');
        $smsCode = I('sms_code');

        $User = M('User');
        $map['account'] = $account;
        $map['status'] = 0;
        $user = $User->where($map)->find();

        if (!$user) {
            $result = $this->createResult(1, '该号码不存在或已注册');
        } else {
            // 核实验证码
            $MSmsCode = M('SmsCode');
            $map = array();
            $map["mobile"] = $account;
            $map["use_to"] = 'register';
            $map["check_time"] = array("EQ", 0);
            $sms = $MSmsCode->where($map)->order('send_time desc')->find();
            if ($sms && ($sms["send_time"] + $sms["delay"]) > time() && $sms["code"] == $smsCode) {
                $sms['check_time'] = time();
                $MSmsCode->save($sms);
                $user['password'] = $password;
                $user['status'] = 1;
                $saveFlag = $User->save($user);

                if ($saveFlag) {
                    $result = $this->createResult(200, '注册成功');
                } else {
                    $result = $this->createResult(0, '注册失败');
                }
            } else {
                $result = $this->createResult(4, '验证码错误');
            }
        }

        $this->response($result,'json');
    }

    public function findPassword() {
        $account = I('account');
        $password = I('password', '', 'md5');
        $smsCode = I('sms_code');

        $User = M('User');
        $map['account'] = $account;
        $user = $User->where($map)->find();

        if (!$user) {
            $result = $this->createResult(1, '非系统用户');
        } else if (empty($user['password'])) {
            $result = $this->createResult(2, '帐号未激活');
        } else if ($user['status'] === -1) {
            $result = $this->createResult(3, '用户被禁用');
        } else {
            // 核实验证码
            $MSmsCode = M('SmsCode');
            $map = array();
            $map["mobile"] = $account;
            $map["use_to"] = 'register';
            $map["check_time"] = array("EXP", "is null");
            $sms = $MSmsCode->where($map)->order('send_time desc')->find();
            if ($sms && ($sms["send_time"] + $sms["delay"]) > time() && $sms["code"] === $smsCode) {
                $MSmsCode->setField("check_time", time());
                $data['password'] = $password;
                $saveFlag = $User->save($data);

                if ($saveFlag) {
                    $result = $this->createResult(200, '找回成功');
                } else {
                    $result = $this->createResult(0, '找回失败');
                }
            } else {
                $result = $this->createResult(4, '验证码错误');
            }
        }

        $this->response($result,'json');
    }

    public function sendSMS() {
        $mobile = I('mobile');
        $operation = I('operation');

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

        $smsCode = generate_code(6);
        $sendResult = doSendSms($mobile, $smsCode, $operation);
        if ($sendResult->Code !== 'OK') {
            $result = $this->createResult(0, '发送失败:'.$sendResult->Message);
            $this->response($result,'json');
            return;
        }
        $data = array(
            'mobile'=> $mobile,
            'code' => $smsCode,
            'send_time' => time(),
            'use_to' => $operation,
            'delay'  => 15*60,
            'sms_request_id' => $sendResult->RequestId,
            'sms_biz_id' => $sendResult->BizId,
        );
        $smsCodeId = M('SmsCode')->data($data)->add();

        if ($smsCodeId) {
            $result = $this->createResult(200, '发送成功');
        } else {
            $result = $this->createResult(0, '发送失败');
        }

        $this->response($result,'json');
    }

    public function qrcode() {
        $text = I('text');
        Vendor('phpqrcode/phpqrcode', COMMON_PATH . 'Vendor/', '.php');
        \QRcode::png($text);
    }
}