<?php
namespace Home\Controller;

class RequestUseController extends CommonController {

    public function _initialize() {
        $this->assign('pagetitle',"客户审请审核");
        parent::_initialize();
    }
    /**
     * 查询列表初始化搜索条件配置*
     *
     * @param array $map
     */
    public function _filter(&$map){
        $map['status'] = array('neq',-2);
    }

    public function index() {
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $this->setMap($map,$search);

        $this->keepSearch();
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            parent::_list($model, $map);
        }
        //parent::index();
        $voList = $this->voList;
        $this->assign('list', $voList);
        $this->display();
    }

    public function editSave() {
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $this->setMap($map,$search);

        $this->keepSearch();

        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $id = (int)I($model->getPk());
        $data = $model->create($_REQUEST);
        if(!$data){
            $error = $model->getError();
            $this->assign('vo',$_REQUEST);
            $this->assign('error',$error);
            $this->display('edit');
        }else{
            $result = $model->save($data);
            if($result){
                parent::_list($model, $map);
                $voList = $this->voList;
                $this->assign('list', $voList);
                $this->display('index');
            }else{
                $this->error('数据未保存！',$this->getReturnUrl());
            }
        }
    }

    public function save() {
        if(empty($_POST['company'])) {
            $error['company']='单位名称不能为空！';
        }
        if (empty($_POST['contacts'])){
            $error['contacts']='联系人姓名不能为空！';
        }
        if(empty($_POST['telphone'])) {
            $error['telphone']='联系电话不能为空！';
        }
        if (empty($_POST['order_number'])){
            $error['order_number']='订单号不能为空！';
        }

        if ($error) {
            $this->assign('error',$error);
            $this->assign('vo',$_POST);
            $this->display('add');
            return;
        }
        $map = array();
        $map["telphone"] = $_POST['contacts'];
        $map['status'] = array("neq", -1);
        $model = M("RequestUse");
        $data = $model->where($map)->find();
        if ($data) {
            $error['telphone'] = '该手机号已审请过，请等待审核';

            $this->assign('error',$error);
            $this->assign('vo',$_POST);
            $this->display('add');
            return;
        }

        $data = $model->create();
        if(!$data){
            $error = $model->getError();
            $this->assign('vo',$_REQUEST);
            $this->assign('error',$error);
            $this->display('add');
        }else{
            $result = $model->add($data);
            if($result){
                $this->display(T('saveSuccess'));
//                $this->success('数据已保存！', U("saveSuccess"));
            }else{
                $this->error('数据未保存！', U("add"));
            }
        }
    }

    public function saveSuccess() {
        $this->display();
    }


    /**
     * 拒绝操作
     *
     * @param string 模型对象
     */
    public function forbid() {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => array('in', $id));
        $list = $model->forbid($condition);
        if ($list !== false) {
            $this->success('拒绝审核成功！',$this->getReturnUrl());
        } else {
            $this->error('操作失败！',$this->getReturnUrl());
        }
    }

    /**
     * 通过操作
     *
     * @param string 模型对象
     */
    public function resume() {
        //恢复指定记录

        $model = M();
        $id = I('id');
        $condition = array('id' => $id);

        $requestUse = $model->table('request_use')->where($condition)->find();
        if ($requestUse) {
            $model->startTrans();;
            $data = array('name'=>$requestUse['company'],
                'admin_mobile'=>$requestUse['telphone'],
                'status'=> 0,
                'create_time'=>time(),
                'update_time' => time(),);
            $companyId = $model->table('company')->data($data)->add();

            if ($companyId) {
                $data = array('company_id' => $companyId,
                    'account' => $requestUse['telphone'],
                    'mobile' => $requestUse['telphone'],
                    'nickname' => $requestUse['contacts'],
                    'is_admin' => 0,
                    'status' => 0,);
                $userId = $model->table('user')->data($data)->add();
            }

            if ($userId) {
                $data = array(
                    'role_id' => 3,
                    'user_id' => $userId,
                );
                $roleUserResult = $model->table('auth_role_user')->data($data)->add();
            }
        }
        if ($roleUserResult && false !== $model->table('request_use')->where($condition)->setField('status',1)) {
            $model->commit();
            $this->success('通过审核成功！',$this->getReturnUrl());
        } else {
            $model->rollback();
            $this->error('操作失败！',$this->getReturnUrl());
        }
    }


    /**
     * 默认删除操作
     * @param string $name 数据对象
     * @return string
     */
    public function del($name="") {
        //虚拟删除指定记录
        $name = $name ? $name : $this->getActionName();
        $model = M($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = I($pk);

            //exit;
            if (isset($id)) {
                $condition = array($pk => array('in', explode(',', $id)));
                $list = $model->where($condition)->setField('status', -2);
                if ($list !== false) {
                    $this->success('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
    }

}