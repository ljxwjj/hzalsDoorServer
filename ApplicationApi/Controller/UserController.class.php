<?php
namespace Api\Controller;

class UserController extends CommonRestController {
    protected $allowMethod    = array('get','post','put'); // REST允许的请求类型列表
    protected $allowType      = array('html','xml','json'); // REST允许请求的资源类型列表


    // 用户列表
    public function userList() {

    }

    // 用户详情
    public function userInfo() {

    }

    // 用户授权
    public function userSetPermissions() {

    }

    // 添加用户
    public function addUser() {

    }

    // 用户离职
    public function deleteUser() {

    }
}