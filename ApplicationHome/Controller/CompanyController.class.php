<?php
namespace Home\Controller;

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
     * 保存用户*
     *
     */
    function save(){
        $this->keepSearch();
        $id= $_POST['id'];
        $User = D("User");
        $_REQUEST['birthday'] =  $_REQUEST['birthday'] ? strtotime($_REQUEST['birthday']):'';
        import("@.ORG.Util.CheckError");
        $objError = new CheckError();
        $objError->checkError();
        $objError->doFunc(array("帐号","account",2,45),array("EXIST_CHECK","STRING_RANGE_CHECK"));
        if(!$id){
            $objError->doFunc(array("密码","password",6,30),array("EXIST_CHECK","STRING_RANGE_CHECK"));
        }
        $objError->doFunc(array("用户名称","nickname",45),array("EXIST_CHECK","MAX_LENGTH_CHECK","SPTAB_CHECK","HTML_TAG_CHECK"));

        //$objError->doFunc(array("邮箱","email"),array("EXIST_CHECK","EMAIL_CHECK"));
        $objError->doFunc(array("家庭住址","address",0,150),array("STRING_RANGE_CHECK"));
        $objError->doFunc(array("备注","remark",0,100),array("STRING_RANGE_CHECK"));
        if($_REQUEST['mobile']){
            $objError->doFunc(array("移动电话","mobile"),array("CN_MOBILE_CHECK"));
        }
        if($_POST['email']){
            $objError->doFunc(array("邮箱","email"),array("EMAIL_CHECK"));
            if(!$objError->arrErr['email']){
                if(!$User->uniqueEmail()){
                    $objError->arrErr['email'] = '邮箱已经存在！';
                }
            }
        }
        if(!$objError->arrErr['account']){
            if(!preg_match('/^[a-z]\w{2,}$/i',$_POST['account'])){
                $objError->arrErr['account'] = '账号必须是字母，且2位以上！';
            }else{
                if(!$User->uniqueAccount()){
                    $objError->arrErr['account'] = '账号已经存在！';
                }
            }
        }

        if(count($objError->arrErr) == 0){
            parent::save();
        }else{
            $error = $objError->arrErr;
            $this->assign('error',$error);
            $this->assign('vo',$_REQUEST);
            if($id){
                $this->display('edit');
            }else{
                $this->display('add');
            }
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
}