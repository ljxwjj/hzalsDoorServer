<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public $user;

    public function _initialize() {
        $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
        if (empty($user)) {
            $actionName = strtolower(ACTION_NAME);
            if (!in_array($actionName, array())) {
                echo U("user/login");
//                header("location: ". U("user/login"));
                exit;
            }
        }
        $this->user = $user;
    }

    public function index(){
        $this->display();
    }
}