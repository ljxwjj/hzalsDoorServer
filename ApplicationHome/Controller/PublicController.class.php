<?php

namespace Home\Controller;


use Lib\ORG\Util\RBAC;
use Michelf\Markdown;

class PublicController extends CommonController {


    public function login() {

        $vo['account'] = cookie("account");
        $vo['password'] = cookie("password");
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

        import('Lib.ORG.Util.RBAC');
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

    public function alsTest($url = null, $user_id = 0) {//echo APP_PATH;exit;
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
                $user = D('UserToken')->where(array('user_id'=>$user_id))->find();
                $token = $user['token'];
                $params['user_id'] = $user_id;
                $params['token'] = $user['token'];
            }

            ksort($params);
            $paramsStr = implode("", $params);
            $paramsStr = strrev($paramsStr);
            $paramsStr = $paramsStr . '8djUK*014kJ';
            $paramsMd5 = md5($paramsStr);
            if ($wenhao) {
                $signurl = $url . "&sign=$paramsMd5";
                if ($user_id) $signurl .= "&user_id=$user_id&token=$token";
            } else {
                $signurl = $url . ('/sign/' . $paramsMd5);
                if ($user_id) $signurl .= "/user_id/$user_id/token/$token";
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
}