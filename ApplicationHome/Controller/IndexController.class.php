<?php
namespace Home\Controller;

class IndexController extends CommonController {

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"首页");
    }

    public function index(){
        //echo MODULE_PATH;echo "\n";echo APP_PATH;exit;
        $this->display();
    }
}