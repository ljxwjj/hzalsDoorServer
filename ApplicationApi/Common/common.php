<?php
// +----------------------------------------------------------------------
// | ThinkPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2007 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: common.php 2601 2012-01-15 04:59:14Z liu21st $

//公共函数
function toDate($time, $format = 'Y-m-d H:i:s') {
	if (empty ( $time )) {
		return '';
	}
	$format = str_replace ( '#', ':', $format );
	return date ($format, $time );
}

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){    
    
    if(mb_strlen($str,$charset) > $length){
        import("ORG.Util.String");
        return String::msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true);
    }
    return $str;      
}

function getStatus($status, $imageShow = true) {
	switch ($status) {
		case 0 :
			$showText = '禁用';
			$showImg = '<IMG SRC="__PUBLIC__/Images/disable.png" WIDTH="15" HEIGHT="15" BORDER="0" ALT="禁用">';
			break;
		case 2 :
			$showText = '待审';
			$showImg = '<IMG SRC="__PUBLIC__/Images/prected.gif" WIDTH="15" HEIGHT="15" BORDER="0" ALT="待审">';
			break;
		case - 1 :
			$showText = '删除';
			$showImg = '<IMG SRC="__PUBLIC__/Images/delete.png" WIDTH="15" HEIGHT="15" BORDER="0" ALT="删除">';
			break;
		case 1 :
		default :
			$showText = '正常';
			$showImg = '<IMG SRC="__PUBLIC__/Images/ok.png" WIDTH="15" HEIGHT="15" BORDER="0" ALT="正常">';

	}
	return ($imageShow === true) ?  $showImg  : $showText;
}

function showStatus($status, $id) {
	switch ($status) {
		case 0 :
			$info = '<a href="javascript:resume(\'id\',' . $id . ')" title="恢复">恢复</a>';//被禁用
			break;
		case 1 :
			$info = '<a href="javascript:forbid(\'id\',' . $id . ')" title="禁用">禁用</a>';//正常
			break;
		case - 1 :
			$info = '<a href="javascript:recycle(\'id\',' . $id . ')" title="还原">还原</a>';//虚拟删除
			break;
	}
	return $info;
}

function showStatus2($status, $id) {
	switch ($status) {
		case 0 :
			$info = '<a href="javascript:resume(\'id\',' . $id . ')" title="恢复" class="button icon-revert with-tooltip"></a>';//被禁用
			break;
		case 1 :
			$info = '<a href="javascript:forbid(\'id\',' . $id . ')" title="禁用" class="button icon-forbidden with-tooltip"></a>';//正常
			break;
		case - 1 :
			$info = '<a href="javascript:recycle(\'id\',' . $id . ')" title="还原" class="button icon-replay-all with-tooltip"></a>';//虚拟删除
			break;
	}
	return $info;
}


function pwdHash($password, $type = 'md5') {
	return hash ( $type, $password );
}

/**
 * 检查变量是否空白
 *
 * 去除变量空白时 $greedy=true.
 * - " " (ASCII 32 (0x20)), 通常空白
 * - "\t" (ASCII 9 (0x09)), 制表符
 * - "\n" (ASCII 10 (0x0A)), 回车
 * - "\r" (ASCII 13 (0x0D)), 换行
 * - "\0" (ASCII 0 (0x00)), NULL
 * - "\x0B" (ASCII 11 (0x0B)), 垂直制表
 *
 */
function isBlank($val, $greedy = true) {
    if (is_array($val)) {
        if ($greedy) {
            if (empty($val)) {
                return true;
            }
            $array_result = true;
            foreach ($val as $in) {
                $array_result = isBlank($in, $greedy);
                if (!$array_result) {
                    return false;
                }
            }
            return $array_result;
        } else {
            return empty($val);
        }
    }

    if ($greedy) {
        $val = preg_replace("/　/", "", $val);
    }

    $val = trim($val);
    if (strlen($val) > 0) {
        return false;
    }
    return true;
}  

