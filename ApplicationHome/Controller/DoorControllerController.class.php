<?php
namespace Home\Controller;

class DoorControllerController extends CommonController {

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"控制器管理");
    }

    public function index(){
        $this->display();
    }
}