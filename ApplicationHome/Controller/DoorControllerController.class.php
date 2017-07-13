<?php
namespace Home\Controller;

use Lib\ORG\Util\Page;

class DoorControllerController extends CommonController {

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"控制器管理");
    }

    /**
     * 查询列表初始化搜索条件配置*
     *
     * @param array $map
     */
    public function _filter(&$map){
        $user_id = $_SESSION[C('USER_AUTH_KEY')];
        $company_id = M('User')->where(array('id'=>$user_id))->getField('company_id');
        if ($company_id > 1) {
            $map['company_id'] = $company_id;
        }
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

    public function index($name = "")
    {
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $map['status'] = array("neq","-1");
        $this->setMap($map,$search);

        $company_id = session('company_id');
        if ($_REQUEST['company_id']) {
            $company_id = $_REQUEST['company_id'];
            $map['company_id'] = $company_id;
            $this->assign('company_id', $company_id);
        } else if ($company_id > 1) {
            $map['company_id'] = $company_id;
            $this->assign('company_id', $company_id);
        }

        $this->keepSearch();
        $model = M('DoorControllerView');
        if (!empty($model)) {
            $this->_list($model, $map);
        }

        //保持分页记录
        $nowpage = (int)I('p')?(int)I('p'):(int)I('search_p');
        if($nowpage){
            $this->assign('nowpage', $nowpage);
        }

        $voList = $this->voList;
        $this->assign('list', $voList);
        $this->display();
    }



    /**
     * 编辑*
     *
     * @param string $name 数据对象
     */
    function edit($name="") {
        $this->keepSearch();
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $id = (int)I($model->getPk());
        if (empty($id)) {
            $this->error('请选择要编辑的数据！');
            exit;
        }
        $vo = $model->getById($id);
        if($vo){
            $this->assign('vo', $vo);
            $this->display();
        }else{
            $this->error('没有找到要编辑的数据！');
        }
    }

    public function add()
    {
        $myUserId = $_SESSION[C('USER_AUTH_KEY')];

        $companyId = I('company_id');
        if (!$companyId) {
            $companyId = M('User')->where(array('id'=>$myUserId))->getField('company_id');
        }
        $vo['company_id'] = $companyId;

        $this->assign('vo', $vo);
        $this->keepSearch();

        $this->display();
    }


    /**
     * 保存*
     *
     */
    public function save() {
        $this->keepSearch();
        $is_add_tpl_file = $this->isAddTplFile();
        $name = $this->getActionName();
        $model = D($name);
        $id = (int)I($model->getPk());

        if(empty(I('serial_number'))) {
            $error['serial_number']='序列号不能为空！';
        }
        if (empty(I('model'))){
            $error['model']='角色不能为空！';
        }
        if (empty(I('company_id'))) {
            $_REQUEST['company_id'] = session('company_id');
        } else {
            if (I('company_id') != session('company_id') && !session(C('ADMIN_AUTH_KEY'))) {
                $error['company_id']='非法操作！';
            }
        }
        if ($error) {
            $this->assign('vo', $_REQUEST);
            $this->assign('error', $error);
            if(!$id && $is_add_tpl_file){
                $this->display('add');
            }else{
                $this->display('edit');
            }
            return;
        }

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
        }else{
            if($id){
                $result = $model->save($data);
            }else{
                $result = $model->add($data);
            }
            if($result){
                $this->success('数据已保存！',$this->getReturnUrl());
            }else{
//                $this->display('edit');
                $this->error('数据未保存！',$this->getReturnUrl());
            }
        }
    }

    public function view($id) {
        $model = M('DoorControllerView');
        $condition = array('id' => $id);
        $vo = $model->where($condition)->find();
        if ($vo) {
            $this->assign('vo', $vo);
            $this->display();
        } else {
            $this->error("页面未找到", 'index');
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

            if (isset($id)) {
                $condition = array($pk => array('in', explode(',', $id)));
                $list = $model->where($condition)->setField('status', -1);
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