function getCommaList($array, $space=true, $arrPop = array()) {
    if (count($array) > 0) {
        $line = "";
        foreach($array as $val) {
            if (!in_array($val, $arrPop)) {
                if ($space) {
                    $line .= $val . ", ";
                } else {
                    $line .= $val . ",";
                }
            }
        }
        if ($space) {
            $line = ereg_replace(", $", "", $line);
        } else {
            $line = ereg_replace(",$", "", $line);
        }
        return $line;
    } else {
        return false;
    }

}
	
/** 
 * 人民币小写转大写 
 * 
 * @param string $number 数值 
 * @param string $int_unit 币种单位，默认"元"，有的需求可能为"圆" 
 * @param bool $is_round 是否对小数进行四舍五入 
 * @param bool $is_extra_zero 是否对整数部分以0结尾，小数存在的数字附加0,比如1960.30， 
 *             有的系统要求输出"壹仟玖佰陆拾元零叁角"，实际上"壹仟玖佰陆拾元叁角"也是对的 
 * @return string 
 */ 
function num2rmb($number = 0, $int_unit = '元', $is_round = TRUE, $is_extra_zero = FALSE) 
{ 
    // 将数字切分成两段 
    $parts = explode('.', $number, 2); 
    $int = isset($parts[0]) ? strval($parts[0]) : '0'; 
    $dec = isset($parts[1]) ? strval($parts[1]) : ''; 
 
    // 如果小数点后多于2位，不四舍五入就直接截，否则就处理 
    $dec_len = strlen($dec); 
    if (isset($parts[1]) && $dec_len > 2) 
    { 
        $dec = $is_round 
                ? substr(strrchr(strval(round(floatval("0.".$dec), 2)), '.'), 1) 
                : substr($parts[1], 0, 2); 
    } 
 
    // 当number为0.001时，小数点后的金额为0元 
    if(empty($int) && empty($dec)) 
    { 
        return '零'; 
    } 
 
    // 定义 
    $chs = array('0','壹','贰','叁','肆','伍','陆','柒','捌','玖'); 
    $uni = array('','拾','佰','仟'); 
    $dec_uni = array('角', '分'); 
    $exp = array('', '万'); 
    $res = ''; 
 
    // 整数部分从右向左找 
    for($i = strlen($int) - 1, $k = 0; $i >= 0; $k++) 
    { 
        $str = ''; 
        // 按照中文读写习惯，每4个字为一段进行转化，i一直在减 
        for($j = 0; $j < 4 && $i >= 0; $j++, $i--) 
        { 
            $u = $int{$i} > 0 ? $uni[$j] : ''; // 非0的数字后面添加单位 
            $str = $chs[$int{$i}] . $u . $str; 
        } 
        //echo $str."|".($k - 2)."<br>"; 
        $str = rtrim($str, '0');// 去掉末尾的0 
        $str = preg_replace("/0+/", "零", $str); // 替换多个连续的0 
        if(!isset($exp[$k])) 
        { 
            $exp[$k] = $exp[$k - 2] . '亿'; // 构建单位 
        } 
        $u2 = $str != '' ? $exp[$k] : ''; 
        $res = $str . $u2 . $res; 
    } 
 
    // 如果小数部分处理完之后是00，需要处理下 
    $dec = rtrim($dec, '0'); 
 
    // 小数部分从左向右找 
    if(!empty($dec)) 
    { 
        $res .= $int_unit; 
 
        // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求 
        if ($is_extra_zero) 
        { 
            if (substr($int, -1) === '0') 
            { 
                $res.= '零'; 
            } 
        } 
 
        for($i = 0, $cnt = strlen($dec); $i < $cnt; $i++) 
        { 
            $u = $dec{$i} > 0 ? $dec_uni[$i] : ''; // 非0的数字后面添加单位 
            $res .= $chs[$dec{$i}] . $u; 
        } 
        $res = rtrim($res, '0');// 去掉末尾的0 
        $res = preg_replace("/0+/", "零", $res); // 替换多个连续的0 
    } 
    else 
    { 
        $res .= $int_unit . '整'; 
    } 
    return $res; 
}

