<?php
namespace Api\Controller;

class UserController extends CommonRestController {
    protected $allowMethod    = array('get','post','put'); // REST允许的请求类型列表
    protected $allowType      = array('html','xml','json'); // REST允许请求的资源类型列表

    public function _filter(&$map) {
        if (session(C('ADMIN_AUTH_KEY'))) {

        } else {
            $map['company_id'] = session("user")["company_id"];
        }
    }

    // 用户列表
    public function lists() {
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $this->setMap($map,$search);

        $model = D('User');
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $voList = $this->voList;
        foreach ($voList as $i=>$v) {
            if ($v['head_image']) {
                $voList[$i]['head_image'] = getHttpRooDir().'/Public'.$v['head_image'];
            }
        }
        $result = $this->createResult(200, "", $voList);

        $this->response($result,'json');
    }

    // 用户详情
    public function detail() {
        parent::detail();
    }

    // 用户授权
    public function userSetPermissions() {
        $this->response($this->createResult(0, "敬请期待"), "json");
    }

    // 添加用户
    public function add() {
        parent::add();
    }

    // 用户离职
    public function del() {
        parent::del();
    }
}