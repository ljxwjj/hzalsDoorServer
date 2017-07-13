<?php
namespace Home\Controller;

use Lib\ORG\Util\Page;

class UserController extends CommonController {
    public $user;

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"用户管理");
    }

    /**
     * 查询列表初始化搜索条件配置*
     *
     * @param array $map
     */
    public function _filter(&$map){
        if (!I('search_status')) {
            $map['status'] = array('neq', -1);
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

    public function index() {
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
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
        $model = M('UserView');
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        //parent::index();
        $voList = $this->voList;
        $this->assign('list', $voList);
        $this->display();
    }


    protected function _list($model, $map, $sortBy = '', $asc = false) {
        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = $asc ? 'asc' : 'desc';
        }
        //取得满足条件的记录数
        $count = $model->where($map)->count('id');
        if ($count > 0) {
            import ( '@.ORG.Util.Page' );
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = C('LIST_ROWS');
            }
            $p = new Page($count, $listRows);
            //分页查询数据
            $this->voList = $model->where($map)
                ->order("`" . $order . "` " . $sort)
                ->limit($p->firstRow . ',' . $p->listRows)
                ->select();

            //分页显示
            $page = $p->show();

            //列表排序显示
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
            $sort = $sort == 'desc' ? 1 : 0; //排序方式
            //模板赋值显示
            $this->assign('sort', $sort);
            $this->assign('order', $order);
            $this->assign('sortImg', $sortImg);
            $this->assign('sortType', $sortAlt);
            $this->assign("page", $page);
            $this->assign('map',$p->parameter);

        }
        cookie('_currentUrl_', __SELF__);
        return;
    }


    public function view($id) {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => $id);
        $vo = $model->where($condition)->find();
        if ($vo) {
            $sql = "select user.*, auth_role.name AS role_name from user ".
                "LEFT JOIN auth_role_user on user.id = auth_role_user.user_id ".
                "LEFT JOIN auth_role on auth_role_user.role_id = auth_role.id where user.company_id = %d";
            $arrList = M('User')->query($sql, $id);

            $this->assign('vo', $vo);
            $this->assign('arrList', $arrList);
            $this->display();
        } else {
            $this->error("页面未找到", 'index');
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

        $this->_loadAuthRole($companyId);
        $this->assign('vo', $vo);
        $this->keepSearch();

        $this->display();
    }

    public function edit($name = "")
    {
        $this->keepSearch();
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $id = (int)I($model->getPk());
        if (empty($id)) {
            $this->error('请选择要编辑的数据！');
            exit;
        }
        $vo = $model->getById($id);
        $role_id = M("AuthRoleUser")->where(array('user_id'=>$id))->getField("role_id");
        $vo['role_id'] = $role_id;
        if($vo){
            $this->_loadAuthRole($vo['company_id']);
            $this->assign('vo', $vo);
            $this->display();
        }else{
            $this->error('没有找到要编辑的数据！');
        }
    }

    private function _loadAuthRole($companyId) {
        $myUserId = $_SESSION[C('USER_AUTH_KEY')];
        $mylevel = M('AuthRoleUser')
            ->join('JOIN auth_role ON auth_role_user.role_id = auth_role.id')
            ->where(array('auth_role_user.user_id'=>$myUserId))
            ->getField('level');
        $map['level'] = array('EGT', $mylevel);  // >=
        if (session('company_id') == 1 && $companyId == 1) {
            $map['level'] = array('ELT', 2);  // <=
        } else if (session('company_id') == 1) {
            $map['level'] = array('EGT', 3);  // >=
        }
        $roleList = M('AuthRole')->where($map)->getField('id, name');
        $this->assign('roleList', $roleList);


    }

    public function save($name = '', $tpl = 'edit')
    {
        $is_add_tpl_file = $this->isAddTplFile();

        if(empty(I('account'))) {
            $error['account']='账号不能为空！';
        }
        if (!I('role')){
            $error['role']='角色不能为空！';
        }
        if (empty(I('company_id'))) {
            $_REQUEST['company_id'] = session('company_id');
        } else {
            if (I('company_id') != session('company_id') && !session(C('ADMIN_AUTH_KEY'))) {
                $error['company_id']='非法操作！';
            }
        }
        if ($error) {
            $this->_loadAuthRole(I('company_id'));

            $this->assign('vo', $_REQUEST);
            $this->assign('error', $error);
            $this->display('add');
            return;
        }

        $model = D('User');
        $id = (int)I($model->getPk());
        $account = I('account');
        $role = (int)I('role');

        $model->startTrans();
        if ($id) {// 编辑
            $userRoleId = $model->query("select role_id from auth_role_user where user_id = %d", $id);
            if (empty($userRoleId)) {
                $roleSaveFlag = $model->execute("insert into auth_role_user(role_id, user_id) values(%d, %d)", $role, $id);
            } else if ($userRoleId[0]['role_id'] != $role) {
                $roleSaveFlag = $model->execute("update auth_role_user set role_id = %d where user_id = %d", $role, $id);
            } else {
                $roleSaveFlag = true;
            }
        } else {// 新增
            $roleSaveFlag = true;
            if ((int)I('company_id') === 1 && (int)I('role') < 20) {
                $_REQUEST['is_admin'] = 1;
            }
        }


        if ($roleSaveFlag) {
            $data = $model->create($_REQUEST);
            if (!$data) {
                $error = $model->getError();
                $this->assign('vo', $_REQUEST);
                $this->assign('error', $error);
                if (!$id && $is_add_tpl_file) {
                    $this->display('add');
                } else {
                    $this->display($tpl);
                }
            } else {
                if ($id) {
                    $result = $model->save($data);
                } else {
                    $result = $model->add($data);
                    $roleSaveFlag = $model->execute("insert into auth_role_user(role_id, user_id) values(%d, %d)", $role, $result);
                }

            }
        }
        if ($roleSaveFlag && $result) {
            $model->commit();
            $this->success('数据已保存！', $this->getReturnUrl());
        } else {
            $model->rollback();
            $this->error('数据未保存！'.$model->getError(), $this->getReturnUrl());
        }
    }

}