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

    public function resume(){
        //恢复指定记录
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => $id);
        $data = $model->where($condition)->find();
        if (!$data) {
            $this->response($this->createResult(0, "状态恢复失败！"), "json");
            return;
        }
        $map = array(
            'account'=> $data['account'],
            'company_id' => array('neq', $data['company_id']),
            'status' => array('in', '0,1'));
        $others = $model->where($map)->find();
        if ($others) {
            $this->response($this->createResult(0, "状态恢复失败，已在其它公司帐号下使用！"), "json");
            return;
        }
        if (empty($data['password'])) {
            $data['status'] = 0;
        } else {
            $data['status'] = 1;
        }
        $result = $model->save($data);
        if ($result) {
            $this->response($this->createResult(200, "状态恢复成功！"), "json");
        } else {
            $this->response($this->createResult(0, "状态恢复失败！"), "json");
        }
    }

    public function forbid() {
        parent::forbid();
    }

    // 用户离职
    public function del() {
        parent::del();
    }
}