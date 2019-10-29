<?php
namespace Home\Controller;

use Lib\ORG\Util\CheckError;
use Lib\ORG\Util\RBAC;

class DepartmentController extends CommonController {

    public function _initialize() {
        $this->assign('pagetitle',"部门管理");
        parent::_initialize();
    }

    /**
     * 岗位列表*
     *
     */
    public function index(){
        //dump($_REQUEST);exit;
        $company_id = I('company_id');
        $mode = I('mode');
        $pid = (int)I('pid')?(int)I('pid'):(int)I('search_pid');
        if (empty($pid)) {
            $pid = 0;
        }
        if (empty($company_id)) {
            $company_id = session('company_id');
        }
        $id = (int)I('id');
        $displaytype = 1; // 1默认，岗位列表及操作 2 用户列表及操作 3 权限操作
        switch($mode){
            case 'edit':
                $this->edit($id);
                break;
            case 'save':
                $this->save($company_id,$pid,$id);
                unset($id);
                break;
            case 'up':
                $this->doup($id);
                break;
            case 'down':
                $this->dodown($id);
                break;
            case 'delete':
                $this->dodelete($id);
                break;
            case 'userlist':
                $displaytype = 2;
                break;
            case 'saveusers':
                $this->saveusers($company_id, $id);
                $displaytype = 2;
                break;
            case 'deleteusers':
                $user_id = (int)I('user_id');
                $this->deleteusers($id,$user_id);
                $displaytype = 2;
                //删除用户 提交id(role_id) user_id 在role_user表中删除
                break;
            case 'nodelist':
                $displaytype = 3;
                break;
            case 'savenodes':
                $this->savenodes($id);
                $displaytype = 3;
                break;
            default:
                break;
        }

        //岗位下的用户列表取得
        if($displaytype == 2){
            $arrList = $this->findUserByDepartment($id);
            //子岗位展示列表取得
        }elseif($displaytype == 1){
            $arrList = $this->findCategoiesByParentCategoryId($company_id, $pid);
            //岗位权限展示列表取得
        }elseif($displaytype == 3){
            $arrList = $this->getDoorByDepartment($company_id, $id);
        }
        /*elseif($displaytype == 4){
            $arrList = $this->getDataAccessByRoteid($id);
        }*/

        //节点树列表取得
        $arrTree = $this->getCatTree($company_id, $pid);
        $company_name = M('Company')->where(array('id'=>$company_id))->getField('name');

        $this->assign('id', $id);
        $this->assign('company_name', $company_name);
        $this->assign('displaytype', $displaytype);
        $this->assign('pid', $pid);
        $this->assign('mode', $mode);
        $this->assign('arrList', $arrList);
        $this->assign('arrTree', $arrTree);
        $this->display();
    }

    /**
     * 岗位保存*
     *
     * @param int $pid 上级岗位ID
     * @param int $id  岗位ID
     */
    public function save($company_id, $pid=0,$id='') {
        $Department = D('Department');
        import('Lib.ORG.Util.CheckError');
        $objError = new CheckError();
        $objError->checkError();
        //验证岗位编码
        $objError->doFunc(array('岗位编码','code',45),array('EXIST_CHECK','MAX_LENGTH_CHECK'));
        //验证岗位名称
        $objError->doFunc(array('岗位名称','name',45),array('EXIST_CHECK','MAX_LENGTH_CHECK'));
        $error = $objError->arrErr;

        if(!$error['code']){
            //验证
            if($id){
                //验证编码唯一
                $map = array();
                $map['id'] = array('neq',$id);
                $map['code'] = array('eq',$_REQUEST['code']);
                $map['company_id'] = array('eq', $company_id);
                $codeCount = $Department->where($map)->count();
                if($codeCount > 0){
                    $error['code'] = "岗位编码已经存在!";
                }
            }else{
                //验证编码唯一
                $map = array();
                $map['code'] = array('eq',$_REQUEST['code']);
                $map['company_id'] = array('eq', $company_id);
                $codeCount = $Department->where($map)->count();
                if($codeCount > 0){
                    $error['code'] = "岗位编码已经存在!";
                }
            }
        }
        if(count($error) == 0){
            $data = $Department->create($_REQUEST);
            if(!$data){
                $error = $Department->getError();
                $this->assign('vo',$_REQUEST);
                $this->assign('error',$error);
            }else{
                $data['company_id'] = $company_id;
                if($id){
                    $result = $Department->save($data);
                }else{
                    if ($pid == 0) {
                        $map['pid'] = array('eq',$pid);
                        $rank = $Department->where($map)->max('sort')+1;
                    } else {
                        $map['id'] = array('eq',$pid);
                        $rank = $Department->where($map)->getField('sort');

                        $rankMap['sort'] = array('egt', $rank);
                        $Department->where($rankMap)->setInc('sort',1);
                    }

                    $map['id'] = array('eq',$pid);
                    $level = $Department->where($map)->field('level')->find();
                    $level = $level['level']+1;

                    $data['sort'] = $rank;
                    $data['level'] = $level;
                    $result = $Department->add($data);
                }
            }
        }else{
            $this->assign('vo',$_REQUEST);
            $this->assign('error',$error);
        }
    }

