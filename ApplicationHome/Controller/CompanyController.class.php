<?php
namespace Home\Controller;

use Lib\ORG\Util\CheckError;

class CompanyController extends CommonController {

    public function _initialize() {
        $this->assign('pagetitle',"客户管理");
        parent::_initialize();
    }
    /**
     * 查询列表初始化搜索条件配置*
     *
     * @param array $map
     */
    public function _filter(&$map){
        $map['status'] = array('eq',0);
    }

    /**
     * 设置查询条件*
     *
     * @param array $map  查询条件
     * @param array $search 搜索数组
     */
    protected function setMap(&$map,&$search){
        foreach ($_REQUEST as $key => $val) {
            if($val == "") {
                continue;
            }
            if (ereg("^search_", $key)) {
                $field = str_replace('search_','',$key);
                $map[$field] = $val;
                switch($field){
                    case 'account':
                    case 'nickname':
                        $map[$field] = array('like',"%".$val."%");
                        $search[$key] = $val;
                        break;
                    default:
                        $map[$field] = $val;
                        $search[$key] = $val;
                        break;
                }
            }
        }
    }

    /**
     * 用户列表(查询)*
     *
     */
    function index(){
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
    /**
     * 保存*
     *
     * @param string $name 数据对象
     * @param string $tpl  模板名称
     */
    public function save() {
        $is_add_tpl_file = $this->isAddTplFile();
        $name = $this->getActionName();
        $model = D($name);
        $id = (int)I($model->getPk());
        $data = $model->create($_REQUEST);
        if(!$data){
            $error = $model->getError();
            $this->assign('vo',$_REQUEST);
            $this->assign('error',$error);
            if(!$id && $is_add_tpl_file){
                $this->display('add');
            }else{
                $this->display('edit');
            }
            return;
        }
        if ($id) { // 编辑
            $result = $model->save($data);
        } else { // 新增
            if (empty(I('contacts'))) {
                $error['contacts'] = "联系人不能为空";
                $this->assign('vo',$_REQUEST);
                $this->assign('error',$error);
                $this->display('add');
                return;
            }
            $model->startTrans();
            $companyId = $model->add($data);
            if ($companyId) {
                $data = array('company_id' => $companyId,
                    'account' => I('admin_mobile'),
                    'mobile' => I('admin_mobile'),
                    'nickname' => I('contacts'),
                    'is_admin' => 0,
                    'status' => 0,);
                $userId = $model->table('user')->data($data)->add();
            }
            if ($userId) {
                $data = array(
                    'role_id' => 20,
                    'user_id' => $userId,
                );
                $result = $model->table('auth_role_user')->data($data)->add();
            }
            if ($result) {
                $model->commit();
            } else {
                $model->rollback();
            }
        }
        if($result){
            $this->success('数据已保存！',$this->getReturnUrl());
        }else{
            $this->error('数据未保存！',$this->getReturnUrl());
        }
    }


    /**
     * 默认禁用操作
     *
     * @param string 模型对象
     */
    public function forbid($name='') {
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => array('in', $id));
        $list = $model->forbid($condition);
        if ($list !== false) {
            $this->success('状态禁用成功',$this->getReturnUrl());
        } else {
            $this->error('状态禁用失败！',$this->getReturnUrl());
        }
    }

    /**
     * 默认恢复操作
     *
     * @param string 模型对象
     */
    public function resume($name='') {
        //恢复指定记录
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => array('in', $id));
        if (false !== $model->resume($condition)) {
            $this->success('状态恢复成功！',$this->getReturnUrl());
        } else {
            $this->error('状态恢复失败！',$this->getReturnUrl());
        }
    }

    public function view($id) {
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => $id);
        $vo = $model->where($condition)->find();
        if ($vo) {
//            $sql = "select user.*, auth_role.name AS role_name from user ".
//                "LEFT JOIN auth_role_user on user.id = auth_role_user.user_id ".
//                "LEFT JOIN auth_role on auth_role_user.role_id = auth_role.id where user.company_id = %d";
//            $arrList = M('User')->query($sql, $id);
            if ($vo['expiration_date']) {
                $expirationDate = date($vo['expiration_date']);
                $vo['expiration_status'] = $expirationDate < date('Y-m-d')?"已过期":"";
            }
            $this->assign('vo', $vo);
//            $this->assign('arrList', $arrList);
            $this->display();
        } else {
            $this->error("页面未找到");
        }
    }

    public function del()
    {
        $id = (int)I('id');
        $model = M('Company');
        if (!$id) {
            $this->error('非法操作');
            exit;
        }
        if ($id === 1) {
            $this->error('系统用户，禁止删失败！');
            exit;
        }
        $myUserId = $_SESSION[C('USER_AUTH_KEY')];
        $role_id = M("AuthRoleUser")->where(array('user_id'=>$myUserId))->getField("role_id");
        if ($role_id != 18) {
            $this->error('非超级系统管理员，禁止该删除操作！');
            exit;
        }

        $model->startTrans();
        $model->execute("update user set status = -1 where company_id = %d", $id);
        $condition = array('id' => $id);
        $list = $model->where($condition)->setField('status', -1);
        if ($list !== false) {
            $model->commit();
            $this->success('删除成功！');
        } else {
            $model->rollback();
            $this->error('删除失败！');
        }

    }
}