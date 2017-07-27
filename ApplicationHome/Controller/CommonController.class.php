<?php
namespace Home\Controller;
use Lib\ORG\Util\Page;
use Lib\ORG\Util\RBAC;
use Think\Controller;

class CommonController extends Controller {
    /**
     * 权限判断**
     *
     */
    public function _initialize() {
        import('Lib.ORG.Util.RBAC');
        //登陆检查
        if(!RBAC::checkLogin()){
            return;
        }
        // 用户权限检查
        if (C('USER_AUTH_ON') && RBAC::checkAccess()) {
            // 没有权限 抛出错误
            if (!RBAC::AccessDecision()) {
                // 定义权限错误页面
                if (C('RBAC_ERROR_PAGE')) {
                    redirect(U(C('RBAC_ERROR_PAGE')));
                    // 提示错误信息
                } else {
                    if (C('GUEST_AUTH_ON')) {
                        $this->assign('jumpUrl', PHP_FILE . C('USER_AUTH_GATEWAY'));
                        exit;
                    }
                    $this->error(L('_VALID_ACCESS_'));
                    exit;
                }
            }
        }
    }

    /**
     * 数据列表*
     *
     * @param string $name 模型名称
     */
    public function index($name="") {
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $map['status'] = array("neq","-1");
        $this->setMap($map,$search);

        $this->keepSearch();
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $this->_list($model, $map);
        }

        //保持分页记录
        $nowpage = (int)I('p')?(int)I('p'):(int)I('search_p');
        if($nowpage){
            $this->assign('nowpage', $nowpage);
        }
    }

    /**
     * 根据表单生成查询条件
     * 进行列表过滤
     * @param Model $model 数据对象
     * @param HashMap $map 过滤条件
     * @param string $sortBy 排序
     * @param boolean $asc 是否正序
     * @return void
     */
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
            $this->voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();

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

    /**
     * 新建*
     *
     */
    public function add() {
        $this->keepSearch();
        //是否存在新建模板 （项目中存在新建界面与编辑界面有较大差异的 也可以考虑用变量控制）
        $is_add_tpl_file = $this->isAddTplFile();
        if($is_add_tpl_file){
            $this->display();
        }else{
            $this->display('edit');
        }
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


    /**
     * 保存*
     *
     * @param string $name 数据对象
     * @param string $tpl  模板名称
     */
    public function save($name='',$tpl='edit') {
        $is_add_tpl_file = $this->isAddTplFile();
        $name = $name ? $name : $this->getActionName();
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
                $this->display($tpl);
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
                $this->error('数据未保存！',$this->getReturnUrl());
            }
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

    /**
     * 物理删除*
     *
     * @param string $name 数据对象
     */
    public function foreverdel($name='') {
        //物理删除指定记录
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = I($pk);
            if (isset($id)) {
                $condition = array($pk => array('in', explode(',', $id)));
                if (false !== $model->where($condition)->delete()) {
                    $this->success('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
        //$this->forward();
    }


    /**
     * 取得操作成功后要返回的URL地址
     * 默认返回当前模块的默认操作
     * 可以在action控制器中重载
     * @param array  $search 搜索条件
     */
    public function getReturnUrl($search=array(),$action="") {
        $search = $this->keepSearch();
        foreach($search as $k=>$v){
            $params.= $k.'/'.$v.'/';
        }
        if($_REQUEST['p']){
            $params.= 'p/'.$_REQUEST['p'];
        }
        if(!$action){
            return __CONTROLLER__  . '/' . C('DEFAULT_ACTION') . '/'.$params;
        } else {
            return __CONTROLLER__  . '/' . $action . '/'.$params;
        }

        //return __URL__ . '?' . C('VAR_MODULE') . '=' . MODULE_NAME . '&' . C('VAR_ACTION') . '=' . C('DEFAULT_ACTION');
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

    /**
     * 默认还原操作
     *
     * @param string 模型对象
     */
    public function recycle($name='') {
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => array('in', $id));
        if (false !== $model->recycle($condition)) {
            $this->success('状态还原成功！',$this->getReturnUrl());
        } else {
            $this->error('状态还原失败！',$this->getReturnUrl());
        }
    }

    /**
     * 判断add模板是否存在
     *
     * @return bool
     */
    protected function isAddTplFile(){
        if($_REQUEST ['t']){
            $tpl_dir = str_replace(C('DEFAULT_THEME'), $_REQUEST ['t'], TMPL_PATH.C('DEFAULT_THEME').'/');
        }else{
            $tpl_dir = TMPL_PATH.C('DEFAULT_THEME').'/';
        }
//        $add_tpl_file = $tpl_dir.MODULE_NAME.'/add.html';
        $add_tpl_file = T(CONTROLLER_NAME.'/add');//echo $add_tpl_file;exit;

        return is_file($add_tpl_file);
    }


    /**
     * 默认依据url传参，生成搜索条件*
     *
     * @param array $map 查询数组
     * @param array $search 搜索数组
     */
    protected function setMap(&$map,&$search){
        foreach ($_REQUEST as $key => $val) {
            if($val == "") {
                continue;
            }
            if (ereg("^search_", $key)) {
                $field = str_replace('search_','',$key);
                $map[$field] = array('eq',$val);
                $search[$key] = $val;
            }
            //break;
        }
    }

    /**
     * 默认保持搜索条件
     *
     * 对应的模板页面要 定义对应的<input type="hidden">
     *
     * @return array
     */
    protected function keepSearch(){
        foreach ($_REQUEST as $key => $val) {
            if($val == "") {
                continue;
            }
            if (ereg("^search_", $key)) {
                $search[$key] = $val;
            }
            if($key=='m_search'){
                $mSearch = explode('-',$val);
                foreach($mSearch as $key=>$value){
                    $s = explode(':',$value);
                    $search[$s[0]][] = $s[1];
                }
            }

            //m_search/search_sex:1/
        }
        foreach($search as $k=>$v){
            if(is_array($v)){
                $search[$k] = implode('-',$v);
            }
        }
        $this->assign('search',$search);
        return  $search;
    }

    protected function getActionName() {
        return CONTROLLER_NAME;
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @return void
     */
    protected function response($data) {
        header('HTTP/1.1 200 OK');
        header('Status:200 OK');
        exit(json_encode($data));
    }

}