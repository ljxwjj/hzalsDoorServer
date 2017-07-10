<?php
namespace Home\Controller;

class AuthNodeController extends CommonController {

    public function _initialize() {
        $this->assign('pagetitle',"权限管理");
        parent::_initialize();
    }
    /**
     * 权限列表*
     *
     */
    public function index(){
        //dump($_REQUEST);
        $mode = I('mode');
        $pid = (int)I('pid')?(int)I('pid'):(int)I('search_pid');
        if (empty($pid)) {
            $pid = 0;
        }
        $id = (int)I('id');
        if(empty($_SESSION[C('ADMIN_AUTH_KEY')]) && !empty($mode) && ($mode != 'save' || ($mode == 'save' && !$id))){
            import('Lib.ORG.Util.RBAC');
            $accessList = RBAC::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
            $module = defined('P_MODULE_NAME')?  P_MODULE_NAME   :   MODULE_NAME;
            if(!isset($accessList[strtoupper(APP_NAME)][strtoupper($module)][strtoupper($mode)])) {
                redirect(U(C('RBAC_ERROR_PAGE')));
                exit;
            }
        }
        switch($mode){
            case 'edit':
                $this->edit();
                break;
            case 'save':
                $this->save();
                break;
            case 'up':
                $this->doup();
                break;
            case 'down':
                $this->dodown();
                break;
            case 'delete':
                $this->dodelete();
                break;
            default:
                break;
        }

        //节点展示列表取得
        $arrList = $this->findCategoiesByParentCategoryId($pid);
        //节点树列表取得
        $arrTree = $this->getCatTree($pid);

        $this->assign('id', $id);
        $this->assign('pid', $pid);
        $this->assign('mode', $mode);
        $this->assign('arrList', $arrList);
        $this->assign('arrTree', $arrTree);
        $this->display();
    }


    /**
     * 保存权限*
     *
     */
    public function save() {
        $pid = (int)I('pid')?(int)I('pid'):(int)I('search_pid');

        $Node = D('AuthNode');
        $id = (int)I($Node->getPk());
        import('Lib.ORG.Util.CheckError');
        $objError = new CheckError();
        $objError->checkError();
        //验证岗位编码
        $objError->doFunc(array('权限名','name',45),array('EXIST_CHECK','MAX_LENGTH_CHECK'));
        //验证岗位名称
        $objError->doFunc(array('权限中文名','title',45),array('EXIST_CHECK','MAX_LENGTH_CHECK'));
        $error = $objError->arrErr;

        //同一pid下的权限名和权限中文名不能重复
        if(!$error['name']){
            if(!$id){
                $count = $Node->where(array("name"=>array("eq",$_REQUEST['name']),'pid'=>array("eq",$pid)))->count();
            }else{
                $count = $Node->where(array("name"=>array("eq",$_REQUEST['name']),"id"=>array("neq",$id),'pid'=>array("eq",$pid)))->count();
            }
            if($count){
                $error['name'] = "权限名存在";
            }
        }
        if(!$error['title']){
            if(!$id){
                $count = $Node->where(array("title"=>array("eq",$_REQUEST['title']),'pid'=>array("eq",$pid)))->count();
            }else{
                $count = $Node->where(array("title"=>array("eq",$_REQUEST['title']),"id"=>array("neq",$id),'pid'=>array("eq",$pid)))->count();
            }
            if($count){
                $error['title'] = "权限中文名存在";
            }
        }

        if(count($error) == 0){
            $data = $Node->create($_REQUEST);
            if(!$data){
                $error = $Node->getError();
                $this->assign('vo',$_REQUEST);
                $this->assign('error',$error);
            }else{
                if($id){
                    $result = $Node->save($data);
                }else{

                    if ($pid == 0) {
                        $map['pid'] = array('eq',$pid);
                        $rank = $Node->where($map)->max('sort')+1;
                    } else {
                        $map['id'] = array('eq',$pid);
                        $rank = $Node->where($map)->getField('sort');

                        $rankMap['sort'] = array('egt', $rank);
                        $Node->where($rankMap)->setInc('sort',1);
                    }

                    $map['id'] = array('eq',$pid);
                    $level = $Node->where($map)->field('level')->find();
                    $level = $level['level']+1;

                    $data['sort'] = $rank;
                    $data['level'] = $level;
                    $result = $Node->add($data);
                }
            }
        }else{
            $this->assign('vo',$_REQUEST);
            $this->assign('error',$error);
        }
    }

    /**
     * 权限编辑*
     *
     */
    public function edit() {
        $Node = D('AuthNode');
        $id = (int)I($Node->getPk());
        if (empty($id)) {
            $this->error('请选择要编辑的数据！');
            exit;
        }
        $vo = $Node->getById($id);
        if($vo){
            $this->assign('vo', $vo);
        }else{
            $this->error('没有找到要编辑的数据！');
            exit;
        }
    }

    /**
     * 权限删除*
     *
     */
    function dodelete() {
        $id = (int)I('id');
        $Node = D('AuthNode');

        $map['pid'] = array('eq',$id);
        $count = $Node->where($map)->count('id');
        if ($count > 0) {
            $this->error("包含下级操作或模块，不能删除！");
            exit;
        }
        load("@.sort");
        deleteRankRecord($Node, "id", $id, array(), true);
    }

    /**
     * 查询子权限数据*
     *
     * @param int $pid 父权限节点ID
     * @return array   子权限节点
     */
    protected function findCategoiesByParentCategoryId($pid) {
        if (!$pid) {
            $pid = 0;
        }
        $map['pid'] = array('eq',$pid);
        $Node = D('AuthNode');
        return $Node->where($map)->order('sort DESC')->select();
    }

    /**
     * 节点树.
     *
     * @param integer $pid 父节点
     * @return array 节点树数组
     */
    protected function getCatTree($pid) {
        $Node = D('AuthNode');
        $arrRet = $Node->field('id,name,title,pid,level,sort,status')->order('sort DESC')->select();

        load("@.Array");
        $treeArr = array();
        array_to_tree2($arrRet,$treeArr,'id','pid');

        load("@.tree");
        $arrParentID = getParentsArray($Node, 'pid', 'id', $pid);

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
     * 权限上移*
     *
     * @param int $id
     */
    protected function doup(){
        $id = (int)I('id');

        $Node = D('AuthNode');
        $Node->startTrans();
        $up_id = $this->getUpRankID($Node, "pid", "id", $id);
        if ($up_id != "") {
            load("@.sort");
            $my_count = $this->countChilds($Node, "pid", "id", $id);
            $up_count = $this->countChilds($Node, "pid", "id", $up_id);

            if ($my_count > 0 && $up_count > 0) {
                $this->upRankChilds($Node, "pid", "id", $id, $up_count);
                $this->downRankChilds($Node, "pid", "id", $up_id, $my_count);
            }
        }
        $Node->commit();
    }

    /**
     * 权限下移*
     *
     * @param int $id
     */
    protected function dodown() {
        $id = (int)I('id');

        $Node = D('AuthNode');
        $Node->startTrans();
        $down_id = $this->getDownRankID($Node, "pid", "id", $id);

        if ($down_id != "") {
            load("@.sort");
            $my_count = $this->countChilds($Node, "pid", "id", $id);
            $down_count = $this->countChilds($Node, "pid", "id", $down_id);
            if ($my_count > 0 && $down_count > 0) {
                $this->upRankChilds($Node, "pid", "id", $down_id, $my_count);
                $this->downRankChilds($Node, "pid", "id", $id, $down_count);
            }
        }
        $Node->commit();
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
     * @return mixed
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
     * @return int
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