/**
 * 获取用户的所有角色*
 *
 * @param int $userId
 * @return array
 */
function getUserRoles($userId){
    load("@.Array");  
    $Mrole = D("AuthRole");
    $roleTb = $Mrole->getTableName();
    $MroleUser = M("RoleUser");
    $roleUserTb = $MroleUser->getTableName();
    
    $arrRoles = $Mrole->join("{$roleUserTb} ON {$roleUserTb}.role_id = {$roleTb}.id")
    	->where(array("{$roleUserTb}.user_id"=>array("eq", $userId)))
    	->field("{$roleTb}.*")
    	->select();
    return $arrRoles;
}

/**
 * 获取用户角色编码*
 *
 * @param int $userId
 * @return array
 */
function getUserRoleCodes($userId){
    load("@.Array");  
    $Mrole = D("AuthRole");
    $roleTb = $Mrole->getTableName();
    $MroleUser = M("RoleUser");
    $roleUserTb = $MroleUser->getTableName();
    
    $roleCodes = $Mrole->join("{$roleUserTb} ON {$roleUserTb}.role_id = {$roleTb}.id")
    	->where(array("{$roleUserTb}.user_id"=>array("eq", $userId)))
    	->field("{$roleTb}.code")
    	->select();
    	
    $arrCodes = array_col_values($roleCodes, "code");
    return $arrCodes;
}

/**
 * 获取用户对应的角色的可操作的数据范围*
 *
 * @param unknown_type $userId
 * @param unknown_type $moduleName
 * @param unknown_type $actionName
 */
function getUserDataAccessRangeType($userId,$moduleName,$actionName){
    $arrRoles = getUserRoles($userId);
    $MdataAccess = D("DataAccess");
    $Muser = D("User");
    $arrRangeType = array();
    $map['module_name'] = $moduleName;
    $map['action_name'] = $actionName;
    load("@.Array");
    $arrCodes = array_col_values($arrRoles,'code');
    $userRec = $Muser->getById($userId);

    //系统管理员,全部
    if(in_array("gly", $arrCodes) || $userRec['account'] == 'admin'){
        $arrRangeType = array(1);
    } elseif($arrRoles) {
        $arrRolesId = array_col_values($arrRoles,'id');
        $map['role_id'] = array('in',$arrRolesId);
        $arrRangeType = $MdataAccess->where($map)->order("id DESC")->field("range_type")->distinct(true)->select();
        if($arrRangeType){
            $arrRangeType = array_col_values($arrRangeType,'range_type');
            if(in_array(1,$arrRangeType)){
                $arrRangeType = array(1);
            } elseif(in_array(2,$arrRangeType)){
                $arrRangeType = array(2);
            }
        }
    }
    return $arrRangeType;
}

/**
 * 根据用户id和范围类型查询可以操作的用户数据*
 *
 * @param int $userId
 * @param array $arrRangeType
 */
function getUserDataAccessRangeUserId($userId,$arrRangeType){
    $arrUserId = array();
    foreach($arrRangeType as $rangeType){        
        switch($rangeType){
            case 2://本部门和所有子部门的用户
                $arrDepartmentAndChildDepartmentsId = getUserDepartmentAndChildDepartmentsId($userId);
                if($arrDepartmentAndChildDepartmentsId){
                    $arrDepartmentAndChildDepartmentsUserId = getDepartmentUserId($arrDepartmentAndChildDepartmentsId);
                    if($arrDepartmentAndChildDepartmentsUserId){
                        $arrUserId = array_merge($arrUserId,$arrDepartmentAndChildDepartmentsUserId);
                    }
                }
                break;
            case 3://本部门用户
                $arrDepartmentId = getUserDepartmentsId($userId);
                //dump($arrDepartmentId);
                if($arrDepartmentId){
                    $arrDepartmentUserId = getDepartmentUserId($arrDepartmentId);
                    if($arrDepartmentUserId){
                        $arrUserId = array_merge($arrUserId,$arrDepartmentUserId);
                    }
                }
                break;
            case 5://自己                      
                $arrUserId = array_merge($arrUserId,array($userId));                
                break;
            default:
                break;
        }
    }
    $arrUserId = array_values(array_unique($arrUserId));
    return $arrUserId;
}



