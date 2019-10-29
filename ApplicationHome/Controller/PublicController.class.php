<?php

namespace Home\Controller;


use Lib\ORG\Util\RBAC;
use Michelf\Markdown;
use Lib\ORG\Util\Cookie;

class PublicController extends CommonController {


    public function login() {

        $vo['account'] = Cookie::get("account");
        $vo['password'] = Cookie::get("password");
        if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->assign("vo",$vo);
            $this->display();
        }else{
            $this->redirect('Index/index');
        }
    }

    public function checkLogin() {

        if(empty($_POST['account'])) {
            $error['account']='账号不能为空！';
        }
        if (empty($_POST['password'])){
            $error['password']='密码不能为空！';
        }

        //生成认证条件
        $map   =   array();
        // 支持使用绑定帐号登录
        $map['account']	= $_POST['account'];
        $map["status"]	=	array('EQ', 1);
//        $map["is_admin"]	=	array('eq',1);

        import('@.Lib.ORG.Util.RBAC');
        $authInfo = RBAC::authenticate($map);
        //使用用户名、密码和状态的方式进行认证
        if(!$error['account'] && !$authInfo) {
            $error['account']='账号不存在或已禁用！';
        }
        if(!$error['password'] && $authInfo['password'] != md5($_POST['password'])) {
            $error['password']='密码错误！';
        }


        if($error){
            $this->assign('error',$error);
            $this->assign('vo',$_POST);
            $this->display('login');
            exit;
        }else{
            session('[regenerate]');
            $_SESSION[C('USER_AUTH_KEY')]	=	$authInfo['id'];
            $_SESSION['email']	            =	$authInfo['email'];
            $_SESSION['loginUserName']		=	$authInfo['nickname'];
            $_SESSION['lastLoginTime']		=	$authInfo['last_login_time'];
            $_SESSION['login_count']	    =	$authInfo['login_count'];
            $_SESSION['account']	        =	$authInfo['account'];
            $_SESSION['company_id']         =   $authInfo['company_id'];
            $_SESSION['head_image']         =   $authInfo['head_image'];
            session('session_refresh_time', time());
            if($authInfo['is_admin']==1) {
                $_SESSION[C('ADMIN_AUTH_KEY')] = true;
            }

            //保存登录信息
            $Admin	=	M('User');
            $ip		=	get_client_ip();
            $time	=	time();
            $data = array();
            $data['id']	=	$authInfo['id'];
            $data['last_login_time']	=	$time;
            $data['login_count']	=	array('exp','login_count+1');
            $data['last_login_ip']	=	$ip;
            $Admin->save($data);

            if($_POST['remember_password'] == 1){
                Cookie::set('account',$authInfo['account'],86400);
                Cookie::set('password',$_POST['password'],86400);
            }

            // 缓存访问权限
            RBAC::saveAccessList();
            $this->redirect('Index/index');
            //$this->success('登录成功！',__APP__.'/Index/index');
        }
    }

    public function logout() {
        if(session(C('USER_AUTH_KEY'))) {
            session(null);
            session('[destroy]');
            $this->success('你已经安全退出系统！',U('Public/login'));
        }else {
            $this->error('已经退出！', U('Public/login'));
        }
    }

    public function register() {
        $this->display();
    }

    public function checkRegister() {
        $account = I('account');
        $password = I('password', '', 'md5');
        $smsCode = I('sms_code');

        $User = M('User');
        $map['account'] = $account;
        $map['status'] = 0;
        $user = $User->where($map)->find();

        if (!$user) {
            $error['account'] = '非系统用户';
        } else if (!empty($user['password'])) {
            $error['account'] = '该手机号已注册';
        } else if ($user['status'] === -1) {
            $error['account'] = '用户被禁用';
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
                    $this->success('注册成功！',U('Public/login'));
                    return true;
                } else {
                    $this->error('注册失败！', U('Public/register'));
                    return false;
                }
            } else {
                $error['sms_code'] = '验证码错误';
            }
        }
        if ($error) {
            $this->assign('error', $error);
            $this->assign('vo', $_REQUEST);
            $this->display('register');
        }

    }

    public function sendRegisterSMS() {
        $mobile = I('account');

        $user = M('User')->where(array('account'=>$mobile, 'status'=>0))->find();
        if (!$user) {
            $result['code'] = 0;
            $result['message'] = "用户已注册或非系统用户";
            $this->response($result);
            exit;
        }
        $MSmsCode = M('SmsCode');
        $map = array();
        $map["mobile"] = $mobile;
        $map["use_to"] = 'register';
        $map["check_time"] = array("EQ", 0);
        $sms = $MSmsCode->where($map)->order('send_time desc')->find();
        if ($sms && ($sms["send_time"] + 120) > time()) {
            $result['code'] = 0;
            $result['message'] = "信息发送太频繁!";
            $this->response($result);
            exit;
        }

        $smsCode = generate_code(6);
        $sendResult = doSendSms($mobile, $smsCode, "register");
        if ($sendResult->Code !== 'OK') {
            $result['code'] = 0;
            $result['message'] = '发送失败:'.$sendResult->Message;
            $this->response($result);
            return;
        }
        $data = array(
            'mobile'=> $mobile,
            'code' => $smsCode,
            'send_time' => time(),
            'use_to' => "register",
            'delay'  => 15*60,
            'sms_request_id' => $sendResult->RequestId,
            'sms_biz_id' => $sendResult->BizId,
        );
        $smsCodeId = $MSmsCode->data($data)->add();

        if ($smsCodeId) {
            $result['code'] = 200;
            $result['message'] = '发送成功';
        } else {
            $result['code'] = 0;
            $result['message'] = '发送失败';
        }
        $this->response($result);
    }

    public function findPassword() {
        $this->display();
    }

    public function sendFindPasswordSMS() {
        $mobile = I('account');

        $user = M('User')->where(array('account'=>$mobile, 'status'=>1))->find();
        if (!$user) {
            $result['code'] = 0;
            $result['message'] = "用户未注册或非系统用户";
            $this->response($result);
            exit;
        }
        $MSmsCode = M('SmsCode');
        $map = array();
        $map["mobile"] = $mobile;
        $map["use_to"] = 'findPassword';
        $map["check_time"] = array("EQ", 0);
        $sms = $MSmsCode->where($map)->order('send_time desc')->find();
        if ($sms && ($sms["send_time"] + 120) > time()) {
            $result['code'] = 0;
            $result['message'] = "信息发送太频繁!";
            $this->response($result);
            exit;
        }

        $smsCode = generate_code(6);
        $sendResult = doSendSms($mobile, $smsCode, "findPassword");
        if ($sendResult->Code !== 'OK') {
            $result['code'] = 0;
            $result['message'] = '发送失败:'.$sendResult->Message;
            $this->response($result);
            return;
        }
        $data = array(
            'mobile'=> $mobile,
            'code' => $smsCode,
            'send_time' => time(),
            'use_to' => "findPassword",
            'delay'  => 15*60,
            'sms_request_id' => $sendResult->RequestId,
            'sms_biz_id' => $sendResult->BizId,
        );
        $smsCodeId = $MSmsCode->data($data)->add();

        if ($smsCodeId) {
            $result['code'] = 200;
            $result['message'] = '发送成功';
        } else {
            $result['code'] = 0;
            $result['message'] = '发送失败';
        }
        $this->response($result);
    }

    public function checkFindPassword() {
        $account = I('account');
        $password = I('password', '', 'md5');
        $smsCode = I('sms_code');

        $User = M('User');
        $map['account'] = $account;
        $map['status'] = 1;
        $user = $User->where($map)->find();

        if (!$user) {
            $error['account'] = '非系统用户';
        } else if (empty($user['password'])) {
            $error['account'] = '该手机号未注册';
        } else if ($user['status'] === -1) {
            $error['account'] = '用户被禁用';
        } else {
            // 核实验证码
            $MSmsCode = M('SmsCode');
            $map = array();
            $map["mobile"] = $account;
            $map["use_to"] = 'findPassword';
            $map["check_time"] = array("EQ", 0);
            $sms = $MSmsCode->where($map)->order('send_time desc')->find();
            if ($sms && ($sms["send_time"] + $sms["delay"]) > time() && $sms["code"] == $smsCode) {
                $sms['check_time'] = time();
                $MSmsCode->save($sms);
                $user['password'] = $password;
                $user['status'] = 1;
                $saveFlag = $User->save($user);

                if ($saveFlag) {
                    $this->success('密码修改成功！',U('Public/login'));
                    return true;
                } else {
                    $this->error('密码修改失败！', U('Public/findPassword'));
                    return false;
                }
            } else {
                $error['sms_code'] = '验证码错误';
            }
        }
        if ($error) {
            $this->assign('error', $error);
            $this->assign('vo', $_REQUEST);
            $this->display('findPassword');
        }
    }

    public function alsTest($url = null, $user_id = 0) {//echo C("public_dir");exit;
        if ($url) {
            $params = $this->getKeyValue($url);  //var_dump($params);exit;
            $wenhao = true;

            if(empty($params)) {
                $info   =   parse_url($url);
                $path = $info['path'];
                $paramStr = str_replace('/door/api.php/', '', $path);
                $params = explode('/', $paramStr);
                $paramsArray = array();
                $count = count($params)/2;
                for ($i=1; $i < $count; $i++) {
                    $paramsArray[$params[$i*2]] = $params[$i*2+1];
                }
                $params = $paramsArray;      //var_dump($params);exit;
                $wenhao = false;
            }

            if ($user_id) {
                $token = D('UserToken')->where(array('user_id'=>$user_id))->getField('token');
                $account = M('User')->where(array('id'=>$user_id))->getField('account');
                $params['account'] = $account;
                $params['user_id'] = $user_id;
                $params['token'] = $token;
            }

            ksort($params);
            $paramsStr = implode("", $params);
            $paramsStr = utf8_strrev($paramsStr);
            $paramsStr = $paramsStr . '8djUK*014kJ';
            $paramsMd5 = md5($paramsStr);
            if ($wenhao) {
                $signurl = $url . "&sign=$paramsMd5";
                if ($user_id) $signurl .= "&account=$account&user_id=$user_id&token=$token";
            } else {
                $signurl = $url . ('/sign/' . $paramsMd5);
                if ($user_id) $signurl .= "/account/$account/user_id/$user_id/token/$token";
            }

        }

        Vendor('Michelf/Markdown', MODULE_PATH . 'Vendor/', '.inc.php');
        $obj = new Markdown();
        $my_text = file_get_contents(APP_PATH.'/API.md');
        $api_doc_html = Markdown::defaultTransform($my_text);
        $this->assign('api_doc_html',$api_doc_html);

        if ($url) {
            $this->assign('url',$url);
            $this->assign('signurl',$signurl);
            $this->assign('user_id', $user_id);
        }
        $this->display();
    }

    public function pushTest() {
        $userName = I("userName");
        if ($userName) {
            $rid = M('User')->where(array('account'=>$userName, 'status'=>1))->getField('jpush_register_id');

            if (I("type1")) jpushToUser($rid, "马上就要上班了，记得打卡哦！");
            if (I("type2")) jpushToUser($rid, "阿里桑手机门禁 2018年07月31日 19时02分44秒与服务器失去连接，请及时检查设备状态！");
            if (I("type3")) jpushToUser($rid, "Ver5.1新版预告", "als://webpage/10");
            if (I("type4")) jpushToUser($rid, "上个月的考勤报表已统计完毕，请注意查收！", "als://attendance");
            if (I("type5")) jpushToUser($rid, "总机房5号控制器 2F012 2018年08月24日 14时52分48秒收到非法入侵事件！");

            $this->assign("message", "推送成功");
        }
        $this->display();
    }

    public function testJpush() {

        Vendor('Requests', COMMON_PATH . 'Vendor/Requests-1.7.0/library/', '.php');
        \Requests::register_autoloader();


        $url = "http://api.hzals.com/door/index.php/UfaceManager/faceSpotCallback";
//        $url = "http://192.168.0.12/door/api.php/UfaceManager/faceSpotCallback";
        $headers = array("Content- Type", "application/x-www-form-urlencoded");
        $data = "photoUrl=http%3A%2F%2Funiubi-device.oss-cn-hangzhou.aliyuncs.com%2Fdevice%2Fspot%2Fphoto%2F84E0F421716406B2%2F2019-08-23%2FE0D8049966434870989FC2E0D9420BA2_20190823095052_rgb.jpg&personGuid=E0D8049966434870989FC2E0D9420BA2&data=%7B%22yAxis%22%3A%22362%22%2C%22xAxis%22%3A%22387%22%2C%22sex%22%3A%22null%22%2C%22width%22%3A%2294%22%2C%22name%22%3A%22%E5%B4%94%E6%AC%A2%E8%89%B3%22%2C%22age%22%3A%22null%22%2C%22height%22%3A%2295%22%7D&recMode=1&showTime=2019-09-26+11%3a32%3a52&recVideoUrl&appId=55FD1CB09EFD4183AA01388D35667D19&deviceKey=84E0F421321802FA&guid=8610787477841453055&idCardInfo&type=0&userGuid=7AF875C57A9A4E74AE2CD46B8ADD2766";
        $response = \Requests::post($url, $headers, $data);
        echo($response->body);
        exit;

        //jpush();
    }

    public function shareQrcodeToFriend() {
        $this->display();
    }

    public function serialNumberEncoded() {

        $serial_numbers = I("serial_numbers");
        if ($serial_numbers) {
            $serialArray = explode("\r\n", $serial_numbers);
            foreach ($serialArray as $serialNumber) {
                $resultArray[] = serialNumberToEncoded($serialNumber, 6);
            }
            $this->assign('serial_numbers', $serial_numbers);
            $this->assign('serial_number_encode_result',implode("\r\n", $resultArray));
        }
        $this->display();
    }

    private function getKeyValue($url) {
        $result = array();
        $mr = preg_match_all('/(\?|&)(.+?)=([^&?]*)/i', $url, $matchs);
        if ($mr !== FALSE) {
            for ($i = 0; $i < $mr; $i++) {
                $result[$matchs[2][$i]] = $matchs[3][$i];
            }
        }

        return $result;
    }

    public function messageCount() {
        $result = array();
        if (session(C('ADMIN_AUTH_KEY'))) {
            $result['code'] = 200;
            $result['request_use_messagecount'] = session('request_use_messagecount');
            $result['repair_record_messagecount'] = session('repair_record_messagecount');
        } else {
            $result['code'] = 0;
            $result['message'] = "没有该数据的操作权限！";
        }
        $this->response($result);
    }
}