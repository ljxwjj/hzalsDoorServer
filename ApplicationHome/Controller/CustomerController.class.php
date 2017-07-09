<?php
namespace Home\Controller;

class CustomerController extends CommonController {

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"首页");
    }

    public function index(){
        $this->display();
    }
}