/**
 * 获取用户所在部门Id*
 *
 * @param int $userId
 */
function getUserDepartmentsId($userId){
    $arrDepartments = getUserDepartments($userId);    
    load("@.Array");
    $arrDepartmentsId = array_col_values($arrDepartments,'department_id');
    return $arrDepartmentsId;
}


/**
 * 获取用户所在的部门以及所有子部门的ID*
 *
 */
function getUserDepartmentAndChildDepartmentsId($userId){
    $Mdepartment = M("Department");
    $allDepartments = array();
    load("@.Array");
    
    $departments = getUserDepartments($userId);
	$arrDepartments = array_col_values($departments, "department_id");
	
	while ($arrDepartments) {
		$allDepartments = array_merge($allDepartments, $arrDepartments);
		$childDepartments = $Mdepartment->where(array("pid"=>array("in", $arrDepartments)))
		->field("id")
		->select();
		$arrDepartments = array_col_values($childDepartments, "id");
	}
	return $allDepartments;
}

/**
 * 获取部门下的所有用户ID*
 *
 * @param array $arrDepartmentsId
 * @return array
 */
function getDepartmentUserId($arrDepartmentsId){
    $MdepartmentUser = D("DepartmentUser");   
    $departmentUserTb =  $MdepartmentUser->getTableName();
    
    $Mrole = D("AuthRole");
    $roleTb = $Mrole->getTableName();
    $MroleUser = M("RoleUser");
    $roleUserTb = $MroleUser->getTableName();
    load("@.Array");
    
	$arrDepartmentUser = $MdepartmentUser
	->where(array("{$departmentUserTb}.department_id"=>array("in", $arrDepartmentsId)))
	->field("{$departmentUserTb}.user_id")
	->select();
	
	$arrDepartmentUserId = array_col_values($arrDepartmentUser,'user_id');
    return  $arrDepartmentUserId;
}


/**
 * 验证客户数据权限*
 *
 * @param int $userId
 * @param int $id
 * @param string $modelName
 * @param string $actionName
 */
function checkCustomerAuth($userId,$id,$modelName,$actionName){
    $modelName = strtolower($modelName);
    $actionName = strtolower($actionName);
    $Mcustomer = D("Customer");
    $arrRangeType = getUserDataAccessRangeType($userId,$modelName,$actionName);
    $rangeUserId = array();
    if(in_array(1,$arrRangeType)){
        return true;
    }
    
    $rangeUserId = getUserDataAccessRangeUserId($userId,$arrRangeType);
    if($rangeUserId){   
        $map['customer_manager_id'] = array("in",$rangeUserId);
        $map['id'] = $id;
        $findCount = $Mcustomer->where($map)->count();        
        if($findCount > 0){
            return true;
        }
        
        //查看客户是否在数据范围内的客户的共享的客户信息中
        $findUserCustomerShareCount = findUserCustomerShareCount($rangeUserId,$id);
        if($findUserCustomerShareCount > 0){
            return true;
        }
    }
    return false;
}


