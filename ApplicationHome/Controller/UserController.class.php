<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends Controller {
    public $user;

    public function _initialize() {
        $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
        if (empty($user)) {
            $actionName = strtolower(ACTION_NAME);
            if (!in_array($actionName, array("login", "checklogin"))) {
                header("location: ". U("user/login"));
                exit;
            }
        }
        $this->user = $user;
    }

    public function index() {
        $this->display();
    }

    public function login() {
        $this->display();
    }

    public function checkLogin($mobile, $password) {
        if ($password) $password = md5($password);
        $User = M('User');  // D('User');
        $map['mobile'] = $mobile;
        $user = $User->where($map)->find();
        if ($user && $user['password'] === $password) {
            // 登录成功
            $_SESSION['user'] = $user;
            $this->success("登录成功", U("Index/index"));
        } else {
            $this->error("用户名或密码错误");
        }
    }

    public function logout() {
        unset($_SESSION["user"]);
        $this->success("退出成功", U("Index/index"));
    }
}