    /**
     * 岗位编辑*
     *
     * @param int $id  岗位ID
     */
    public function edit($id='') {
        $Department = D('Department');
        if (empty($id)) {
            $this->error('请选择要编辑的数据！');
            exit;
        }
        $vo = $Department->getById($id);
        if($vo){
            $this->assign('vo', $vo);
        }else{
            $this->error('没有找到要编辑的数据！');
            exit;;
        }
    }

    /**
     * 岗位删除*
     *
     * @param int $id  岗位ID
     */
    protected function dodelete($id='') {
        $Department = D('Department');
        $UserDepartment = D("UserDepartment");
        $DepartmentDoor = D("DepartmentDoor");

        $map['department_id'] = array('eq',$id);
        $count = $DepartmentDoor->where($map)->count('department_id');
        if ($count > 0) {
            $this->error("包含下级操作或模块，不能删除！");
            exit;
        }
        $arrRoleUser = $UserDepartment->where(array("department_id"=>$id))->select();
        if($arrRoleUser){
            $this->error("岗位下存在用户，不能删除！");
            exit;
        }

        load("@.sort");
        deleteRankRecord($Department, "id", $id, array(), true);
    }

    /**
     * 查询岗位权限*
     *
     * @param int $id 部门ID
     * @return array
     */
    protected function getDoorByDepartment($company_id, $id){
        if (empty($id)) {
            $this->error('请选择要分配权限的部门！');
            exit;
        }
        //取得当前用户
//        $Role = D('Department');
//        $vo = $Role->field('id,name')->getById($id);

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
        $Access = D('DepartmentDoor');
        $map = array();
        $map['department_id'] = array('eq',$id);
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

    /**
     * 保存岗位对应的权限*
     *
     * @param unknown_type $id
     */
    protected function savenodes($id=''){
        $node_id = I('node_id');
        $department_id = $id;

        $DepartmentDoor = M('DepartmentDoor');

        $map['department_id'] = array('eq',$department_id);
        $DepartmentDoor->where($map)->delete();

        foreach($node_id as $controller_id=>$doors){
            foreach ($doors as $door_id) {
                $data[] = array('department_id'=>$department_id, 'controller_id'=>$controller_id, 'door_id'=>intval($door_id));
            }
        }
        $DepartmentDoor->addall($data);

        // 针对部门，梳理卡片授权信息
        checkUserCardByDepartment($id);
    }

    /**
     * 查询子岗位数据*
     *
     * @param int $pid 父岗位ID
     * @return array   子岗位数据
     */
    protected function findCategoiesByParentCategoryId($company_id, $pid) {
        if (!$pid) {
            $pid = 0;
        }
        $map['company_id'] = $company_id;
        $map['pid'] = $pid;
        $Role = D('Department');
        return $Role->where($map)->order('sort DESC')->select();
    }

    /**
     * 查询部用户数据*
     *
     * @param int $id  部门ID
     * @return array   部门用户
     */
    protected function findUserByDepartment($id) {

        if (!$id) {//role_id
            $this->error('请选择要查询用户列表的岗位');
            exit;
        }
        //联合查询
        $model     =   D("User");
        $sql    =   "SELECT id, account, nickname, email, status FROM user AS user, user_department AS department WHERE department.department_id = '$id' AND department.user_id = user.id";

        load("@.Array");
        $rs =   $model->query($sql);
        foreach($rs as $key=>$v){
            $res = $model->getUserDepartmentById($v['id']);
            $arrDepartment = $res['UserDepartment'];
            $arrDepartmentName = array_col_values($arrDepartment,'name');
            if($arrDepartmentName){
                $rs[$key]['department'] = implode(",",$arrDepartmentName);
            }
        }
        return $rs;
    }

    /**
     * 节点树.
     *
     * @param integer $pid 父节点
     * @return array 节点树数组
     */
    protected function getCatTree($company_id, $pid=0) {
        $Department = D('Department');
        $arrRet = $Department->field('id,name,pid,level,sort,status')
            ->where(array('company_id'=> $company_id))
            ->order('sort DESC')->select();

        load("@.Array");
        $treeArr = array();
        array_to_tree2($arrRet,$treeArr,'id','pid');

        load("@.tree");
        $arrParentID = getParentsArray($Department, 'pid', 'id', $pid);

        foreach($treeArr as $key => $array) {
            foreach($arrParentID as $val) {
                if($array['pid'] == $val) {
                    $treeArr[$key]['display'] = 1;
                    break;
                }
            }
        }

        return $treeArr;
    }

    /**
     * 岗位排序上移*
     *
     * @param int $id 岗位ID
     */
    protected function doup($id=''){
        $Role = D('Department');
        $Role->startTrans();
        $up_id = $this->getUpRankID($Role, "pid", "id", $id);
        if ($up_id != "") {
            load("@.sort");
            $my_count = $this->countChilds($Role, "pid", "id", $id);
            $up_count = $this->countChilds($Role, "pid", "id", $up_id);

            if ($my_count > 0 && $up_count > 0) {
                $this->upRankChilds($Role, "pid", "id", $id, $up_count);
                $this->downRankChilds($Role, "pid", "id", $up_id, $my_count);
            }
        }
        $Role->commit();
    }

    /**
     * 岗位排序下移*
     *
     * @param int $id 岗位ID
     */
    protected function dodown($id='') {
        $Role = D('Department');
        $Role->startTrans();
        $down_id = $this->getDownRankID($Role, "pid", "id", $id);

        if ($down_id != "") {
            load("@.sort");
            $my_count = $this->countChilds($Role, "pid", "id", $id);
            $down_count = $this->countChilds($Role, "pid", "id", $down_id);
            if ($my_count > 0 && $down_count > 0) {
                $this->upRankChilds($Role, "pid", "id", $down_id, $my_count);
                $this->downRankChilds($Role, "pid", "id", $id, $down_count);
            }
        }
        $Role->commit();
    }

    /**
     * 移除岗位下的用户*
     *
     * @param int $id 岗位ID
     * @param int $user_id 用户ID
     */
    protected function deleteusers($id='',$user_id=''){
        if(empty($id)){
            $this->error("请选择要去除用户的岗位");
            exit;
        }
        if(empty($user_id)){
            $this->error("请选择从岗位去除的用户");
            exit;
        }
        $RoleUser = D('UserDepartment');
        $map['department_id'] = array('eq',$id);
        $map['user_id'] = array('eq',$user_id);
        $RoleUser->where($map)->delete();

        checkUserCardsByUser($user_id);
    }

    /**
     * 新增岗位下的用户*
     *
     * @param int $id 岗位ID
     */
    protected function saveusers($company_id, $id=''){
        if(empty($id)){
            $this->error('请选择增加用户的岗位');
            exit;
        }
        $error['users']='';
        $success['users']='';

        $users = I('users');
        $condition['department_id'] = $id;

        if(empty($users)){
            $error['users'] .= '要登录在岗位内的用户不能为空！';
        }else{
            $users = str_replace('，', ',',$users);
            $users = explode(',',$users);
            foreach($users as $user){
                $map['account|nickname|email'] = array('eq',$user);
                $map['company_id'] = $company_id;
                $User = D('User');
                $user_id = $User->where($map)->getField('id');
                if($user_id){
                    //查询是否已经增加过
                    $UserDepartment = D('UserDepartment');
                    $condition['user_id'] = $user_id;
                    $isE = $UserDepartment->where(array('user_id'=>$user_id))->getField('department_id');
                    if($isE == $id){
                        $error['users'] .= '"'.$user.'" 已经登录过，请不要重复登录！ ';
                    } else if ($isE) {
                        $data = $UserDepartment->create($condition);
                        $UserDepartment->where(array('user_id'=>$user_id))->save($data);
                        $success['users'] .= '"'.$user.'" 成功登录在岗位内！ ';
                    } else{
                        $data = $UserDepartment->create($condition);
                        $UserDepartment->add($data);
                        $success['users'] .= '"'.$user.'" 成功登录在岗位内！ ';
                    }
                }else{
                    $error['users'] .= '"'.$user.'" 不存在！ ';
                }
            }
        }
        $this->assign('success', $success);
        $this->assign('error', $error);
    }

    /**
     * 获取当前ID的同一个父级的下的紧邻位置之前的记录ID*
     *
     * @param object 数据表对象
     * @param string $pid_name 父ID名称
     * @param string $id_name  子ID名称
     * @param int $id       当前ID
     * @return mixed
     */
    protected function getUpRankID($tableObj, $pid_name, $id_name, $id) {
        // 父id取得。
        $map[$id_name] = array('eq',$id);
        $where = "$id_name = ?";
        $pid = $tableObj->where($map)->getField($pid_name);

        unset($map);
        $map[$pid_name] = array('eq',$pid);
        $arrRet = $tableObj->where($map)->field($id_name)->order('sort DESC')->select();

        $max = count($arrRet);
        $up_id = "";
        for($cnt = 0; $cnt < $max; $cnt++) {
            if($arrRet[$cnt][$id_name] == $id) {
                $up_id = $arrRet[($cnt - 1)][$id_name];
                break;
            }
        }
        return $up_id;
    }

    /**
     * 获取当前ID的同一个父级下的紧邻位置后的记录ID*
     *
     * @param object 数据表对象
     * @param string $pid_name 父ID名称
     * @param string $id_name  子ID名称
     * @param int $id       当前ID
     * @return int
     */
    protected function getDownRankID($tableObj, $pid_name, $id_name, $id) {
        // 父id取得。
        $map[$id_name] = array('eq',$id);
        $where = "$id_name = ?";
        $pid = $tableObj->where($map)->getField($pid_name);

        unset($map);
        $map[$pid_name] = array('eq',$pid);
        $arrRet = $tableObj->where($map)->field($id_name)->order('sort DESC')->select();

        $max = count($arrRet);
        $down_id = "";
        for($cnt = 0; $cnt < $max; $cnt++) {
            if($arrRet[$cnt][$id_name] == $id) {
                $down_id = $arrRet[($cnt + 1)][$id_name];
                break;
            }
        }
        return $down_id;
    }

    /**
     * 检索数据表对象当前ID数据下的所有子节点数据数量*
     *
     * @param object $tableObj  数据表对象
     * @param string $pid_name  父ID字段名
     * @param string $id_name   子ID字段名
     * @param int $id   当前记录ID
     * @return mixed
     */
    protected function countChilds($tableObj, $pid_name, $id_name, $id) {
        $arrRet = getChildrenArray($tableObj, $pid_name, $id_name, $id);
        return count($arrRet);
    }

    /**
     * 数据表对象当前ID数据下的子节点增加排序值*
     *
     * @param object $tableObj  数据表对象
     * @param string $pid_name  父ID字段名
     * @param string $id_name   子ID字段名
     * @param int $id  当前记录ID
     * @param $count   移动数量
     * @return bool
     */
    protected function upRankChilds($tableObj, $pid_name, $id_name, $id, $count) {
        $arrRet = getChildrenArray($tableObj, $pid_name, $id_name, $id);
        //$line = getCommaList($arrRet);
        $map[$id_name]  = array('in',$arrRet);
        $ret = $tableObj->where($map)->setInc('sort',$count);
        return $ret;
    }

    /**
     * 数据表对象当前ID数据下的子节点减少排序值*
     *
     * @param object $tableObj  数据表对象
     * @param string $pid_name  父ID字段名
     * @param string $id_name   子ID字段名
     * @param int $id   当前记录ID
     * @param $count   移动数量
     * @return bool
     */
    protected function downRankChilds($tableObj, $pid_name, $id_name, $id, $count) {
        $arrRet = getChildrenArray($tableObj, $pid_name, $id_name, $id);
        //$line = getCommaList($arrRet);
        $map[$id_name]  = array('in',$arrRet);
        $ret = $tableObj->where($map)->setDec('sort',$count);
        return $ret;
    }


}