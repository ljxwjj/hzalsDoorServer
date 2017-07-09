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
     * 检查帐号*
     *
     */
    public function checkAccount() {
        if(!preg_match('/^[a-z]\w{4,}$/i',$_POST['account'])) {
            $this->error( '用户名必须是字母，且5位以上！');
        }
        $Admin = M("User");
        // 检测用户名是否冲突
        $name  =  $_REQUEST['account'];
        $result  =  $Admin->getByAccount($name);
        if($result) {
            $this->error('该用户名已经存在！');
        }else {
            $this->success('该用户名可以使用！');
        }
    }

    /**
     * 重置密码*
     *
     */
    public function resetPwd() {
        $id = I("id",0,"htmlspecialchars");
        $password = $_POST['password'];
        if(''== trim($password)) {
            $this->error('密码不能为空！');
        }
        $Admin = M('User');
        $Admin->password	=	md5($password);
        $Admin->id			=	$id;
        $result	=	$Admin->save();
        if(false !== $result) {
            $this->success("密码修改为$password");
        }else {
            $this->error('重置密码失败！');
        }
    }


    /**
     * 重载彻底删除，删除关联数据*
     *
     */
    function del(){
        $id = I("id",0,"htmlspecialchars");
        $Admin = M("User");
        $Mproject = D("Project");
        $Mproperty = D("Property");
        $Mlog = D("Log");
        $MCustomer = D("Customer");

//		$mapProject['owner_id'] = $id;
//		$mapProject['create_user_id'] = $id;
//		$mapProject['_logic'] = "or";
//		$whereProject['_complex'] = $mapProject;
//		$whereProject['status'] = array("neq",-1);
//		$projectCount =  $Mproject->where($whereProject)->count();
//        if ($projectCount > 0) {
//             $this->error("存在用户建立或拥有的项目，不能删除！");
//             exit;
//        }
//
//        $mapProperty['status'] = array("neq",-1);
//        $mapProperty['create_user_id'] = $id;
//        $propertyCount =  $Mproperty->where($whereProject)->count();
//        if ($propertyCount > 0) {
//             $this->error("存在用户建立的物业，不能删除！");
//             exit;
//        }
//
//        $mapLog['status'] = array("neq",-1);
//        $mapLog['create_user_id'] = $id;
//        $mapLog['owner_id'] = $id;
//        $logCount =  $Mlog->where($whereProject)->count();
//        if ($propertyCount > 0) {
//             $this->error("存在用户建立或拥有的日志，不能删除！");
//             exit;
//        }
//
//        $mapLog['status'] = array("neq",-1);
//        $mapLog['create_user_id'] = $id;
//        $logCount =  $Mlog->where($whereProject)->count();
//        if ($propertyCount > 0) {
//             $this->error("存在用户建立的日志，不能删除！");
//             exit;
//        }
//
//        /**
//         * 集团
//         */
//		$mapCustomerGroup['create_user_id'] = $id;
//		$mapCustomerGroup['status'] = array("neq",-1);
//        $customerGroupCount =  $MCustomer->where($mapCustomerGroup)->count();
//        if ($customerGroupCount > 0) {
//             $this->error("存在用户创建的集团，不能删除！");
//             exit;
//        }
//
//        //客户，
//        $mapCustomer['customer_manage_id'] = $id;
//		$mapCustomer['create_user_id'] = $id;
//		$mapCustomer['_logic'] = "or";
//		$whereCustomer['_complex'] = $mapCustomer;
//		$whereCustomer['status'] = array("neq",-1);
//        $customerCount =  $MCustomer->where($whereCustomer)->count();
//        if ($customerCount > 0) {
//             $this->error("存在用户客户，不能删除！");
//             exit;
//        }
//
//        //联系人
//        $mapCustomerUser['create_user_id'] = $id;
//		$mapCustomerUser['status'] = array("neq",-1);
//        $customerUserCount =  $MCustomer->where($mapCustomerUser)->count();
//        if ($customerUserCount > 0) {
//             $this->error("存在用户创建的联系人，不能删除！");
//             exit;
//        }

        $condition['id']  = array('eq',$id);
        $result = $Admin->where($condition)->setField('status', -1);
        if($result){
            $RoleAdmin = M("RoleUser");
            $condition_roleuser['user_id']  = array('eq',$id);
            $RoleAdmin->where($condition_roleuser)->delete();
            $this->success('数据删除！',$this->getReturnUrl());
        }else{
            $this->error('数据未删除！',$this->getReturnUrl());
        }
    }


    /**
     * 重载彻底删除，删除关联数据*
     *
     */
    function foreverdel(){
        $id = I("id",0,"htmlspecialchars");
        $Admin = M("User");
        $condition['id']  = array('eq',$id);
        $result = $Admin->where($condition)->delete();
        if($result){
            $RoleAdmin = M("RoleUser");
            $condition_roleuser['user_id']  = array('eq',$id);
            $RoleAdmin->where($condition_roleuser)->delete();
            $this->success('数据删除！',$this->getReturnUrl());
        }else{
            $this->error('数据未删除！',$this->getReturnUrl());
        }
    }

    /**
     * 用户分配岗位页面*
     *
     */
    function assignrole(){
        $this->keepSearch();
        $id = (int)I('id');
        if (empty($id)) {
            $this->error('请选择要分配岗位的用户！');
            exit;
        }
        //取得当前用户
        $User = D('User');
        $vo = $User->field('id,account,nickname,email')->getById($id);

        //取得所有岗位
        $Role = D('Role');
        $arrRet = $Role->field('id,name,pid,level,sort,status')->order('sort DESC')->select();

        load("@.Array");
        $arrTree = array();
        array_to_tree2($arrRet,$arrTree,'id','pid');

        //取得已分配的岗位
        $RoleUser = D('RoleUser');
        $map['user_id'] = array('eq',$id);
        $arrRet = $RoleUser->where($map)->field('role_id')->select();

        //匹配分配岗位
        foreach($arrTree as $k=>$v){
            foreach($arrRet as $value){
                if($v['id'] == $value['role_id']){
                    $arrTree[$k]['checked'] = 1;
                }
            }
        }

        $this->assign('arrTree', $arrTree);
        $this->assign('vo', $vo);

        $this->display();
    }

    /**
     * 保存用户分配岗位*
     *
     */
    function saveroles(){
        $role_id = I('role_id');
        $user_id = I('user_id');

        if(empty($user_id)){
            $this->error('没有选择分配岗位的用户',$this->getReturnUrl());
        }

        $RoleUser = D('RoleUser');

        $map['user_id'] = array('eq',$user_id);
        $RoleUser->where($map)->delete();

        foreach($role_id as $k=>$v){
            $data[$k]['role_id'] = $v;
            $data[$k]['user_id'] = $user_id;
        }
        $RoleUser->addall($data);
        $this->success('数据已保存！',$this->getReturnUrl());
    }

    /**
     * 用户分配部门页面*
     *
     */
    function assigndepartment(){
        $this->keepSearch();
        $id = (int)I('id');
        if (empty($id)) {
            $this->error('请选择要分配部门的用户！');
            exit;
        }
        //取得当前用户
        $User = D('User');
        $vo = $User->field('id,account,nickname,email')->getById($id);

        //取得所有部门
        $Department = D('Department');
        $arrRet = $Department->field('id,name,pid,level,sort,status')->order('sort DESC')->select();

        load("@.Array");
        $arrTree = array();
        array_to_tree2($arrRet,$arrTree,'id','pid');

        //取得已分配的部门
        $DepartmentUser = D('DepartmentUser');
        $map['user_id'] = array('eq',$id);
        $arrRet = $DepartmentUser->where($map)->field('department_id')->select();

        //匹配分配部门
        foreach($arrTree as $k=>$v){
            foreach($arrRet as $value){
                if($v['id'] == $value['department_id']){
                    $arrTree[$k]['checked'] = 1;
                }
            }
        }

        $this->assign('arrTree', $arrTree);
        $this->assign('vo', $vo);

        $this->display();
    }

    /**
     * 保存用户分配的部门*
     *
     */
    function savedepartments(){
        $department_id = I('department_id');
        $user_id = I('user_id');

        if(empty($user_id)){
            $this->error('没有选择分配部门的用户',$this->getReturnUrl());
        }

        $DepartmentUser = D('DepartmentUser');

        $map['user_id'] = array('eq',$user_id);
        $DepartmentUser->where($map)->delete();

        foreach($department_id as $k=>$v){
            $data[$k]['department_id'] = $v;
            $data[$k]['user_id'] = $user_id;
        }
        $DepartmentUser->addall($data);
        $this->success('数据已保存！',$this->getReturnUrl());
    }

    /**
     * ajax获取用户信息*
     *
     */
    function getAjaxUserInfo(){
        $id = (int)$_REQUEST['user_id'];
        //取得当前用户
        $User = D('User');
        $vo = $User->getById($id);

        //取得所有岗位
        $Role = D('Role');
        $arrRet = $Role->field('id,name,pid,level,sort,status')->order('sort DESC')->select();

        $RoleUser = D('RoleUser');
        $map['user_id'] = array('eq',$id);
        $arrRet = $RoleUser->where($map)->field('role_id')->select();
        $this->assign('vo',$vo);
        $this->display("view");
    }

    /**
     * 查看用户信息*
     *
     */
    function view(){
        $id = I('id');
        $this->keepSearch();
        $User = D("User");
        if(!$id){
            $this->error("没有可以查看的选项");
        }
        $vo = $User->getById($id);
        $this->assign("vo",$vo);
        $this->display();
    }

    /**
     * 根据部门ajax查询用户 *
     *
     */
    function getAjaxUserByDepartment() {
        $departmentIds = $_REQUEST['department'];
        $userName = $_REQUEST['user_name'];
        if (!is_array($departmentIds)) {
            $departmentIds = array($departmentIds);
        }
        //dump($departmentIds);exit;
        $User = D('User');
        $UserTb = $User->getTableName();
        $DepartmentUser = D('DepartmentUser');
        $DepartmentUserTb = $DepartmentUser->getTableName();
        $Department = D('Department');
        $DepartmentTb = $Department->getTableName();
        if(isset($_REQUEST['department'])){
            $map["{$DepartmentUserTb}.department_id"] = array("in", $departmentIds);
        }
        $map["{$UserTb}.status"] = 1;

        if ($userName) {
            $map["{$UserTb}.nickname"] = array("like", "%$userName%");
        }
        $vo = $User->join("{$DepartmentUserTb} ON {$DepartmentUserTb}.user_id = {$UserTb}.id")
            ->where($map)
            ->field("distinct {$UserTb}.*")
            ->select();
        load("@.Array");
        foreach ($vo as $key=>$user) {
            $departments = $DepartmentUser->join("{$DepartmentTb} ON {$DepartmentTb}.id = {$DepartmentUserTb}.department_id")
                ->where(array("{$DepartmentUserTb}.user_id"=>array("eq", $user["id"])))
                ->select();
            $arrDepartment = array_col_values($departments, "name");
            $vo[$key]["department"] = implode(",", $arrDepartment);
        }
        //dump($DepartmentUser->getLastSql());
        $this->assign("vo",$vo);
        $this->display("department_user");
    }

    /**
     * 用户导入、导出的字段和标题*
     *
     * @return array
     */
    protected function getUserFieldAndTitle(){
        return array(
            array('field'=>'account','title'=>'登录帐号','is_require'=>1),
            array('field'=>'nickname','title'=>'姓名','is_require'=>1),
            array('field'=>'sex','title'=>'性别','is_require'=>1),
            array('field'=>'last_login_time', 'title'=>'最后登录时间','is_require'=>0),
            array('field'=>'login_count', 'title'=>'登录次数','is_require'=>0),
            array('field'=>'email', 'title'=>'邮箱','is_require'=>1),
            array('field'=>'mobile', 'title'=>'手机','is_require'=>0),
            array('field'=>'birthday', 'title'=>'出身日期','is_require'=>0),
            array('field'=>'status', 'title'=>'状态','is_require'=>0),
            array('field'=>'address', 'title'=>'地址','is_require'=>0),
            array('field'=>'departments', 'title'=>'所属部门','is_require'=>1),
            array('field'=>'roles', 'title'=>'岗位','is_require'=>0),
        );
    }


    /**
     * 用户数据导出页面*
     *
     */
    public function export(){
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $map['status'] = 1;
        $this->setMap($map,$search);
        $model = D('User');
        $arrList = $model->where($map)->select();
        $this->exportExcel($arrList,1);
    }

    /**
     * 将用户数据导出到EXCEL*
     *
     * @param array $arr 导出的数据
     * @param int $relation 是否是关联导出  1关联导出   0非关联导出
     */
    public function exportExcel($arr){
        header("Content-type:text/html;charset=utf8");
        import("@.ORG.PHPExcel.Classes.PHPExcel");
        $objPHPExcel = new PHPExcel();

        //设置excel标题列
        $arrExcelKey = getExcelColumnKey();

        //获取导出的字段标题数组
        $arrCustomerExportFieldAndTitle = $this->getUserFieldAndTitle();

        //设置活动单元表格的下标
        $sheetIndex = $objPHPExcel->getSheetCount()-1;

        //导出集团数据
        $this->exportUser($objPHPExcel,$arr,$sheetIndex);

        /*--------------下面是设置其他信息------------------*/
        $objPHPExcel->getActiveSheet()->setTitle('用户管理');//设置sheet的名称

        //导出位置
        $outputFileName = '用户管理.xlsx';
        $xlsWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.getExcelConverFileName($outputFileName).'"');
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $xlsWriter->save( "php://output" );
    }


    /**
     * 设置excel单元格对应的用户数据字段*
     *
     * @param object $objPHPExcel  excel类实例
     * @param array $arr    导出的数据
     * @param int $relation 是否关联   1关联  0非关联
     */
    public function exportUser($objPHPExcel,$arrUserData,$sheetIndex=0){
        $Muser = D('User');
        $Mdepartment = D('Department');
        $departmentTb = $Mdepartment->getTableName();
        $MdepartmentUser = D('DepartmentUser');
        $departmentUserTb = $MdepartmentUser->getTableName();
        $Mrole = D('Role');
        $roleTb = $Mrole->getTableName();
        $MroleUser = D('RoleUser');
        $roleUserTb = $MroleUser->getTableName();
        load("@.Array");

        //获取excel标题列数组
        $arrExcelKey = getExcelColumnKey();

        //获取导出的字段和标题数组
        $arrExportFieldAndTitle = $this->getUserFieldAndTitle();
        $arrExportTitles = array_col_values($arrExportFieldAndTitle,'title');
        $arrExportFields = array_col_values($arrExportFieldAndTitle,'field');

        //设置EXCEL对应列对应的客户标题
        foreach($arrExportTitles as $key=>$exportTitle){
            $objPHPExcel->setActiveSheetIndex($sheetIndex)->setCellValue($arrExcelKey[$key]."1",$exportTitle);
        }

        //循环每条数据
        foreach($arrUserData as $k=>$userData){
            //相关字段重新赋值
            if( $userData['birthday'] ){
                $userData['birthday'] = date('Y/m/d',$userData['birthday']);
            } else {
                $userData['birthday'] = "";
            }

            if( $userData['last_login_time'] ){
                $userData['last_login_time'] = date('Y/m/d H:i:s',$userData['last_login_time']);
            } else {
                $userData['last_login_time'] = "";
            }

            $departments = $Mdepartment->join("{$departmentUserTb} ON {$departmentUserTb}.department_id = {$departmentTb}.id")
                ->where(array("{$departmentUserTb}.user_id"=>array("eq", $userData["id"])))
                ->select();
            if ($departments) {
                $departments = array_col_values($departments, "name");
                $userData["departments"] = implode(",", $departments);
            } else {
                $userData["departments"] = "";
            }

            $roles = $Mrole->join("{$roleUserTb} ON {$roleUserTb}.role_id = {$roleTb}.id")
                ->where(array("{$roleUserTb}.user_id"=>array("eq", $userData["id"])))
                ->select();
            if ($roles) {
                $roles = array_col_values($roles, "name");
                $userData["roles"] = implode(",", $roles);
            } else {
                $userData["roles"] = "";
            }

            switch ($userData["sex"]) {
                case "0":
                    $userData["sex"] = "女";
                    break;
                case "1":
                    $userData["sex"] = "男";
                    break;
                default:
                    $userData["sex"] = "";
            }

            switch ($userData["status"]) {
                case "0":
                    $userData["status"] = "禁用";
                    break;
                case "1":
                    $userData["status"] = "启用";
                    break;
                case "-1":
                    $userData["status"] = "注销";
                default:
                    $userData["status"] = "";
            }


            //循环数据每个字段
            foreach($arrExportFields as $fieldKey=>$field){
                //设置EXCEL的列对应的单元格的值
                $objPHPExcel->setActiveSheetIndex($sheetIndex)->setCellValue($arrExcelKey[$fieldKey].($k+2), $userData[$field]);
            };

        }
    }


    /**
     * 设置用户数据导入/导出的模板*
     *
     */
    public function example(){
        import('@.ORG.PHPExcel.Classes.PHPExcel');
        header("content-Type:text/html;charset=utf8");
        $excelFileName = '用户数据示例';
        $objPHPExcel = new PHPExcel();
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        load("@.Array");

        $arrExportUser = $this->getUserFieldAndTitle();

        //获取excelsheet的标题
        $arrExportUserTitle = array_col_values($arrExportUser,'title');
        $arrExportUserTitleIsRequire = array_col_values($arrExportUser,'is_require');


        $arrUserTitle = array(
            $arrExportUserTitle
        );

        $arrUserIsRequire = array(
            $arrExportUserTitleIsRequire
        );

        //活动sheet名
        $arrSheetTitle = array(
            '用户管理',
        );

        $arrExcelColumnKey = getExcelColumnKey();
        $sheetCount = $objPHPExcel->getSheetCount();

        foreach($arrUserTitle as $titleKey=>$titleValue){
            if($titleKey >= $sheetCount){
                $workSheet = new PHPExcel_Worksheet($objPHPExcel); //创建一个工作表
                $objPHPExcel->addSheet($workSheet); //插入工作表
            }
            $objPHPExcel->setActiveSheetIndex($titleKey);
            $objPHPExcel->getActiveSheet()->setTitle($arrSheetTitle[$titleKey]);
            $sheetColumnTitleIsRequire = array();
            $sheetColumnTitleIsRequire = $arrUserIsRequire[$titleKey];
            foreach($titleValue as $fieldKey=>$fieldValue){
                $objPHPExcel->getActiveSheet()->setCellValue($arrExcelColumnKey[$fieldKey]."1",$fieldValue);
                if($sheetColumnTitleIsRequire){
                    if($sheetColumnTitleIsRequire[$fieldKey] == 1){
                        $objPHPExcel->getActiveSheet()->getStyle($arrExcelColumnKey[$fieldKey]."1")->getFont()->getColor()->setRGB('FF0000');
                    }
                }
            }
        }

        $columns = PHPExcel_Cell::columnIndexFromString($objPHPExcel->getActiveSheet()->getHighestColumn());
        for($i=0;$i<$columns;$i++){
            $column_name = chr(ord('A')+$i);
            $objPHPExcel->getActiveSheet()->getStyle($column_name.'1')->getAlignment()->setVertical(
                PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直方向上居中
            $objPHPExcel->getActiveSheet()->getStyle($column_name.'1')->getAlignment()->setHorizontal(
                PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(32);

        /* 生成到浏览器，提供下载 */
        ob_end_clean();  //清空缓存
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.getExcelConverFileName($excelFileName).'.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

    /**
     * 用户数据导入页面*
     *
     */
    public function import(){
        $this->keepSearch();
        $this->display();
    }

    /**
     * 用户数据导入*
     *
     */
    public function importExcel(){
        header("Content-type:text/html;charset=utf8");
        $this->keepSearch();

        import("@.ORG.Util.ExcelToArrary");
        import("@.ORG.Util.CheckError");
        $error = array();
        $objError = new CheckError();
        $objError->doFunc(array("上传文件",'myfile',array('xlsx','xls')),array('FILE_EXIST_CHECK','FILE_EXT_CHECK'));
        $error = $objError->arrErr;


        if(count($error) == 0){
            $tmp_file = $_FILES ['myfile'] ['tmp_name'];
            $file_types = explode ( ".", $_FILES ['myfile'] ['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];

            import('ORG.Net.UploadFile');
            $objUploadFile = new UploadFile();
            $savePath = C("UPLOAD_DIR_EXCEL");
            $objUploadFile->__set('savePath',$savePath);
            $uploadfile = $objUploadFile->upload();
            if(!$uploadfile){
                $uploadError = $objUploadFile->getErrorMsg();
                echo "error:".$uploadError;
            }else{
                $fileInfo = $objUploadFile->getUploadFileInfo();

                $ExcelToArrary = new ExcelToArrary();//实例化
                $arrExcelData = $ExcelToArrary->read($savePath.$fileInfo[0]['savename'],"UTF-8",$file_type);//传参,判断office2007还是office2003

                $arrExcelDataCount = count($arrExcelData);
                $arrExcelUserData = array_values($arrExcelData[0]);

                //覆盖导入
                $isCover = 1;
                //导入基本数据
                $importUserResult = $this->importUser($arrExcelUserData,$isCover);
                //dump($importUserResult);
                if(!$importUserResult['error'] && !$importUserUserResult['error']){
                    $this->success('导入成功',$this->getReturnUrl());
                }else{
                    if($importUserResult['error']){
                        $error['import_error'][] = array("title"=>"用户基础数据",
                            "errorinfo"=>implode("<br/>",$importUserResult['error']),
                            "errorcount"=>count($importUserResult['error']),
                            'successcount' => $importUserResult['success']
                        );
                    }
                }
                //dump($error);
            }
        }

        if(count($error) != 0){
            $this->assign("error",$error);
            $this->display('import');
        }
    }

    /**
     * 用户数据导入*
     *
     * @param array $arr 读取的sheet表数据
     * @param int $init  是否关联  1关联 0非关联
     * @param int $is_cover 导入方式  1追加  0清空
     * @return array  $returnResult 包括成功数量和错误信息
     */
    public function importUser($arrData,$isCover){
        //sheet1表导入
        $Mdepartment     = D('Department');
        $Muser           = D("User");
        $MdepartmentUser = D("DepartmentUser");
        $Mrole           = D("Role");
        $arrUser         = $Muser->select();
        $arrDepartment   = $Mdepartment->select();
        $arrRole         = $Mrole->select();

        load("@.Array");
        $arrUserToHashmap       = array_to_hashmap($arrUser,"id","nickname");
        $arrDepartmentToHashmap = array_to_hashmap($arrDepartment,"id","name");
        $arrRoleToHasmap        = array_to_hashmap($arrRole,"id","name");

        $importResult = array('success'=>0,'error'=>array());

        if($isCover == 1){
            $arrUserDbResult =  $Muser->where(array("status"=>array("neq",-1)))->select();
            $arrId = array_col_values($arrUserDbResult,'id');
        }else{
            $table = $Muser->getTableName();
            $sql   = "TRUNCATE TABLE {$table}";
            $truncate = $Muser->execute($sql);
        }

        //获取导出的字段和标题数组
        $arrExportFieldAndTitle = $this->getUserFieldAndTitle();
        $arrExportFields = array_col_values($arrExportFieldAndTitle,'field');
        $arrExportTitles = array_col_values($arrExportFieldAndTitle,'title');

        //组成 filed=>title数组
        $arrExportCombine = array_combine($arrExportFields,$arrExportTitles);

        //dump($arrData);
        //计算数据行数总数
        $dataLength = count($arrData);

        //循环数组数据从第一行数据开始 （数组0行为标题行）
        for($i = 1; $i<$dataLength; $i++){  //$i 行数据
            $count = $cols = $flag = 0;
            $rowData = array();
            //循环第一行数组的每个标题
            for($j = 0; $j < count($arrData[0]);$j++){  //$j 列字段
                if(in_array(trim($arrData[0][$j]),$arrExportTitles)){
                    //搜索标题对应字段
                    $field = array_search($arrData[0][$j],$arrExportCombine);
                    //dump($field);
                    $arrData[$i][$j] = trim($arrData[$i][$j]);

                    //验证字段必填项和唯一字段
                    switch($field){
                        case 'account':
                            if(empty($arrData[$i][$j])){
                                $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'不能为空;';
                                break 2;
                            }

                            //查询原先记录是否存在
                            $res = $Muser->getByAccount($arrData[$i][$j]);
                            //如果记录存在并且是清空导入，提示数据重复
                            if($res && !$isCover){
                                $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'已经存在;';
                                break 2;
                            }

                            $rowData[$field] = $arrData[$i][$j];
                            break;

                        case 'nickname':
                            if(empty($arrData[$i][$j])){
                                $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'不能为空;';
                                break 2;
                            }

                            $res = $Muser->getByNickname($arrData[$i][$j]);
                            if($res && !$isCover){
                                $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'"'.$arrData[$i][$j].'" 已经存在;';
                                break 2;
                            }

                            $rowData[$field] = $arrData[$i][$j];
                            break;

                        case 'sex':
                            if(!empty($arrData[$i][$j])){
                                $rowData[$field] = $arrData[$i][$j] == "男" ? 1 : ($arrData[$i][$j] == "女" ? 0 : "");
                                if($rowData[$field] === ''){
                                    $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'"'.$arrData[$i][$j].'" 不存在;';
                                    break 2;
                                }
                            }
                            break;

                        case 'email':
                            if(!empty($arrData[$i][$j])){
                                //验证email
//                                if(!preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",$arrData[$i][$j])){
//                                    $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'格式不正确;';
//                                    break 2;
//                                }
                                $rowData[$field] = $arrData[$i][$j];
                            }
                            break;

                        case 'mobile':
                            if(!empty($arrData[$i][$j])){
                                //验证手机
//                                if(!preg_match("/(^(\d{2,4}[-_－—])?\d{3,8}([-_－—]\d{7,8})?([-_－—]\d{1,7})?$)|(^0?(13[0-9]|15[^4\D]|18[05-9])\d{8}$)/",$arrData[$i][$j])){
//                                    $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'格式不正确;';
//                                    break 2;
//                                }
                                $rowData[$field] = $arrData[$i][$j];
                            }
                            break;

                        case 'birthday'://生日
                            if(!empty($arrData[$i][$j])){
                                $rowData[$field] = strtotime(excelTime($arrData[$i][$j]));
                            }
                            break;

                        case 'zhiwu'://岗位职务
                            if(!empty($arrData[$i][$j])){
                                $rowData[$field] = $arrData[$i][$j];
                            }
                            break;

                        case 'status': //状态
                            if(!empty($arrData[$i][$j])){
                                if($arrData[$i][$j] == '启用'){
                                    $rowData[$field] = 1;
                                }elseif($arrData[$i][$j] == '禁用'){
                                    $rowData[$field] = 0;
                                }elseif($arrData[$i][$j] == '注销'){
                                    $rowData[$field] = -1;
                                }else{
                                    $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'"'.$arrData[$i][$j].'" 不存在;';
                                    break 2;
                                }
                            } else {
                                $rowData[$field] = 1;
                            }
                            break;

                        case 'departments'://所属部门
                            if(!empty($arrData[$i][$j])){
                                $rowData[$field] = array_search($arrData[$i][$j],$arrDepartmentToHashmap);
                                //dump($rowData[$field]);
                                if(!$rowData[$field]){
                                    $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'"'.$arrData[$i][$j].'" 不存在;';
                                    break 2;
                                }
                            }
                            break;

                        case 'roles'://所属岗位
                            if(!empty($arrData[$i][$j])){
                                $rowData[$field] = array_search($arrData[$i][$j],$arrRoleToHasmap);
                                //dump($rowData[$field]);
                                if(!$rowData[$field]){
                                    $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'"'.$arrData[$i][$j].'" 不存在;';
                                    break 2;
                                }
                            }
                            break;

                        case 'create_user_id': //创建人
                            if(!empty($arrData[$i][$j])){
                                $rowData[$field] = array_search($arrData[$i][$j],$arrUserToHashmap);
                                if(!$rowData[$field]){
                                    $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'"'.$arrData[$i][$j].'" 不存在;';
                                    break 2;
                                }
                            }
                            break;

                        case 'update_user_id'://最后修改人
                            if(!empty($arrData[$i][$j])){
                                $rowData[$field] = array_search($arrData[$i][$j],$arrUserToHashmap);
                                if(!$rowData[$field]){
                                    $importResult['error'][$i] .= '第'.$i.'行 '.$arrData[0][$j].'"'.$arrData[$i][$j].'" 不存在;';
                                    break 2;
                                }
                            } else {
                                $rowData[$field] = $_SESSION[C("USER_AUTH_KEY")];
                            }
                            break;

                        case 'create_time': //创建时间
                            if(!empty($arrData[$i][$j])){
                                $rowData[$field] = strtotime(excelTime($arrData[$i][$j]));
                            } else {
                                $rowData[$field] = time();
                            }
                            break;

                        case 'update_time':
//                          if(!empty($arrData[$i][$j])){
//                              $rowData[$field] = strtotime(excelTime($arrData[$i][$j]));
//                          } else {
                            $rowData[$field] = time();
//                          }
                            break;

                        default:
                            $rowData[$field] = $arrData[$i][$j];
                            break;
                    }
                }
            }

            if(!$importResult['error'][$i]  && $rowData){ //数据验证没有错误
                //用户部门数据
                $departmentId = $rowData['department_id'] ? $rowData['department_id'] :"";
                //dump($res);
                //dump($isCover);
                //dump($rowData);
                if($isCover && $res ){ //数据存在，修改
                    $map['id'] = $res['id'];
                    if(isset($rowData['create_user_id'])){
                        unset($rowData['create_user_id']);
                    }
                    if(isset($rowData['create_time'])){
                        unset($rowData['create_time']);
                    }

                    $rowData = $Muser->create($rowData);
                    if($rowData){
                        $save = $Muser->where($map)->save($rowData);
                        if($save === false ){
                            $importResult['error'][$i] = $Muser->getError();
                        }else{
                            $importResult['success'] ++;
                        }
                    }else{
                        $importResult['error'][$i] = $Muser->getError();
                    }

                }else{//清空导入或者追加导入新
                    if(isset($rowData['update_user_id'])){
                        unset($rowData['update_user_id']);
                    }
                    if(isset($rowData['update_time'])){
                        unset($rowData['update_time']);
                    }

                    $rowDataData = $Muser->create($rowData);
                    if($rowDataData){
                        $Muser->startTrans();
                        $rowDataData['password'] = pwdHash('123456');
                        //dump($rowDataData);
                        $create = $Muser->add($rowDataData);
                        //dump($Muser->getLastSql());
                        //dump($create);
                        if($create){
                            if($departmentId){
                                $rowMidData['department_id'] = $departmentId;
                                $rowMidData['user_id'] = $create;
                                $addDepartmentUserId = $MdepartmentUser->add($rowMidData);
                                if($addDepartmentUserId){
                                    $Muser->commit();
                                    $importResult['success'] ++;
                                }else{
                                    $Muser->rollback();
                                    $importResult['error'][$i] = $MdepartmentUser->getError();
                                }
                            }else{
                                $Muser->commit();
                                $importResult['success'] ++;
                            }
                        }else{
                            $Muser->rollback();
                            $importResult['error'][$i] = $Muser->getError();
                        }
                    } else {
                        $importResult['error'][$i] = $Muser->getError();
                    }
                }
            }
        }
        //dump($importResult);
        //exit;
        if( !$importResult['error']  && $importResult['success'] == 0 ){
            $importResult['error'][] = "没有导入任何数据";
        }

        return $importResult;
    }
}