//权限节点验证
function checkAuth($modelName=MODULE_NAME,$actionName=ACTION_NAME,$id=""){
//    dump($id);
//    dump($modelName);
//    dump($actionName);
    //管理员
	if($_SESSION['administrator']){
		return true;
	}
	
	//查询如果是管理员角色,拥有系统所有权限
	$arrUserRoleCodes = getUserRoleCodes($_SESSION[C('USER_AUTH_KEY')]);	
	if(in_array('gly',$arrUserRoleCodes)){
	    return true;
	}
	
	//控制权限验证
	$accessList = RBAC::getAccessList($_SESSION[C('USER_AUTH_KEY')]);	
	if(!isset($accessList['HOME'][strtoupper($modelName)][strtoupper($actionName)])){
	   return false;
	}
	
	//对于集团、客户、联系人、验证分配的数据权限
	//项目，材料需求，日志，客户关系,存在数据  需要验证所在项目分配的数据权限
	$modelName = strtolower($modelName);
	$actionName = strtolower($actionName);
	
    if($modelName == 'project' || $modelName == 'materialrequirement'){
        $Mproperty = D("Property");        
        if($id){//具体数据            
            $projectId = "";            
            //判断项目ID
            switch($modelName){
                case 'project':
                    //物业ID信息相关操作
                    if(strpos($actionName,'property') !== false && $actionName !="addproperty"){
                        $projectId = $Mproperty->getFieldById($id,'project_id');
                    } else {
                    //项目ID
                        $projectId = $id;
                    }                    
                    break;
                case 'materialrequirement':                    
                    $projectId = $Mproperty->getFieldById($id,'project_id');                    
                    break;
                default:
                    break;
            }
            
            if($projectId){
                $checkProjectId = checkProjectAuth($_SESSION[C('USER_AUTH_KEY')],$projectId,$modelName,$actionName);
                if($checkProjectId){
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }            
        }        
	} else if($modelName == 'log'){
	    $Mlog = D("Log");
	    if($id){//具体数据
	        $checkLogId = checkLogAuth($_SESSION[C('USER_AUTH_KEY')],$id,$modelName,$actionName);
	        if($checkLogId){
                return true;
            } else {
                return false;
            }
	    }	    	    
	} else if($modelName == 'customergroup'){
	    if($id){
	        $checkCustomerGroupId = checkCustomerGroupAuth($_SESSION[C('USER_AUTH_KEY')],$id,$modelName,$actionName);
	        if($checkCustomerGroupId){
                return true;
            } else {
                return false;
            }
	    }
	} else if($modelName == 'customer'){
	    if($id){
	        $checkCustomerId = checkCustomerAuth($_SESSION[C('USER_AUTH_KEY')],$id,$modelName,$actionName);
	        if($checkCustomerId){
                return true;
            } else {
                return false;
            }
	    }
	} else if($modelName == 'customeruser'){
	    if($id){
	        $checkCustomerUserId = checkCustomerUserAuth($_SESSION[C('USER_AUTH_KEY')],$id,$modelName,$actionName);
	        if($checkCustomerUserId){
                return true;
            } else {
                $checkProjectCustomerUserId = checkProjectCustomerUserAuth($_SESSION[C('USER_AUTH_KEY')],$id,$modelName,$actionName);
                if($checkProjectCustomerUserId){
                    return true;
                }
                return false;
            }
	    }
	}
	
	return true;
}


 /**
 * 根据浏览器判断转化的文件名称*
 *
 * @param string $fileName
 */
function getExcelConverFileName($fileName){
//    $ua = $_SERVER["HTTP_USER_AGENT"];
//    $encoded_filename = urlencode($fileName);
//    $encoded_filename = str_replace("+", "%20", $encoded_filename);
//    echo $fileName;
//    echo $ua;
//    if (preg_match("/MSIE/", $ua)) {
//        return $encoded_filename;           
//    } else {
//        return  $fileName ;
//    }
      $fileName =  iconv("UTF-8","gb2312",$fileName);
      return $fileName;
}

/**
 * EXCEL时间实际转化*
 *
 * @param int $date
 * @param string $time
 * @return unknown
 */
function excelTime($date, $time = false) {  
    if(function_exists('GregorianToJD')){  
        if (is_numeric( $date )) {  
        $jd = GregorianToJD( 1, 1, 1970 );  
        $gregorian = JDToGregorian( $jd + intval ( $date ) - 25569 );  
        $date = explode( '/', $gregorian );  
        $date_str = str_pad( $date [2], 4, '0', STR_PAD_LEFT )  
        ."-". str_pad( $date [0], 2, '0', STR_PAD_LEFT )  
        ."-". str_pad( $date [1], 2, '0', STR_PAD_LEFT )  
        . ($time ? " 00:00:00" : '');  
        return $date_str;  
        }  
    }else{  
        $date=$date>25568?$date+1:25569;  
        /*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/  
        $ofs=(70 * 365 + 17+2) * 86400;  
        $date = date("Y-m-d",($date * 86400) - $ofs).($time ? " 00:00:00" : '');  
    }  
  return $date;  
}  


/**
 * 返回EXCEL的列名*
 *
 * @return unknown
 */
function getExcelColumnKey(){
    return  array(
        'A','B','C','D','E','F','G','H','I','J','K',
        'L','M','N','O','P','Q','R','S','T','U','V',
        'W','X','Y','Z','AA','AB','AC','AD','AE','AF',
        'AG','AH','AI','AJ','AK','AL','AM','AN','AO',
    );
}


/**
 * 获取数据筛选类型函数*
 *
 * @return array
 */
function getDataAccessRangeType(){
    return array(
        '1'=>'全部',
        '2'=>'本部门以及所有子部门',
        '3'=>'本部门',
        ///'4'=>'所有子部门',
        '5'=>'自己',
        //'6'=>'选择相关人员'
    );    
}

/**
 * 获取需要数据过滤访问的模块*
 *
 */
function getDataAccessModule(){
    return array(
        array('module_name'=>'customergroup','module_title'=>'集团管理'),
        array('module_name'=>'customer','module_title'=>'客户管理'),
        array('module_name'=>'customeruser','module_title'=>'联系人管理'),
        array('module_name'=>'project','module_title'=>'项目管理'),
        array('module_name'=>'materialrequirement','module_title'=>'项目材料需求管理'),   
        array('module_name'=>'log','module_title'=>'日志管理'), 
        array('module_name'=>'materialcategorymanage','module_title'=>'材料管理'),
        array('module_name'=>'brandreport','module_title'=>'品牌报备'),
        array('module_name'=>'scheme','module_title'=>'方案管理'),             
    );    
}

/**
 * 获取需要数据过滤访问的模块的节点*
 *
 */
function getDataAccessModuleNode($module_name){
    $module_name = strtolower($module_name);
    $node = array();
    switch($module_name){
        case 'customergroup':
            $node = array(
                array('action_name'=>'index','action_title'=>'列表'),
                array('action_name'=>'view','action_title'=>'查看'),
                array('action_name'=>'edit','action_title'=>'编辑'),
                array('action_name'=>'forbid','action_title'=>'禁用'),
                array('action_name'=>'resume','action_title'=>'恢复'),
                array('action_name'=>'del','action_title'=>'删除')
            );
           break;
       case 'customer':
           $node = array(
                array('action_name'=>'index','action_title'=>'列表'),
                array('action_name'=>'view','action_title'=>'查看'),
                array('action_name'=>'edit','action_title'=>'编辑'),
                array('action_name'=>'forbid','action_title'=>'禁用'),
                array('action_name'=>'resume','action_title'=>'禁用恢复'),                
                array('action_name'=>'del','action_title'=>'删除')
            );
           break;
       case 'customeruser':
           $node = array(
                array('action_name'=>'index','action_title'=>'列表'),
                array('action_name'=>'view','action_title'=>'查看'),
                array('action_name'=>'edit','action_title'=>'编辑'),
                array('action_name'=>'forbid','action_title'=>'禁用'),
                array('action_name'=>'resume','action_title'=>'禁用恢复'),
                array('action_name'=>'del','action_title'=>'删除'),
                array('action_name'=>'bidlist','action_title'=>'客户关系')
            );
            break;
       case 'project':
            $node = array(
                array('action_name'=>'index','action_title'=>'列表'),
                array('action_name'=>'view','action_title'=>'查看'),
                array('action_name'=>'projectinfo','action_title'=>'编辑页查看'),
                array('action_name'=>'edit','action_title'=>'编辑'),
                array('action_name'=>'del','action_title'=>'删除'),
                array('action_name'=>'addproperty','action_title'=>'新增物业'),
                array('action_name'=>'editproperty','action_title'=>'编辑物业'),
                array('action_name'=>'showproperty','action_title'=>'查看物业详情'),
                array('action_name'=>'delproperty','action_title'=>'删除物业'),
                array('action_name'=>'projectuser','action_title'=>'项目成员列表'),
                array('action_name'=>'saveprojectuser','action_title'=>'新增项目成员'),
                array('action_name'=>'delprojectuser','action_title'=>'删除项目成员'),
                array('action_name'=>'saveprojectnode','action_title'=>'保存项目成员项目权限'),
            );
            break; 
       case 'materialrequirement':
            $node = array(
                array('action_name'=>'index','action_title'=>'列表'),
                array('action_name'=>'view','action_title'=>'查看'),
                array('action_name'=>'bidlist','action_title'=>'材料客户关系'),               
            );
            break;
       case 'log':
            $node = array(
                array('action_name'=>'index','action_title'=>'列表'),
                array('action_name'=>'view','action_title'=>'查看'),
                array('action_name'=>'logcomment','action_title'=>'日志点评'),
                array('action_name'=>'logcommentfeedback','action_title'=>'点评反馈')
            );
           break;
       case 'materialcategorymanage':
            $node = array(
                array('action_name'=>'saleIndex','action_title'=>'已回馈材料列表(营销部)'),
                array('action_name'=>'departmentIndex','action_title'=>'已回馈材料列表(事业部)'),
                array('action_name'=>'logcomment','action_title'=>'机会确认'),
            );
           break;
       case 'brandreport':
            $node = array(
                array('action_name'=>'index','action_title'=>'列表'),
                array('action_name'=>'view','action_title'=>'查看'),
                array('action_name'=>'edit','action_title'=>'编辑'),
                array('action_name'=>'del','action_title'=>'删除'),
                array('action_name'=>'approveindex','action_title'=>'审核列表')
            );
           break;
       case 'scheme':
            $node = array(
                array('action_name'=>'index','action_title'=>'列表'),                
                array('action_name'=>'view','action_title'=>'查看'),
                array('action_name'=>'edit','action_title'=>'编辑'),
                array('action_name'=>'del','action_title'=>'删除'),
                array('action_name'=>'addQuotation','action_title'=>'新增配置'),
                array('action_name'=>'editQuotation','action_title'=>'配置修改'),
                array('action_name'=>'viewQuotation','action_title'=>'查看配置'),
                array('action_name'=>'viewQuotationHistory','action_title'=>'配置历史'),                
                array('action_name'=>'approve','action_title'=>'审核'),     
                array('action_name'=>'verifyPrice','action_title'=>'核价'),
                array('action_name'=>'applyVerifyPrice','action_title'=>'核价申请'),
                array('action_name'=>'finalJudgment','action_title'=>'终审'),                
                array('action_name'=>'addTrack','action_title'=>'跟踪反馈'),
                array('action_name'=>'signView','action_title'=>'签约比例查看'),
                array('action_name'=>'exportPdf','action_title'=>'导出'),
                array('action_name'=>'reportHistory','action_title'=>'审核历史'),
                array('action_name'=>'showTrack','action_title'=>'查看跟踪反馈'),
            );
           break;           
       default:
           break;             
    }
    return $node;
}



/**
 * 根据部门ID,D递归查询部门以及子部门下的所有用户*
 *
 * @param unknown_type $departmentId
 * @return unknown
 */
function getLoopDepartmentUserId($departmentId){
    $Mdepartment     = D("Department");
    $MdepartmentUser = D("DepartmentUser");    
    $arrUserId       = array();
    $arrDepartment   = $Mdepartment->where(array("id"=>array("eq",$departmentId)))->select();    
    if($arrDepartment){
        load("@.Array");
        $arrLoopDepartment = loopChildDepartments($arrDepartment,$Mdepartment);
        $arrDepartmentId   = getAllDepartMentIds($arrLoopDepartment);        
        $arrUser = $MdepartmentUser->where(array('department_id'=>array('in',$arrDepartmentId)))->field('user_id')->select();
        $arrUserId = array_col_values($arrUser, 'user_id');
    }
    return $arrUserId;
}

/**
 * 人民币金额格式化
 *
 * @param string $num  金额
 * @param string $separator 千位分隔符
 * @param int $accuracy 精度
 * @return string
 */

function rmbFormat($num,$separator=',',$accuracy=2 ){

 $numArr = explode('.',$num); 

 $IntPart = $numArr['0'];

 $c = strlen($IntPart); 

 $prefix = NULL; 

 $IntPart = $c>0 && $IntPart{0}=='-' ? $prefix = substr($IntPart,1) : $IntPart; 

 $IntPart = str_pad($IntPart, $c+(3-$c%3) , '0', STR_PAD_LEFT);

 $arr = str_split($IntPart, 3);

 $Int = ltrim( implode($separator , $arr),'0'.$separator ); 

 $Int = empty($Int) ? '0' : $Int;

 $addPart = ( $f = strlen($numArr['1']) ) < $accuracy ? $accuracy - $f : 0;

 $fractional = empty($numArr['1'])

             ? str_repeat('0', $accuracy )

             : substr($numArr['1'],0,$accuracy) . str_repeat('0', $addPart);

             

 $prefix = empty($prefix) ? $prefix : '-';

 return $prefix . $Int .'.'. $fractional;

}

function createToeken($length = 32) {
    $token = md5(time()."als2005");
    return substr(str_shuffle($token), 0, $length);;
}

function createUCenterQR() {
    $token = createToeken(8);
    $token = hexdec($token);
    $token = $token>>2;
    $token = $token<<2;
    return dechex($token);
}

function createShareQR() {
    $token = createToeken(8);
    $token = hexdec($token);
    $token = $token>>2;
    $token = $token<<2;
    $token = $token | 0b01;
    return dechex($token);
}

function generate_code($length = 6) {
    return substr(str_shuffle("012345678901234567890123456789"), 0, $length);
}

function doSendSms($mobile, $smsCode, $operation) {
    Vendor('api_sdk/smsUtil', COMMON_PATH . 'Vendor/', '.php');
    return sendSms($mobile, $smsCode, $operation);
}

function getUserDoors($user_id = null) {
    if (!$user_id) {
        return false;
    }
    if ($user_id == session(C('USER_AUTH_KEY'))) {
        $user = session('user');
    } else {
        $user = M('User')->find($user_id);
    }
    if (!$user) return false;
    $userDoors = M('UserDoor')->where(array('user_id'=>$user_id))->select();
    foreach ($userDoors as $door) {
        $doorMap[$door['controller_id']][$door['door_id']] = 1;
    }

    $department = M('UserDepartment')->where(array('user_id'=>$user_id))
        ->getField('department_id');
    while ($department) {
        $departmentDoors = M('DepartmentDoor')->where(array('department_id'=>$department))
            ->select();
        foreach ($departmentDoors as $door) {
            $doorMap[$door['controller_id']][$door['door_id']] = 1;
        }
        $department = M('Department')->where(array('id'=>$department))
            ->getField('pid');
    }
    return $doorMap;
}
?>