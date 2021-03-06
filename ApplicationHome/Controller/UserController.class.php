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
            $map['status'] = array('in', '0,1');
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
            $this->_list($model, $map, 'id');
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
            import ( '@.Lib.ORG.Util.Page' );
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


    public function view() {
        $model = M('UserView');
        $id = I('id');
        $vo = $model->where("id=$id")->find();
        if ($vo) {
            $this->assign('vo', $vo);
            $this->display();
        } else {
            $this->error("页面未找到");
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
        $this->_loadDepartment($companyId);
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
        $vo['department_id'] = M('UserDepartment')->where(array('user_id'=>$id))->getField('department_id');
        if($vo){
            $this->_loadAuthRole($vo['company_id']);
            $this->_loadDepartment($vo['company_id']);
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

    private function _loadDepartment($companyId) {
        $Department = M('Department');
        $departmentList = $Department->where(array('company_id'=>$companyId))->select();
        $this->assign('departmentList', $departmentList);
    }

    public function save($name = '', $tpl = 'edit')
    {
        $is_add_tpl_file = $this->isAddTplFile();

        $id = (int)I('id');
        $company_id = I('company_id');
        $account = I('account');
        $role = (int)I('role');
        $department = (int)I('department');
        $card_number = I('card_number');

        if(empty(I('account'))) {
            $error['account']='账号不能为空！';
        }
        if (!$role){
            $error['role']='角色不能为空！';
        }

        $model = D('User');
        if ($card_number) {
            $map = array('card_number'=>$card_number);
            if ($id) $map['id'] = array('neq', $id);
            $numberUser = $model->where($map)->find();
            if ($numberUser) {
                $error['card_number']='该卡号已被其他人使用过！';
            }
        }

        if ($id) {
            // 编辑时不能改变公司
            $myUserId = $_SESSION[C('USER_AUTH_KEY')];
            $myRole = M('AuthRoleUser')->where("user_id=$myUserId")->getField('role_id');
            if ($id == $myUserId && $role != $myRole) {
                $error['role']='不能修改自己的角色！';
            }
        } else if (empty($company_id)) {
            $company_id = session('company_id');
            $_REQUEST['company_id'] = $company_id;
        } else {
            if ($company_id != session('company_id') && !session(C('ADMIN_AUTH_KEY'))) {
                $error['company_id']='非法操作！';
            }
        }
        if ($error) {
            $vo = $_REQUEST;
            if ($id) {
                $role_id = M("AuthRoleUser")->where(array('user_id'=>$id))->getField("role_id");
                $vo['role_id'] = $role_id;
                $vo['department_id'] = M('UserDepartment')->where(array('user_id'=>$id))->getField('department_id');
            }
            $this->_loadAuthRole($company_id);
            $this->_loadDepartment($company_id);

            $this->assign('vo', $vo);
            $this->assign('error', $error);
            $this->display('edit');
            return;
        }


        if (!$id) {
            $user = $model->where(array('account' => $account, 'status' => array("neq", -1)))->find();
            if ($user && $user['company_id'] != $_REQUEST['company_id']) {
                $this->error('该手机号已被在其它公司注册过！' . $model->getError(), $this->getReturnUrl());
                exit;
            }

            $user = $model->where(array('account' => $account, 'company_id' => $_REQUEST['company_id']))->find();
            if ($user && $user['status'] == -1) {
                $user['status'] = empty($user['password']) ? 0 : 1;
                $model->save($user);
                $this->success('用户数据已恢复！', $this->getReturnUrl());
                exit;
            } else if ($user) {
                $this->error('该用户已存在！' . $model->getError(), $this->getReturnUrl());
                exit;
            }
        }

        $model->startTrans();
        if ($id) {// 编辑
            $model->execute("delete from auth_role_user where user_id = %d", $id);
            $model->execute("delete from user_department where user_id = %d", $id);
        } else {// 新增
            $roleSaveFlag = true;
            if ((int)I('company_id') === 1 && (int)I('role') < 20) {
                $_REQUEST['is_admin'] = 1;
            }
        }

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
                if ($_SESSION[C('ADMIN_AUTH_KEY')]) {
                    if (!isset($data['splash_display'])) $data['splash_display'] = '0';
                    $editFields = "account,nickname,sex,email,mobile,card_number,splash_display,splash_exp";
                } else {
                    $editFields = "account,nickname,sex,email,mobile,card_number";
                }
                $result = $model->field($editFields)->save($data);
            } else {
                $result = $model->add($data);
                if ($result) $id = $result;
            }
            if ($role) {
                $roleSaveFlag = $model->execute("insert into auth_role_user(role_id, user_id) values(%d, %d)", $role, $id);
            } else {
                $roleSaveFlag = true;
            }
            if ($department) {
                $departmentSaveFlag = $model->execute("insert into user_department(department_id, user_id) values(%d, %d)", $department, $id);
            } else {
                $departmentSaveFlag = true;
            }
        }

        if ($roleSaveFlag && $departmentSaveFlag && $result) {
            $model->commit();
            // 针对用户，梳理卡片授权信息
            checkUserCardsByUser($id);

            // 用户信息同步到uface用户
            $guid = checkUfaceUser($id, $data);

            if ($role < 22) { // 管理员默认授权所有人脸设备
                $this->defaultUfaceUserDevices($company_id, $guid);
            }
            $this->success('数据已保存！', $this->getReturnUrl());
        } else {
            $model->rollback();
            $this->error('数据未保存！'.$model->getError(), $this->getReturnUrl());
        }
    }

    public function forbid($name='') {
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => array('in', $id));
        $list = $model->forbid($condition);
        if ($list !== false) {
            checkUserCardsByUser($id);
            deleteUfaceUser($id);
            $this->success('状态禁用成功',$this->getReturnUrl());
        } else {
            $this->error('状态禁用失败！',$this->getReturnUrl());
        }
    }

    public function doorlist() {
        $id = I('id');
        $company_id = I('company_id');
        if (empty($id)) {
            $this->error('请选择要分配权限的用户！');
            exit;
        }
        $user = M('User')->find($id);
        $this->assign('user', $user);
        if (empty($company_id)) {
            $company_id = $user['company_id'];
        }

        $arrList = $this->getDoorByUser($company_id, $id);
        $this->assign('arrList', $arrList);
        $ufaceList = $this->getUfaceByUser($company_id, $id);//var_dump($ufaceList);exit;
        $this->assign('ufaceList', $ufaceList);
        $this->display();
    }

    public function savenodes($id='') {
        $user_id = $id;
        $node_id = I('node_id');
        $uface_id = I('uface_id');

        $UserDoor = M('UserDoor');
        $map['user_id'] = array('eq',$user_id);
        $UserDoor->where($map)->delete();

        if ($node_id) {
            foreach ($node_id as $controller_id => $doors) {
                foreach ($doors as $door_id) {
                    $data[] = array('user_id' => $user_id, 'controller_id' => $controller_id, 'door_id' => intval($door_id));
                }
            }
            $result = $UserDoor->addAll($data);
        } else {
            $result = true;
        }
        $this->savePersonDevices($user_id, $uface_id);
        if ($result) {
            checkUserCardsByUser($id);
            $this->success('数据已保存！', $this->getReturnUrl());
        } else {
            $this->error('数据未保存！'.$UserDoor->getError(), $this->getReturnUrl());
        }
    }

    /**
     * 查询岗位权限*
     *
     * @param int $id 部门ID
     * @return array
     */
    protected function getDoorByUser($company_id, $id){
        if (empty($id)) {
            $this->error('请选择要分配权限的用户！');
            exit;
        }

        //取得所有门禁
        $Node = D('DoorController');
        $map = array();
        $map['company_id'] = $company_id;
        $map['status'] = 0;
        $controllers = $Node->where($map)->getField("id,name,door_count");

        $cid = array_keys($controllers);
        $map = array('controller_id'=>array('in', $cid));
        $doors = M('Door')->where($map)->select();
        foreach ($doors as $door) {
            $doorsMap[$door['controller_id']][$door['door_index']] = $door;
        }

        $arrTree = array();
        foreach ($controllers as $value) {
            for ($j = 0; $j < $value['door_count']; $j++) {
                $door = $doorsMap[$value['id']][$j];
                if (!$door) {
                    $door = array();
                    $door['controller_id'] = $value['id'];
                    $door['door_index'] = $j;
                    $door['name'] = $j."号门";
                }
                $door['cate_lv']=0;
                $door['cate_namepre'] = '';
                $door['controller_name'] = $value['name'];
                $arrTree[]=$door;
            }
        }


        //取得已分配的权限
        $Access = D('UserDoor');
        $map = array();
        $map['user_id'] = array('eq',$id);
        $doors = $Access->where($map)->select();
        $doorsMap = array();
        foreach ($doors as $door) {
            $doorsMap[$door['controller_id']][$door['door_id']] = 1;
        }

        //匹配分配岗位
        foreach($arrTree as $k=>$v){
            if ($doorsMap[$v['controller_id']][$v['door_index']] === 1) {
                $arrTree[$k]['checked'] = 1;
            }
        }
        return $arrTree;
    }

    protected function getUfaceByUser($company_id, $id) {
        $guid = D('UfaceUser')->where("user_id = $id")->getField("uface_guid");
        //取得所有人脸设备
        $map = array();
        $map['company_id'] = $company_id;
        $ufaces = D('UfaceDevice')->where($map)->select();

        if ($guid) {
            //取得已分配的权限
            $response = ufaceApiAutoParams('get', array(
                C('UFACE_APP_ID'), "/person/", $guid, "/devices"
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'guid' => $guid,
                'idcardNo' => "",
            ));
            if ($response->result == 1) {
                $allDevices = array();
                foreach ($response->data as $device) {
                    $allDevices[] = $device->deviceKey;
                }
                foreach ($ufaces as $key => $uface) {
                    if (in_array($uface["device_key"], $allDevices)) {
                        $ufaces[$key]["checked"] = 1;
                    }
                }
            } else {
                $error = $response->msg;
            }
        }
        return $ufaces;
    }

    protected function savePersonDevices($user_id, $uface_id) {
        $guid = D('UfaceUser')->where("user_id = $user_id")->getField("uface_guid");
        if (!$guid) return array();

        //取得已分配的权限
        $response = ufaceApiAutoParams('get', array(
            C('UFACE_APP_ID'), "/person/", $guid, "/devices"
        ), array(
            'appId' => C('UFACE_APP_ID'),
            'guid' => $guid,
            'idcardNo' => "",
        ));
        $allDevices = array();
        if ($response->result == 1) {
            foreach ($response->data as $device) {
                $allDevices[] = $device->deviceKey;
            }
        } else {
            $error = $response->msg;
        }

        $role_id = M('AuthRoleUser')->where(array('user_id'=>$user_id))->getField('role_id');

        if ($role_id < 22) {
            $company_id = D("User")->where("id=$user_id")->getField("company_id");
            $map = array("company_id"=>$company_id);
            $selectedDevices = D("UfaceDevice")->where($map)->getField("device_key", true);
        } else if ($uface_id) {
            $company_id = D("User")->where("id=$user_id")->getField("company_id");
            $map = array("id" => array("in", $uface_id), "company_id"=>$company_id);
            $selectedDevices = D("UfaceDevice")->where($map)->getField("device_key", true);
        } else {
            $selectedDevices = array();
        }
        $addDevices = array_diff($selectedDevices, $allDevices); // 计算新增权限
        $delDevices = array_diff($allDevices, $selectedDevices); // 计算撤销权限

        if ($addDevices) {// 人员授权
            $response = ufaceApiAutoParams('post', array(
                C('UFACE_APP_ID'), "/person/", $guid, "/devices"
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'guid' => $guid,
                'deviceKeys' => implode(",", $addDevices),
            ));
            if ($response->result == 1) {

            } else {
                $error = $response->msg;
            }
        }

        if ($delDevices) {// 人员销权
            $response = ufaceApiAutoParams('post', array(
                C('UFACE_APP_ID'), "/person/", $guid, "/devices/delete"
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'guid' => $guid,
                'deviceKeys' => implode(",", $delDevices),
            ));
            if ($response->result == 1) {

            } else {
                $error = $response->msg;
            }
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
        $condition = array($pk => $id);
        $data = $model->where($condition)->find();
        if (!$data) {
            $this->error('状态恢复失败！',$this->getReturnUrl());
            return;
        }
        $map = array(
            'account'=> $data['account'],
            'company_id' => array('neq', $data['company_id']),
            'status' => array('in', '0,1'));
        $others = $model->where($map)->find();
        if ($others) {
            $this->error('状态恢复失败，已在其它公司帐号下使用！',$this->getReturnUrl());
            return;
        }
        if (empty($data['password'])) {
            $data['status'] = 0;
        } else {
            $data['status'] = 1;
        }
        $result = $model->save($data);
        if ($result) {
            checkUserCardsByUser($id);
            checkUfaceUser($id, $data);
            $this->success('状态恢复成功！',$this->getReturnUrl());
        } else {
            $this->error('状态恢复失败！',$this->getReturnUrl());
        }
    }

    /*public function settingCard() {
        $id = I('id');

        if (empty($id)) {
            $this->error('请选择要分配权限的用户！');
            exit;
        }
        $user = M('User')->find($id);
        $this->assign('user', $user);

        if (!$user) {
            $this->error('非法操作');
            return;
        }

        $doorMap = getUserDoors2($user);

        //取得所有门禁
        $Node = D('DoorController');
        $map = array();
        $map['status'] = 0;
        $map['id'] = array('in', array_keys($doorMap));
        $controllers = $Node->where($map)->getField("id,serial_number,name,door_count");

        $cid = array_keys($controllers);
        $map = array('controller_id'=>array('in', $cid));
        $doors = M('Door')->where($map)->select();
        foreach ($doors as $door) {
            $doorsMap[$door['controller_id']][$door['door_index']] = $door;
        }

        $arrTree = array();
        foreach ($controllers as $value) {
            for ($j = 0; $j < $value['door_count']; $j++) {
                if ($doorMap[$value['id']][$j]) {
                    $door = $doorsMap[$value['id']][$j];
                    if (!$door) {
                        $door = array();
                        $door['controller_id'] = $value['id'];
                        $door['door_index'] = $j;
                        $door['name'] = $j . "号门";
                    }
                    $door['cate_lv'] = 0;
                    $door['cate_namepre'] = '';
                    $door['controller_name'] = $value['name']?$value['name']:$value['serial_number'];
                    $arrTree[] = $door;
                }
            }
        }

        // 加载已选中项
        $cardNumber = $user['card_number'];
        if ($cardNumber) {
            $map = array('card_number'=>$cardNumber, 'user_id'=>$id);
            $userCards = M("DoorControllerUserCard")->where($map)->getField("controller_id, doors");
            foreach ($arrTree as $k=>$v) {
                $pos = strpos($userCards[$v['controller_id']], $v['door_index']."");
                if ($pos !== false) {
                    $arrTree[$k]['checked'] = 1;
                }
            }
        }
        // 加载控制器同步状态
        $map = array("user_id"=>$id, "status"=>0);
        $unSyncControllerIds = M("DoorControllerUserCard")->where($map)->distinct(true)->getField("controller_id", true);
        $unSyncControllers = array();
        foreach ($controllers as $value) {
            if (in_array($value['id'], $unSyncControllerIds)) {
                $unSyncControllers[] = $value;
            }
        }
        $this->assign('arrList', $arrTree);
        $this->assign("unSyncControllers", $unSyncControllers);
        $this->display();
    }

    public function settingCardSave() {
        $user_id = I('id', '', 'int');
        $card_number = I('card_number');
        $node_id = I('node_id');

        if (floatval($card_number) > 0xffffffff) {
            $this->error('卡号超长，最大值为 4294967295 ！', $this->getReturnUrl());
            return;
        }

        $user = M("User")->where("id=$user_id")->find();
        $userDoors = getUserDoors2($user);
        foreach ($node_id as $controllerId=>$doors) {
            if (is_array($userDoors[$controllerId])) {
                $doors = is_array($doors)?$doors:array($doors);
                foreach ($doors as $doorId) {
                    if (!$userDoors[$controllerId][$doorId]) {
                        $this->error('门未授权，数据未保存！', $this->getReturnUrl());
                        return;
                    }
                }
            } else {
                $this->error('控制器未授权，数据未保存！', $this->getReturnUrl());
                return;
            }
        }

        $result = updateUserCards($user_id, $card_number, $node_id);

        if ($result) {
            $this->success('数据已保存！', $this->getReturnUrl());
        } else {
            $this->error('数据未保存！', $this->getReturnUrl());
        }
    }*/

    public function employeeRegister() {
        $user_id = I('id', '', 'int');
        $user = M("User")->where("id=$user_id")->find();
        $model = M("UfaceUser");
        $guid = $model->where("user_id=$user_id")->getField("uface_guid");
        if (!$guid) {
            $response = ufaceApiAutoParams('post', array(
                C('UFACE_APP_ID'), "/person"
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'name' => $user['nickname'],
                'phone' => $user['account'],
                'idNo'  => $user['card_number'],
                'type'  => $user['id'],
            ));
            if ($response->result == 1) {
                $guid = $response->data->guid;
                $model->add(array(
                    'user_id' => $user['id'],
                    'uface_guid' => $guid,
                ));
            } else {
                $error = $response->msg;
            }
        }
        $arrList = M("UfaceDevice")->where(array("company_id"=>$user["company_id"]))->select();
        $this->assign("vo", $user);
        $this->assign("arrList", $arrList);
        $this->display();
    }

    /**
     * ajax
     * 注册任务状态变更
     */
    public function updateRegisterationState() {
        $task_id = I('task_id');
        $user_id = I('user_id', '', 'int');
        $device_id = I('device_id', '', 'int');

        $userGuid = D("UfaceUser")->where("user_id=$user_id")->getField("uface_guid");
        $deviceKey = D("UfaceDevice")->where("id=$device_id")->getField("device_key");
        $response = ufaceApiAutoParams("put", array(
            C('UFACE_APP_ID'), "/person/", $userGuid, "/device/", $deviceKey, "/registeration/state/", $task_id
        ), array(
            'appId' => C('UFACE_APP_ID'),
            'state' => 4,
            'personGuid'=> $userGuid,
            'deviceKey'  => $deviceKey,
            'taskId'       => $task_id,
        ));
        if ($response->result == 1) {
            $result['code'] = 200;
            $result['message'] = $response->msg;
            $this->response($result);
        } else {
            $result['code'] = 0;
            $result['message'] = $response->msg;
            $this->response($result);
        }
    }

    /**
     * ajax
     * 开启设备注册模式
     */
    public function turnonDeviceMode4Web() {
        $user_id = I('user_id', '', 'int');
        $device_id = I('device_id', '', 'int');

        $userGuid = D("UfaceUser")->where("user_id=$user_id")->getField("uface_guid");
        $deviceKey = D("UfaceDevice")->where("id=$device_id")->getField("device_key");
        $response = ufaceApiAutoParams("post", array(
            C('UFACE_APP_ID'), "/device/", $deviceKey, "/mode/state"
        ), array(
            'appId' => C('UFACE_APP_ID'),
            'deviceKey'  => $deviceKey,
            'type'       => 1,
            'personGuid'=> $userGuid,
        ));
        if ($response->result == 1) {
            $result['code'] = 200;
            $result['message'] = $response->msg;
            $result['data'] = $response->data;
            $this->response($result);
        } else {
            $result['code'] = 0;
            $result['message'] = $response->msg;
            $this->response($result);
        }
    }

    /**
     * ajax
     * 注册任务状态查询
     */
    public function getRegisteration4Web() {
        $user_id = I('user_id', '', 'int');
        $device_id = I('device_id', '', 'int');
        $task_id = I("task_id");
        $userGuid = D("UfaceUser")->where("user_id=$user_id")->getField("uface_guid");
        $deviceKey = D("UfaceDevice")->where("id=$device_id")->getField("device_key");
        $response = ufaceApiAutoParams("get", array(
            C('UFACE_APP_ID'), "/person/", $userGuid, "/device/", $deviceKey, "/registeration/state/", $task_id
        ), array(
            'appId' => C('UFACE_APP_ID'),
            'personGuid' => $userGuid,
            'deviceKey'  => $deviceKey,
            'taskId'=> $task_id,
        ));
        if ($response->result == 1) {
            $result['code'] = 200;
            $result['message'] = $response->msg;
            $result['data'] = $response->data;
            $this->response($result);
        } else {
            $result['code'] = 0;
            $result['message'] = $response->msg;
            $result['data'] = 0;
            $this->response($result);
        }
    }

    /**
     * ajax
     * 获取已上传照片
     */
    public function getUserFaces() {
        $user_id = I('user_id', '', 'int');
        $user = M("User")->where("id=$user_id")->find();
        if ($user) {
            $model = M("UfaceUser");
            $guid = $model->where("user_id=$user_id")->getField("uface_guid");
            if ($guid) {
                $response = ufaceApiAutoParams('get', array(
                    C('UFACE_APP_ID'), "/person/", $guid, "/faces"
                ), array(
                    'appId' => C('UFACE_APP_ID'),
                    'guid'  => $guid,
                ));
                if ($response->result == 1) {
                    $result['code'] = 200;
                    $result['message'] = "获取成功";
                    $result['data'] = $response->data;
                    $this->response($result);
                    return;
                }
            }
        } else {
            $result['code'] = 0;
            $result['message'] = "对象未找到!";
            $this->response($result);
        }
    }

    /**
     * ajax
     * 上传照片
     */
    public function ufaceValid() {
        $user_id = I('user_id', '', 'int');
        $user = M("User")->where("id=$user_id")->find();
        if ($user) {
            $model = M("UfaceUser");
            $guid = $model->where("user_id=$user_id")->getField("uface_guid");
            if (!$guid) {
                $response = ufaceApiAutoParams('post', array(
                    C('UFACE_APP_ID'), "/person"
                ), array(
                    'appId' => C('UFACE_APP_ID'),
                    'name' => $user['nickname'],
                    'phone' => $user['account'],
                    'type'  => $user['id'],
                ));
                if ($response->result == 1) {
                    $guid = $response->data->guid;
                    $model->add(array(
                        'user_id' => $user['id'],
                        'uface_guid' => $guid,
                    ));
                } else {
                    $error = $response->msg;
                }
            }
            if ($guid) {
                $imgStr = base64EncodeImage($_FILES["img"]["tmp_name"]);

                $response = ufaceApiAutoParams('post', array(
                    C('UFACE_APP_ID'), "/person/", $guid, "/face/valid"
                ), array(
                    'appId' => C('UFACE_APP_ID'),
                    'guid' => $guid,
                    'img'  => $imgStr,
                ));

                if ($response->result == 1) {
                    $result['code'] = 200;
                    $result['message'] = "上传成功";
                    $result['guid'] = $response->data->guid;
                    $result['personGuid'] = $response->data->personGuid;
                    $result['faceUrl'] = $response->data->faceUrl;
                    $this->response($result);
                    return;
                } else {
                    $error = $response->msg;
                }
            }
            $result['code'] = 0;
            $result['message'] = $error;
            $this->response($result);
        } else {
            $result['code'] = 0;
            $result['message'] = "对象未找到!";
            $this->response($result);
        }
    }

    /**
     * ajax
     * 删除照片
     */
    function deleteUserFace() {
        $user_id = I('user_id', '', 'int');
        $img_guid  = I("img_guid");
        $user = M("User")->where("id=$user_id")->find();
        if ($user) {
            $model = M("UfaceUser");
            $guid = $model->where("user_id=$user_id")->getField("uface_guid");
            if ($guid) {
                $response = ufaceApiAutoParams('delete', array(
                    C('UFACE_APP_ID'), "/person/", $guid, "/face/", $img_guid
                ), array(
                    'appId' => C('UFACE_APP_ID'),
                    'guid'  => $img_guid,
                    'persionGuid' => $guid,
                ));
                if ($response->result == 1) {
                    $result['code'] = 200;
                    $result['message'] = "操作成功";
                    $this->response($result);
                    return;
                } else {
                    $error = $response->msg;
                }
            } else {
                $error = "对象未找到！";
            }
            $result['code'] = 0;
            $result['message'] = $error;
            $this->response($result);
        } else {
            $result['code'] = 0;
            $result['message'] = "对象未找到!";
            $this->response($result);
        }
    }

    /**
     * ajax
     * 获取照片授权状态
     */
    function getFaceState() {
        $user_id = I('user_id', '', 'int');
        $img_guid  = I("img_guid");
        $user = M("User")->where("id=$user_id")->find();
        if ($user) {
            $model = M("UfaceUser");
            $guid = $model->where("user_id=$user_id")->getField("uface_guid");
            if ($guid) {
                $guids = explode(",", $img_guid);
                $state_list = array();
                foreach ($guids as $imgGuid) {
                    $response = ufaceApiAutoParams('get', array(
                        C('UFACE_APP_ID'), "/person/", $guid, "/face/", $imgGuid, "/state"
                    ), array(
                        'appId' => C('UFACE_APP_ID'),
                        'persionGuid' => $guid,
                        'guid'  => $imgGuid,
                    ));
                    if ($response->result == 1 && $response->data) {
                        $faceState = (array)$response->data[0];
                        $faceState['deviceName'] = $this->getDeviceNameByKey($faceState['deviceKey']);
                        $faceState['deviceStatus'] = $this->getDeviceStatusByKey($faceState['deviceKey']);
                        $state_list[] = $faceState;
                    }
                }
                $result['code'] = 200;
                $result['message'] = "操作成功";
                $result['data'] = $state_list;
                $this->response($result);
                return;
            } else {
                $result['code'] = 0;
                $result['message'] = "对象未找到！";
                $this->response($result);
            }
        } else {
            $result['code'] = 0;
            $result['message'] = "对象未找到!";
            $this->response($result);
        }
    }

    private function getDeviceNameByKey($deviceKey) {
        $deviceName =  D('UfaceDevice')->where(array("device_key"=>$deviceKey))->getField("name");
        return $deviceName;
    }

    private function getDeviceStatusByKey($deviceKey) {
        $response = ufaceApiAutoParams('get', array(
            C('UFACE_APP_ID'), "/device/", $deviceKey
        ), array(
            'appId' => C('UFACE_APP_ID'),
            'deviceKey' => $deviceKey,
        ));
        if ($response->result == 1) {
            return $response->data->status;
        }
        return -1;
    }

    private function defaultUfaceUserDevices($companyId, $guid) {// 管理员默认授权所有设备
        $response = ufaceApiAutoParams('get', array(
            C('UFACE_APP_ID'), "/person/", $guid, "/devices"
        ), array(
            'appId' => C('UFACE_APP_ID'),
            'guid' => $guid,
            'idcardNo' => "",
        ));
        $preDevices = array();
        if ($response->result == 1) {
            foreach($response->data as $device) {
                $preDevices[] = $device->deviceKey;
            }
        } else {
            $error = $response->msg;
        }

        $willDevices = D("UfaceDevice")->where("company_id = $companyId")->getField("device_key", true);
        $addDevices = array_diff($willDevices, $preDevices);
        if ($addDevices) {// 人员授权
            $response = ufaceApiAutoParams('post', array(
                C('UFACE_APP_ID'), "/person/", $guid, "/devices"
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'guid' => $guid,
                'deviceKeys' => implode(",", $addDevices),
            ));
            if ($response->result == 1) {

            } else {
                $error = $response->msg;
            }
        }

    }
}