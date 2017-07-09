<?php
namespace Home\Controller;

class UserController extends CommonController {
    public $user;

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"用户管理");
    }

    public function index() {
        $this->display();
    }

}