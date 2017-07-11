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
            $_SESSION[C('USER_AUTH_KEY')]	=	$authInfo['id'];
            $_SESSION['email']	            =	$authInfo['email'];
            $_SESSION['loginUserName']		=	$authInfo['nickname'];
            $_SESSION['lastLoginTime']		=	$authInfo['last_login_time'];
            $_SESSION['login_count']	    =	$authInfo['login_count'];
            $_SESSION['account']	        =	$authInfo['account'];
            $_SESSION['company_id']         =   $authInfo['company_id'];
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
                $token = D('UserToken')->where(array('user_id'=>$user_id))->getField('token');
                $params['token'] = $token;
            }

            ksort($params);
            $paramsStr = implode("", $params);
            strrev($paramsStr);
            $paramsStr = $paramsStr . '8djUK*014kJ';
            $paramsMd5 = md5($paramsStr);
            if ($wenhao) {
                $signurl = $url . "&sign=$paramsMd5";
            } else {
                $signurl = $url . ('/sign/' . $paramsMd5);
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