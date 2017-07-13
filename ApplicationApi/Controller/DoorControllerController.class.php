<?php
namespace Api\Controller;

class DoorControllerController extends CommonRestController {

    public function _filter(&$map) {
        if (session(C('ADMIN_AUTH_KEY'))) {

        } else {
            $map['company_id'] = session("user")["company_id"];
        }
    }

    // 公司门禁清单
    public function lists() {
        parent::lists("DoorControllerView");
    }

    public function detail(){
        parent::detail();
    }

    // 添加控制器
    public function add() {
        parent::add();
    }

    // 删除控制器
    public function del() {
        parent::del();
    }

    // 开门
    public function openDoor() {

    }
}