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
function showReportStatus(){
    
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


function getMesProcessStatus($status){
	$info = "";
	if(!is_null($status)){
		switch ($status) {
			case -1 :			
				$info = '待编辑';//待提交
				break;
			case 0 :			
				$info = '待提交';//待提交
				break;
			case 1 :
				$info = '待审核';//待审核
				break;
			case 2 :
				$info = '已审核';//已审核
				break;
			case 3 :
				$info = '已拒绝';//拒绝
				break;
			case 4 :
				$info = '待修改';//待修改
				break;
			default:
				$info = "";
				break;
		}
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
    $Mrole = D("Role");
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
    $Mrole = D("Role");
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
 * 获取用户所在部门*
 *
 * @param int $userId
 */
function getUserDepartments($userId){
    $MdepartmentUser = M("DepartmentUser");
	$departmentUserTb = $MdepartmentUser->getTableName();
    $departments = $MdepartmentUser->where(array("user_id"=>array("eq", $userId)))
    		->field("department_id")
    		->select();    		
    return $departments;
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
    
    $Mrole = D("Role");
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
 * 查询用户所属部门以及子部门下的项目经理*
 *
 * @param int $userId
 */
function getUserDepartmnetAndChildDepartmentProjectManager($userId){
    $MdepartmentUser = D("DepartmentUser");   
    $departmentUserTb =  $MdepartmentUser->getTableName();
    
    $Mrole = D("Role");
    $roleTb = $Mrole->getTableName();
    $MroleUser = M("RoleUser");
    $roleUserTb = $MroleUser->getTableName();
    
    load("@.Array");
    
    $allDepartments = getUserDepartmentAndChildDepartmentsId($userId);
    
    
	$xmjls = $MdepartmentUser->join("{$roleUserTb} ON {$roleUserTb}.user_id = {$departmentUserTb}.user_id")
		->join("{$roleTb} ON {$roleTb}.id = {$roleUserTb}.role_id")
		->where(array("{$departmentUserTb}.department_id"=>array("in", $allDepartments), "{$roleTb}.code"=>array("eq", "xmjl")))
		->field("{$roleUserTb}.user_id")
		->select();
	
    return  $xmjls;
}


/**
 * 查看用户同部门中项目经理*
 *
 * @param int $userId
 * @return array
 */
function getUserDepartmentProjectManager($userId){
    $MdepartmentUser = D("DepartmentUser");
    $departmentUserTb =  $MdepartmentUser->getTableName();
    
    $Mrole = D("Role");
    $roleTb = $Mrole->getTableName();
    
    $MroleUser = M("RoleUser");
    $roleUserTb = $MroleUser->getTableName();
    
    load("@.Array");
    
    $departments = getUserDepartments($userId);    
	$arrDepartments = array_col_values($departments, "department_id");
    
	$xmjls = $MdepartmentUser->join("{$roleUserTb} ON {$roleUserTb}.user_id = {$departmentUserTb}.user_id")
		->join("{$roleTb} ON {$roleTb}.id = {$roleUserTb}.role_id")
		->where(array("{$departmentUserTb}.department_id"=>array("in", $arrDepartments), "{$roleTb}.code"=>array("eq", "xmjl")))
		->field("{$roleUserTb}.user_id")
		->select();
	
    return  $xmjls;
}

/**
 * 获取用户可查看的项目组项目*
 *
 * @param int $userId
 */
function getUserProjectUserProject($userId,$moduleName="project",$actionName="index"){
    $Mproject = D("Project");
    $projectTb = $Mproject->getTableName();
    $MprojectUser = D("ProjectUser");
    $projectUserTb = $MprojectUser->getTableName();
    $MprojectNode = D("ProjectNode");
    load("@.Array");
    $arrProjectUserProject = array();
    $nodeName = strtolower($moduleName)."_".strtolower($actionName);
    $nodeId = $MprojectNode->getFieldByName($nodeName,'id');
    if($nodeId){
        //查询具有相关控制节点权限的项目
         $sql  = " SELECT P.* FROM {$projectTb} AS P,{$projectUserTb} AS PU ";
         $sql .= " WHERE P.status <> -1 AND P.id = PU.project_id ";
         $sql .= " AND PU.user_id = {$userId} ";
         $sql .= " AND FIND_IN_SET(".$nodeId.",project_node_id )";
         $arrProjectUserProject = $Mproject->query($sql);
    }
    return $arrProjectUserProject;    
}


/**
 * 获取用户的共享客户*
 *
 * @param array $userId
 */
function getUserCustomerShareCustomerId($arrUserId){
    $McustomerShare = D("CustomerShare");
    load("@.Array");
    $map['status'] = 1;
    $map['user_id'] = array("in",$arrUserId);
    $arrCustomerId = array();
    $arrCustomerShare = $McustomerShare->where($map)->select();
    if($arrCustomerShare){
        $arrCustomerId = array_col_values($arrCustomerShare,"customer_id");
    }
    return $arrCustomerId;
}

/**
 * 查询客户ID是否在用户共享客户中*
 *
 * @param array $arrUserId
 * @param int $customerId
 */
function findUserCustomerShareCount($arrUserId,$customerId){
    $McustomerShare = D("CustomerShare");
    load("@.Array");
    $map['status'] = 1;
    $map['user_id'] = array("in",$arrUserId);
    $map['customer_id'] = $customerId;
    $findCustomerShareCount = $McustomerShare->where($map)->count();
    return $findCustomerShareCount;
}

/**
 * 查询用户执行项目相关操作的可操作的项目数据范围*
 *
 * @param int $userId  用户ID
 * @param string $modelName  模块名称
 * @param string $actionName  操作名称
 */
function getUserProjectId($userId,$modelName="project",$actionName="index"){
    //所有自己的项目
    //所有下级部门对应项目经理的项目
    //所在项目组对应项目，且具有模块操作权限的项目
//    dump($userId);
//    dump($modelName);
//    dump($actionName);
//    
    $Mproject = D("Project");	
    $projectTb = $Mproject->getTableName();
    
	$Mdepartment = D('Department');
	$departmentTb = $Mdepartment->getTableName();
	
	$MdepartmentUser = M("DepartmentUser");
	$departmentUserTb = $MdepartmentUser->getTableName();
		
    $Mrole = D("Role");
    $roleTb = $Mrole->getTableName();
    
    $MroleUser = M("RoleUser");
    $roleUserTb = $MroleUser->getTableName();
    
    $MprojectUser = D("ProjectUser");
    $projectUserTb = $MprojectUser->getTableName();
    
    $MprojectNode = D("ProjectNode");
    $projectNodeTb = $MprojectNode->getTableName();
    
    $Muser = D("User");
    
    load("@.Array");   
    $arrProjectId = array();
    
    $modelName = strtolower($modelName);
    $actionName = strtolower($actionName);
    $nodeName = $modelName."_".$actionName;
    
    $arrMap = array('status'=>array("neq",-1));
    
    $arrCodes = getUserRoleCodes($userId);
    $userRec = $Muser->getById($userId);
    //系统管理员、所有项目
    if(in_array("gly", $arrCodes) || $userRec['account'] == 'admin'){
        $arrProject = $Mproject				
    				->where($arrMap)
    				->field("{$projectTb}.id")
    				->distinct(true)
    				->select(); 
        if($arrProject){
            $arrProjectId = array_col_values($arrProject,"id");            
        }
        return $arrProjectId;
    }
    
    $arrViewOwnerProject = array();
    if ( in_array("zjl", $arrCodes) || in_array("zxzjl", $arrCodes) || in_array("xgry", $arrCodes)) {
    	// 总经理
    	// 执行总经理
    	// 销管人员
    	// 能查看所有项目    	
        $arrViewOwnerProject = $Mproject
    				->where($arrMap)
    				->field("{$projectTb}.id")
    				->distinct(true)
    				->select();
    				
    } else if ( in_array("xzzl", $arrCodes) || in_array("yxbjl", $arrCodes) || in_array("qyjl", $arrCodes) || in_array("gjxmjl", $arrCodes) ) {
    	// 营销部经理
    	// 行政助理
    	// 区域经理
    	// 高级项目经理
    	
    	// 查看各自负责部门下的所有项目
         	
    	$xmjls = getUserDepartmnetAndChildDepartmentProjectManager($userId);
    	
    	//dump($xmjls);
    	if($xmjls){
    	    $arrXmjls = array_col_values($xmjls, "user_id");
    	    $arrMap["{$projectTb}.owner_id"] = array("in", $arrXmjls);
    	    //查询所有能查看的项目经理owner_id对应的项目    
            $arrViewOwnerProject = $Mproject
        				->where($arrMap)
        				->field("{$projectTb}.id")
        				->distinct(true)
        				->select();
    	}    	
    }				
	//dump($arrViewOwnerProject);			
	//查看的其他项目经理的项目，默认的项目相关权限是项目列表、项目查看，物业查看，材料需求列表，材料需求查看，日志列表，日志查看
	$arrNodeProject = C("DEFAULT_PROJECT_NODE") ? C("DEFAULT_PROJECT_NODE") : array("project_index",
                                                        	                        "project_view",
                                                        	                        "project_showproperty",
                                                        	                        "materialrequirement_index",
                                                        	                        "materialrequirement_view",
                                                        	                        "materialrequirement_bidlist",
                                                        	                        "log_index",
                                                        	                        "log_view",
                                                        	                  );
	                  
	//如果角色是营销部经理，角色有项目日志点评权限
	if(in_array("yxbjl", $arrCodes)){
	    array_push($arrNodeProject,'log_logcomment');
	}
	                 
	if($arrViewOwnerProject){
    	if(in_array($nodeName,$arrNodeProject)){//控制权限范围内
    	    $arrViewOwnerProjectId = array_col_values($arrViewOwnerProject,'id');
    	    $arrProjectId = array_merge($arrProjectId,$arrViewOwnerProjectId);
    	}
	}
	
	//默认自己的项目 + [如果角色是项目助理可以管理同组项目经理的项目]  用户对这些项目拥有项目的所有权限和 项目材料需求列表，材料需求查看，日志列表，日志查看权限
	$arrOwnerId = array($userId);
	//如果是项目助理，管理同组项目经理的项目
	if (in_array("xmzl", $arrCodes)) {
    	$arrManageOwner = getUserDepartmentProjectManager($userId);
    	$arrManageOwnerId = array_col_values($arrManageOwner, "user_id");
    	$arrOwnerId = array_merge($arrOwnerId, $arrManageOwnerId);
	}
	$mapOwner["{$projectTb}.status"]   = array("neq",-1);
	$mapOwner["{$projectTb}.owner_id"] = array("in", $arrOwnerId);
	
	//dump($arrOwnerId);
	//查询所有能管理的项目[项目的所有权限] 
    $arrManageOwnerProject = $Mproject
				->where($mapOwner)
				->field("{$projectTb}.id")
				->distinct(true)
				->select();
    //dump($Mproject->getLastSql());
    //dump($arrManageOwnerProject);	
	if($arrManageOwnerProject){
	    //权限控制范围内
	    if($modelName == 'project' || in_array($nodeName,$arrNodeProject) ) {
	        $arrManageOwnerProjectId = array_col_values($arrManageOwnerProject,'id');
	        $arrProjectId = array_merge($arrProjectId,$arrManageOwnerProjectId);
	    }        
	}
    
    //用户所在项目组项目            
    //查询项目相关控制节点id
    $nodeId = $MprojectNode->getFieldByName($nodeName,'id');
    if($nodeId){
        //查询具有相关控制节点权限的项目
         $sql  = " SELECT P.id FROM {$projectTb} AS P,{$projectUserTb} AS PU ";
         $sql .= " WHERE P.status <> -1 AND P.id = PU.project_id ";
         $sql .= " AND PU.user_id = {$userId} ";
         $sql .= " AND FIND_IN_SET(".$nodeId.",project_node_id )";
         //dump($sql);
         $arrProjectUserProject = $Mproject->query($sql);
         if($arrProjectUserProject){
             $arrProjectUserProjectId = array_col_values($arrProjectUserProject,'id');
             $arrProjectId = array_merge($arrProjectId,$arrProjectUserProjectId);
         }
    }
    $arrProjectId = array_values(array_unique($arrProjectId));
    //dump($arrProjectId);
    return $arrProjectId;
}

/**
 * 验证集团数据权限*
 *
 * @param int $userId
 * @param int $projectId
 * @param string $modelName
 * @param string $actionName
 */
function checkCustomerGroupAuth($userId,$id,$modelName,$actionName){
    $modelName = strtolower($modelName);
    $actionName = strtolower($actionName);
    $McustomerGroup = D("CustomerGroup");
    $Mcustomer = D("Customer");
    $customerTb = $Mcustomer->getTableName();
    $McustomerShare = D("CustomerShare");
    $customerShareTb = $McustomerShare->getTableName();
    $arrRangeType = getUserDataAccessRangeType($userId,$modelName,$actionName);
    $rangeUserId = array();
    if(in_array(1,$arrRangeType)){
        return true;
    }
    
    //数据权限控制范围内的用户
    $rangeUserId = getUserDataAccessRangeUserId($userId,$arrRangeType);
    if($rangeUserId){
        $mapComplex['customer_manager_id'] = array('in',$rangeUserId);
        $arrShareCustomerId = getUserCustomerShareCustomerId($rangeUserId);
        if($arrShareCustomerId){
            $mapComplex['id'] = array("in",$arrShareCustomerId);
            $mapComplex['_logic'] = 'or';
        }        
        $map['group_id'] = $id;
        $map['_complex'] = $mapComplex;
        $findCount = $Mcustomer->where($map)->count();
        if($findCount > 0){
            return true;
        }
    }
    return false;    
}

/**
 * 返回用户数据范围内的客户查询条件*
 *
 * @param array $arrRangeUserId
 */
function getCustomerListMapByRangeUserId($arrRangeUserId){
    //用户数据权限范围内的所属客户经理
    //$map['customer_manager_id'] = array("in",$arrRangeUserId);
    
    //数据权限范围内的客户经理的共享的客户ID
    if($arrRangeUserId){
        $arrShareCustomerId = getUserCustomerShareCustomerId($arrRangeUserId);
        if($arrShareCustomerId){
            $map['id'] = array("in",$arrShareCustomerId);
            //$map['_logic'] = 'or';
        }
    }
    
    return $map;
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


/**
 * 验证客户联系人数据权限*
 *
 * @param int $userId
 * @param int $id
 * @param string $modelName
 * @param string $actionName
 */
function checkCustomerUserAuth($userId,$id,$modelName,$actionName){
    $modelName = strtolower($modelName);
    $actionName = strtolower($actionName);
    $Mcustomer = D("Customer");
    $McustomerUser = D("CustomerUser");
    $customerTb = $Mcustomer->getTableName();
    $customerUserTb = $McustomerUser->getTableName();
    $arrRangeType = getUserDataAccessRangeType($userId,$modelName,$actionName);
    $rangeUserId = array();
    if(in_array(1,$arrRangeType)){
        return true;
    }
    
    $rangeUserId = getUserDataAccessRangeUserId($userId,$arrRangeType);
    if($rangeUserId){
        //查询用户范围内的所有用户
        $mapOr["{$customerTb}.customer_manager_id"] = array('in',$rangeUserId);
        //查询用户范围内的用户共享客户
        $arrCustomerShareCustomerId = getUserCustomerShareCustomerId($rangeUserId);
        
        if($arrCustomerShareCustomerId){
            $mapOr["{$customerTb}.id"] = array('in',$arrCustomerShareCustomerId);
            $mapOr['_logic'] = "or";
        }
        
        $map["{$customerUserTb}.id"] = $id;
        $map['_complex'] = $mapOr;
        
        $findCount = $McustomerUser->join("$customerTb ON $customerTb.id = $customerUserTb.customer_id")
                     ->where($map)->count();
        if($findCount > 0){
            return true;
        }
    }
    
    //用户项目组项目对应的客户联系人
    $arrCustomerId = getProjectUserProjectCustomerId($userId,$modelName,$actionName);
    if($arrCustomerId){
        $map["customer_id"] = array('in',$arrCustomerId);
        $map['id'] = $id;
        $findCount = $McustomerUser->where($map)->count();        
        if($findCount > 0){
            return true;
        }
    }
    
    return false;
}

/**
 * 获取用户项目组项目对应的客户数据*
 *
 * @param unknown_type $userId
 * @param unknown_type $modelName
 * @param unknown_type $actionName
 * @return unknown
 */
function getProjectUserProjectCustomerId($userId,$modelName='customeruser',$actionName='index'){
    $arrProjectUserProject = getUserProjectUserProject($userId,$modelName,$actionName);
    load("@.Array");
    $arrCustomerId = array();
    if($arrProjectUserProject){        
        $arrCustomerId = array_col_values($arrProjectUserProject,'customer_id');
    }
    return $arrCustomerId;
}

/**
 * 验证项目对应客户联系人可查看的数据权限*
 *
 * @param unknown_type $userId
 * @param unknown_type $id
 * @param unknown_type $modelName
 * @param unknown_type $actionName
 */
function checkProjectCustomerUserAuth($userId,$id,$modelName,$actionName){
    $arrCustomerId = getProjectUserProjectCustomerId($userId,$modelName,$actionName);
    $McustomerUser = D("CustomerUser");
    $map['id'] = $id;
    $map['status'] = array("neq",-1);
    $map['customer_id'] = array("in",$arrCustomerId);
    $findCount = $McustomerUser->where($map)->count();
    if($findCount > 0){
        return true;
    }
    return false;    
}

/**
 * 查询用户对当前传递的项目的相关操作是否有权限**
 *
 * @param int $userId 用户ID
 * @param int $projectId 项目ID
 * @param string $modelName
 * @param string $actionName
 */
function checkProjectAuth2($userId,$projectId,$modelName,$actionName){
//    dump($userId);
//    dump($projectId);
//    dump($modelName);
//    dump($actionName);
    //可操作的项目ID
    $arrOperateProjectId = getUserProjectId($userId,$modelName,$actionName);
    //dump($arrOperateProjectId);

    if(in_array($projectId,$arrOperateProjectId)){
        return true;
    }
    return false;
}

/**
 * 查询用户对当前传递的项目的相关操作是否有权限**
 *
 * @param int $userId 用户ID
 * @param int $projectId 项目ID
 * @param string $modelName
 * @param string $actionName
 */
function checkProjectAuth($userId,$projectId,$modelName,$actionName){    
    $modelName = strtolower($modelName);
    $actionName = strtolower($actionName);
    $Mproject = D("Project");
    $MprojectUser = D("ProjectUser");
    $projectTableName = $Mproject->getTableName();        
    $projectUserTb = $MprojectUser->getTableName();
    //查询用户对当前模块和操作的用户数据范围
    $arrRangeType = getUserDataAccessRangeType($userId,$modelName,$actionName);
    $rangeUserId = array();
    if(in_array(1,$arrRangeType)){
        return true;
    }
    
    //查询对应的项目id是否在对应的用户数据范围内,适用于配置判断
    $rangeUserId = getUserDataAccessRangeUserId($userId,$arrRangeType);
    if($rangeUserId){
       $map["owner_id"] = array("in", $rangeUserId);
       $map['id'] = array("eq",$projectId);
       $findCount = $Mproject->where($map)->count();
       if($findCount > 0 ){
           return true;
       }
    }
    
    //查询用户对应的项目组分配了对应项目权限的项目
    $arrProjectUserProject = getUserProjectUserProject($userId,$modelName,$actionName);
    if($arrProjectUserProject){
        load("@.Array");
        $arrProjectUserProjectId = array_col_values($arrProjectUserProject,"id");
        if(in_array($projectId,$arrProjectUserProjectId)){
            return true;
        }
    }
    return false;
}

function checkLogAuth($userId,$logId,$modelName,$actionName){
    $modelName = strtolower($modelName);
    $actionName = strtolower($actionName);
    $nodeName = $modelName."_".$actionName;
    load("@.Array");
    $Muser = D("User");
    
    //查询用户对当前模块和操作的用户数据范围
    $arrRangeType = getUserDataAccessRangeType($userId,$modelName,$actionName);
    $rangeUserId = array();
    if(in_array(1,$arrRangeType)){
        return true;
    }
    
    $MLog = D("Log");
    $logTb = $MLog->getTableName();
    //查询对应的日志id是否在对应的用户数据范围内,适用于配置判断
    $rangeUserId = getUserDataAccessRangeUserId($userId,$arrRangeType);
    if($rangeUserId){
       $map["owner_id"] = array("in", $rangeUserId);
       $map['id'] = array("eq",$logId);
       $findCount = $MLog->where($map)->count();
       if($findCount > 0 ){
           return true;
       }
    }
    
    //所在项目组分配日志相关权限
    $Mproject = D("Project");
    $projectTb = $Mproject->getTableName();
    $MprojectUser = D("ProjectUser");
    $projectUserTb = $MprojectUser->getTableName();
    $MprojectNode = D("ProjectNode");
    $projectNodeTb = $MprojectNode->getTableName();    
    
    //查询日志相关控制节点id
    $nodeId = $MprojectNode->getFieldByName($nodeName,'id');
    if($nodeId){
        //查询具有相关控制节点权限的项目日志
         $sql  = " SELECT L.id FROM {$projectTb} AS P,{$projectUserTb} AS PU, {$logTb} AS L ";
         $sql .= " WHERE P.status <> -1 AND P.id = PU.project_id "; 
         $sql .= " AND PU.user_id = {$userId} ";
         $sql .= " AND FIND_IN_SET(".$nodeId.",project_node_id )";
         $sql .= " AND L.status <> -1 AND P.id = L.project_id ";
         $sql .= " AND L.id = {$logId} ";
         $findLog = $MLog->query($sql);
         if($findLog){
             return true;
         }
    }
    
    //日志参与人员
    $MlogUser = D("LogUser");
    $logUserTb = $MlogUser->getTableName();
    $mapLogUser["{$logTb}.status"] = array("neq",-1);
    $mapLogUser["{$logTb}.id"] = array("eq",$logId);
    $mapLogUser["{$logUserTb}.user_id"] = array("eq",$userId);
    $findLogCount = $MLog->join("$logUserTb ON {$logTb}.id = {$logUserTb}.log_id")
                    ->where($mapLogUser)
                    ->count();
    //dump($MLog->getLastSql());  
    if($findLogCount > 0){
        if(in_array($nodeName,$arrLogNode)){
            return true;
        }
    }
    
    //日志移交历史中的原日志拥有者可以查看移交的日志详情权限
    $MlogHandoverHistory = D("LogHandoverHistory");
    if($nodeName == 'log_view'){
        $mapLogHandoverHistory = array('old_owner_id'=>$userId,'log_id'=>$logId);
        $findLogCount = $MlogHandoverHistory->where($mapLogHandoverHistory)->count();
        if($findLogCount > 0){
            return true;
        }
    }
    return false;
}

//验证用户对某条日志数据的操作权限
function checkLogAuth2($userId,$logId,$modelName,$actionName){
    //系统管理员，所有权限
    //自身的日志，系统分配的操作权限
    //自身角色看到的下级部门相关角色的日志，默认拥有日志的列表，查看权限，如果角色是yxbjl，拥有日志点评权限。    
    //项目组成员，项目组对应的项目，且分配了项目日志相关操作权限    
    //日志参与人员，默认有日志的列表，查看权限
    
    $modelName = strtolower($modelName);
    $actionName = strtolower($actionName);
    $nodeName = $modelName."_".$actionName;
    load("@.Array");
    $Muser = D("User");
    
    //用户角色与基本信息
    $arrCodes = getUserRoleCodes($userId);
    $userRec  = $Muser->getById($userId);
    
    //系统管理员、返回true
    if(in_array("gly", $arrCodes) || $userRec['account'] == 'admin'){
        return true;
    }    
    
    $MLog = D("Log");
    $logTb = $MLog->getTableName();   
    $mapOwnerLog = array('status'=>array("eq",1),
                         'owner_id'=>array('eq',$userId),
                         'id'=>array('eq',$logId),
                   );
    $findLogCount = $MLog->where($mapOwnerLog)->count();
    if($findLogCount > 0){
        return true;
    }
    
    
    //默认上级部门相关角色查看下级部门项目经理的日志权限  日志的列表，日志查看
    $arrLogNode = C("DEFAULT_LOG_NODE") ? C("DEFAULT_LOG_NODE") : array('log_index','log_view');    
        
    if ( in_array("zjl", $arrCodes) || in_array("zxzjl", $arrCodes) || in_array("xgry", $arrCodes)) {
    	// 总经理
    	// 执行总经理
    	// 销管人员
    	// 能查看所有日志
        if(in_array($nodeName,$arrLogNode)){
            return true;
        }
    				
    } else if ( in_array("xzzl", $arrCodes) || in_array("yxbjl", $arrCodes) || in_array("qyjl", $arrCodes) || in_array("gjxmjl", $arrCodes) ) {
    	// 营销部经理
    	// 行政助理
    	// 区域经理
    	// 高级项目经理
    	
    	// 查看各自负责部门下的所有项目         	
    	$xmjls = getUserDepartmnetAndChildDepartmentProjectManager($userId);    	
    	//dump($xmjls);
    	if($xmjls){
    	    $xmjlsId = array_col_values($xmjls,'user_id');
    	    $mapViewOwnerLog = array('status'=>array("eq",1),
                                    'owner_id'=>array('in',$xmjlsId),
                                    'id'=>array('eq',$logId),
                               );
            $findLogCount = $MLog->where($mapViewOwnerLog)->count();
            if($findLogCount > 0){
                $arrTempLogNode = $arrLogNode;
                if( in_array("yxbjl", $arrCodes) ){ //营销部经理拥有下级部门项目经理的日志点评权限
                    array_push($arrTempLogNode,'log_logcomment');                    
                }
                if(in_array($nodeName,$arrTempLogNode)){
                    return true;
                }
            }
    	}
    } else if(in_array("xmzl", $arrCodes)){ //项目助理查看同部门项目经理日志
        $xmjls = getUserDepartmentProjectManager($userId);
    	if($xmjls){
    	    $xmjlsId =  array_col_values($xmjls,'user_id');
    	    $mapViewOwnerLog = array('status'=>array("eq",1),
                                    'owner_id'=>array('in',$xmjlsId),
                                    'id'=>array('eq',$logId),
                               );
            $findLogCount = $MLog->where($mapViewOwnerLog)->count();
            if($findLogCount > 0){
                if(in_array($nodeName,$arrLogNode)){
                    return true;
                }
            }
    	}
    }
    
    $Mproject = D("Project");
    $projectTb = $Mproject->getTableName();
    $MprojectUser = D("ProjectUser");
    $projectUserTb = $MprojectUser->getTableName();
    $MprojectNode = D("ProjectNode");
    $projectNodeTb = $MprojectNode->getTableName();    
    
    //查询日志相关控制节点id
    $nodeId = $MprojectNode->getFieldByName($nodeName,'id');
    if($nodeId){
        //查询具有相关控制节点权限的项目日志
         $sql  = " SELECT L.id FROM {$projectTb} AS P,{$projectUserTb} AS PU, {$logTb} AS L ";
         $sql .= " WHERE P.status <> -1 AND P.id = PU.project_id "; 
         $sql .= " AND PU.user_id = {$userId} ";
         $sql .= " AND FIND_IN_SET(".$nodeId.",project_node_id )";
         $sql .= " AND L.status <> -1 AND P.id = L.project_id ";
         $sql .= " AND L.id = {$logId} ";
         $findLog = $MLog->query($sql);
         if($findLog){
             return true;
         }
    }
    
    
    $MlogUser = D("LogUser");
    $logUserTb = $MlogUser->getTableName();
    $mapLogUser["{$logTb}.status"] = array("neq",-1);
    $mapLogUser["{$logTb}.id"] = array("eq",$logId);
    $mapLogUser["{$logUserTb}.user_id"] = array("eq",$userId);
    $findLogCount = $MLog->join("$logUserTb ON {$logTb}.id = {$logUserTb}.log_id")
                    ->where($mapLogUser)
                    ->count();
    //dump($MLog->getLastSql());  
    if($findLogCount > 0){
        if(in_array($nodeName,$arrLogNode)){
            return true;
        }
    }
    
    //日志移交历史中的原日志拥有者可以查看移交的日志详情权限
    $MlogHandoverHistory = D("LogHandoverHistory");
    if($nodeName == 'log_view'){
        $mapLogHandoverHistory = array('old_owner_id'=>$userId,'log_id'=>$logId);
        $findLogCount = $MlogHandoverHistory->where($mapLogHandoverHistory)->count();
        if($findLogCount > 0){
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


//验证方案操作权限
function checkScheme($modelName=MODULE_NAME,$actionName=ACTION_NAME,$id=""){
    
}

/**
 * 查询分期*
 *
 * @return unknown
 */
function getPeriod(){
    $Period = D("Period");
    $arrPeriod = $Period->where(array("status"=>1))->order("id ASC")->select();    
    return $arrPeriod;
}

/**
 *项目性质 *
 *
 */
function getProjectNature(){
    return array(1=>"合营类",2=>"投资类",3=>"商建类",4=>"政建类",5=>"外拓类");
}

//项目业务归属
function getProjectBelongTo(){
    return array(1=>"业务",2=>"采购");
}

//项目类型
function getProjectType(){
    return array(1=>"潜在重点项目",2=>"重点项目",3=>"一般项目",4=>"无需求项目");
}

//项目服务方式
function getProjectSeviceType(){
    return array(1=>"自行服务",2=>"合作服务");   
}

//采购主体
function getPurchaser(){
    $arrPurchaser = D("Purchaser")->where("status=1")->order("sort ASC")->select();
    return $arrPurchaser;
}

//采购类型
function getPurchaseType(){
    $arrPurchaseType = array(array("id"=>1,"name"=>"招投标"),array("id"=>2,"name"=>"直接采购"));
    return $arrPurchaseType;
}

//采购状态
function getPurchaseStatus(){
    $arrPurchaseStatus = D("PurchaseStatus")->where("status=1")->order("sort ASC")->select();
    return $arrPurchaseStatus;
}

//销售里程碑
function getSaleStage(){
    $arrSaleStage = D("SaleStage")->where("status=1")->order("sort ASC")->select();
    return $arrSaleStage;
}

//销售动作
function getSaleAction(){
    $arrSaleAction = D("SaleAction")->where("status=1")->order("sort ASC")->select();
    return $arrSaleAction;
}

//装修类型
function getPropertyDecorate(){
    $arrDecorate = D("Decorate")->where("status=1")->order("sort ASC")->select();
    return $arrDecorate ;
}

//工程阶段
function getPropertyStage(){
    $arrPropertyStage = D("PropertyStage")->where("status=1")->order("sort ASC")->select();
    return $arrPropertyStage ;
}

/**
 *销售去化率 *
 *
 */
function getPropertySaleRate(){
    $arrPropertySaleRate = array(
        1=>'未开盘',
        2=>'10%',
        3=>'20%',
        4=>'30%',
        5=>'40%',
        6=>'50%',
        7=>'60%',
        8=>'70%',
        9=>'80%',
        10=>'90%',
        11=>'100%'
    );
    return $arrPropertySaleRate;
}

/**
 * 物业屋面结构*
 *
 */
function getPropertyRoofStructure(){
    return array(
        1=>'坡屋面',
        2=>'平屋面'
    );
}

/**
 * 精装标准口径*
 *
 * @return array
 */
function getPropertyRefinedDecorationCaliber(){
     return array(
        1=>'套内面积',
        2=>'建筑面积'
    );
}



//态度
function getManner(){
    $arrManner = D("Manner")->where("status=1")->order("sort ASC")->select();
    return $arrManner ;
}

/**
 * 获取日志点评状态*
 *
 * @return array
 */
function getLogCommentStatus(){
   return array(1=>"未点评",2=>"已点评",3=>"未回馈",4=>"已回馈");
}

/**
 * 获取招标组角色*
 *
 * @return array
 */
function getCustomerBidRole(){
    $arrBidRole = D("CustomerBidRole")->where("status=1")->order("sort ASC")->select();
    return $arrBidRole ;
}

//显示项目性质
function showProjectNature($project_nature){    
    $info = "";
    $arrProjectNature = getProjectNature();
    if(key_exists($project_nature,$arrProjectNature)){
        $info = $arrProjectNature[$project_nature];
    }
    return $info;
}

//显示项目业务归属
function showProjectBelongTo($project_belongto){
    
    $info = "";
    $arrProjectBelongTo = getProjectBelongTo();
    if(key_exists($belongto,$arrProjectBelongTo)){
        $info = $arrProjectBelongTo[$project_belongto];
    }
    return $info;
}

//显示项目类型
function showProjectType($project_type){
    $info = "";
    $arrProjectType = getProjectType();
    if(key_exists($project_type,$arrProjectType)){
        $info = $arrProjectType[$project_type];
    }
    return $info;
}

//项目服务方式
function showProjectSeviceType($project_service_type){    
    $info = "";
    $arrProjectServiceType = getProjectSeviceType();
    if(key_exists($project_service_type,$arrProjectServiceType)){
        $info = $arrProjectServiceType[$project_service_type];
    }
    return $info;
}

//显示采购主体
function showPurchaser($purchaser_id) {
    $info = "";
    $arrPurchaser = getPurchaser();    
    load("@.Array");
    if($arrPurchaser){
       $arrPurchaserToMapId = array_to_hashmap($arrPurchaser,'id',"name") ;
       if(key_exists($purchaser_id,$arrPurchaserToMapId)){
           $info = $arrPurchaserToMapId[$purchaser_id];
       }
    }    
	return $info;
}

//显示采购类型
function showPurchaseType($purchaserType) {
	switch ($purchaserType) {
		case 1 :
			$info = '招投标';
			break;
		case 2 :
			$info = '直接采购';
			break;			
	}
	return $info;
}

//显示采购状态
function showPurchaseStatus($purchase_status_id) {
	$info = "";
    $arrPurchaseStatus = getPurchaseStatus();    
    load("@.Array");
    if($arrPurchaseStatus){
       $arrPurchaseStatusToMapId = array_to_hashmap($arrPurchaseStatus,'id',"name") ;
       if(key_exists($purchase_status_id,$arrPurchaseStatusToMapId)){
           $info = $arrPurchaseStatusToMapId[$purchase_status_id];
       }
    }  
	return $info;	
}


//显示销售里程碑
function showSaleStage($sale_stage_id) {
	$info = "";
    $arrSaleStage = getSaleStage();
    load("@.Array");
    if($arrSaleStage){
       $arrSaleStageToMapId = array_to_hashmap($arrSaleStage,'id',"name") ;
       if(key_exists($sale_stage_id,$arrSaleStageToMapId)){
           $info = $arrSaleStageToMapId[$sale_stage_id];
       }
    }
	return $info;
}

//显示物业装修类型
function showPropertyDecorate($decorate_id){
    $info = "";
    $arrPropertyDecorate = getPropertyDecorate();
    load("@.Array");
    if($arrPropertyDecorate){
       $arrPropertyDecorateToMapId = array_to_hashmap($arrPropertyDecorate,'id',"name") ;
       if(key_exists($decorate_id,$arrPropertyDecorateToMapId)){
           $info = $arrPropertyDecorateToMapId[$decorate_id];
       }
    }
	return $info;
}

//显示物业工程阶段
function showPropertyStage($stage_id){
    $info = "";
    $arrPropertyStage = getPropertyStage();
    load("@.Array");
    if($arrPropertyStage){
       $arrPropertyStageToMapId = array_to_hashmap($arrPropertyStage,'id',"name") ;
       if(key_exists($stage_id,$arrPropertyStageToMapId)){
           $info = $arrPropertyStageToMapId[$stage_id];
       }
    }
	return $info;
}

/**
 * 显示销售去化率*
 *
 * @param int $property_sale
 * @return string
 */
function showPropertySaleRate($property_sale){
    $info = "";
    $arrPropertySaleRate = getPropertySaleRate();
    if(key_exists($property_sale,$arrPropertySaleRate)){
        $info = $arrPropertySaleRate[$property_sale];
    }
    return $info;
}
/**
 * 显示物业屋面结构*
 *
 * @param int $property_roof_structure
 * @return string
 */
function showPropertyRoofStructure($property_roof_structure){
    $info = "";
    $arrPropertyRoofStructure = getPropertyRoofStructure();
    if(key_exists($property_roof_structure,$arrPropertyRoofStructure)){
        $info = $arrPropertyRoofStructure[$property_roof_structure];
    }
    return $info;
}

/**
 * 显示精装标准口径*
 *
 * @param int $property_roof_structure
 * @return string
 */
function showPropertyRefinedDecorationCaliber($property_refined_decoration_caliber){
    $info = "";
    $arrPropertyRefinedDecorationCaliber = getPropertyRefinedDecorationCaliber();
    if(key_exists($property_refined_decoration_caliber,$arrPropertyRefinedDecorationCaliber)){
        $info = $arrPropertyRefinedDecorationCaliber[$property_refined_decoration_caliber];
    }
    return $info;
}


//显示态度
function showManner($manner_id){
    $info = "";
    $arrManner = getManner();
    load("@.Array");
    if($arrManner){
       $arrMannerToMapId = array_to_hashmap($arrManner,'id',"name") ;
       if(key_exists($manner_id,$arrMannerToMapId)){
           $info = $arrMannerToMapId[$manner_id];
       }
    }
	return $info;
}

/**
 * 显示招标组角色*
 *
 * @param int $purchaser_id
 * @return unknown
 */
function showCustomerBidRole($id){
    $info = "";
    $arrBidRole = getCustomerBidRole();
    load("@.Array");
    if($arrBidRole){
       $arrBidRoleToMapId = array_to_hashmap($arrBidRole,'id',"role_title") ;
       if(key_exists($id,$arrBidRoleToMapId)){
           $info = $arrBidRoleToMapId[$id];
       }
    }
	return $info;
}

/**
 * 显示日志点评状态*
 *
 * @param int $commentStatus
 * @return string
 */
function showLogCommentStatus($commentStatus){
    $info = "";
    $arrLogCommentStatus = getLogCommentStatus();
    if(key_exists($commentStatus,$arrLogCommentStatus)){
        $info = $arrLogCommentStatus[$commentStatus];        
    }
    return $info;
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

function getReportStatus($status, $imageShow = true) {
	switch ($status) {
		case 1 :
			$showText = '待报备';			
			break;
		case 2 :
			$showText = '待审核';			
			break;
		case 3 :
			$showText = '待修改';			
			break;
		case 4 :
		    $showText = '审核通过';			
			break;
		case 5 :
		    $showText = '审核失败';			
			break;
		default :
			break;

	}
	return $showText;
}

function getAllocationType($allocationType, $imageShow = true) {
	switch ($allocationType) {
		case 1 :
			$showText = '系统创建';			
			break;
		case 2 :
			$showText = '客户移交';			
			break;
		case 3 :
			$showText = '客户再分配';			
			break;			
		default :
			break;

	}
	return $showText;
}

/**
 * 过滤和排序所有分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param   int     $dept_id     上级分类ID
 * @param   array   $arr        含有所有分类的数组
 * @param   int     $level      级别
 * @return  void
 */
function dept_options($spec_dept_id, $arr)
{
    static $dept_options = array();

    if (isset($dept_options[$spec_dept_id]))
    {
        return $dept_options[$spec_dept_id];
    }
    if (!isset($dept_options[0]))
    {
          $options =  array();
//        $data = read_static_cache('dept_option_static');
//        if ($data === false)
//        {               
            	
            	load("@.Array");
            	$treeArr = array();
            	array_to_tree2($arr,$treeArr,'id','pid');
            	if($treeArr){
            	    $options = array_to_hashmap($treeArr,"id");
            	}
//            if (count($options) <= 2000)
//            {
//                write_static_cache('dept_option_static', $options);
//            }
//        }
//        else
//        {
//            $options = $data;
//        }
          $dept_options[0] = $options;
    }
    else 
    {
        $options = $dept_options[0];
    }
    
    if (!$spec_dept_id)
    {
        return $options;
    }
    else
    {
        if (empty($options[$spec_dept_id]))
        {
            return array();
        }

        $spec_dept_id_level = $options[$spec_dept_id]['level'];

        foreach ($options AS $key => $value)
        {
            if ($key != $spec_dept_id)
            {
                unset($options[$key]);
            }
            else
            {
                break;
            }
        }

        $spec_dept_id_array = array();
        foreach ($options AS $key => $value)
        {
            if (($spec_dept_id_level == $value['level'] && $value['id'] != $spec_dept_id) ||
                ($spec_dept_id_level > $value['level']))
            {
                break;
            }
            else
            {
                $spec_dept_id_array[$key] = $value;
            }
        }
        $dept_options[$spec_dept_id] = $spec_dept_id_array;
        return $spec_dept_id_array;
    }
    
    
}

/**
 * 获得指定部门下的子部门的数组
 *
 * @access  public
 * @param   int     $dept_id     部门的ID
 * @param   int     $selected   当前选中部门的ID
 * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param   int     $level      限定返回的级数。为0时返回所有级数
 * @return  mix
 */
function dept_cat_list($dept_id = 0, $selected = 0, $re_type = true, $removeTop=false,$level = 0)
{
    static $res = NULL;

    if ($res === NULL)
    {
        //$data = read_static_cache('art_cat_pid_releate');
        //if ($data === false)
        //{
            $Department = D('Department');
            $res = $Department->field('id,name,pid,level,sort,status')->order('sort DESC')->select();
            //write_static_cache('art_cat_pid_releate', $res);
        //}
        //else
        //{
        //    $res = $data;
        //}
    }
    
    if (empty($res) == true)
    {
        return $re_type ? '' : array();
    }
    //dump()
    $options = dept_options($dept_id, $res); // 获得指定部门下的子部门的数组
    
    if($removeTop && $options){
         array_shift($options);
    }
    /* 截取到指定的缩减级别 */
    if ($level > 0)
    {
        if ($dept_id == 0)
        {
            $end_level = $level;
        }
        else
        {
            $first_item = reset($options); // 获取第一个元素
            $end_level  = $first_item['level'] + $level;
        }

        /* 保留level小于end_level的部分 */
        foreach ($options AS $key => $val)
        {
            if ($val['level'] >= $end_level)
            {
                unset($options[$key]);
            }
        }
    }
    
    $pre_key = 0;
    foreach ($options AS $key => $value)
    {
        $options[$key]['has_children'] = 1;
        if ($pre_key > 0)
        {
            if ($options[$pre_key]['cat_id'] == $options[$key]['parent_id'])
            {
                $options[$pre_key]['has_children'] = 1;
            }
        }
        $pre_key = $key;
    }
    
    if ($re_type == true)
    {
        $select = '';        
        foreach ($options AS $var)
        {
            $select .= '<option value="' . $var['id'] . '" ';
            //$select .= ' cat_type="' . $var['cat_type'] . '" ';
            $select .= ($selected == $var['id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var['level'] > 0)
            {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['name'])) . '</option>';
        }

        return $select;
    }
    else
    {
        foreach ($options AS $key => $value)
        {
            //$options[$key]['url'] = build_uri('dept_cat', array('acid' => $value['id']), $value['cat_name']);
        }
        return $options;
    }
}


function loopChildDepartments($arrs,$Mdepartment){
    foreach ($arrs as $key=>$node) {
   		$chilenMap = array("pid"=>array("eq", $node['id']),
   				           "status"=>array("eq", "1"));
   		$childen = $Mdepartment->where($chilenMap)->order("sort asc")->select();
   		if ($childen) {
   			$arrs[$key]['childen'] = loopChildDepartments($childen, $Mdepartment);
   		}
   	}
   	return $arrs;
}

/**
* 查询部门以及部门下的所有子部门数据*
*
* @param array $arrs
* @param object $Mdepartment 数据表实例
* @return array
*/
function getDepartmentsTree($pid=0) {    
    $Department = D('Department');
    $map['status'] = 1;
    $map['pid'] = $pid;
	$arrDepartments = $Department->where($map)->field('id,name,pid,level,sort,status')->order('sort DESC')->select();	
	$arrDepartments = loopChildDepartments($arrDepartments, $Department);
	return $arrDepartments;
}


/**
 * 获取客户类型*
 *
 * @return unknown
 */
function getCustomerType(){
    return array(
        '1'=>'绿城',
        '2'=>'蓝城',
        '3'=>'鼎益',
        '4'=>'佳园',
        '5'=>'发展',
        '6'=>'融绿',
        '7'=>'中投',
        '8'=>'外拓会员'
    );
}

/**
 * 显示客户类型*
 *
 * @param int $type
 */
function showCustomerType($type){
    $text = "";
    $arrCustomerType = getCustomerType();
    if(key_exists($type,$arrCustomerType)){
        $text = $arrCustomerType[$type];
    }
    return $text;
}

/**
 * 获取客户分类*
 *
 * @return unknown
 */
function getCustomerCategory(){
    return array(
        '1'=>'公司',
        '2'=>'个人',        
    );
}

/**
 * 显示客户分类*
 *
 * @param int $type
 */
function showCustomerCategory($category){
    $text = "";
    $arrCustomerCategory = getCustomerCategory();
    if(key_exists($category,$arrCustomerCategory)){
        $text = $arrCustomerCategory[$category];
    }
    return $text;
}


/**
 * 获取科目类型*
 *
 * @return unknown
 */
function getSubjectType(){
    return array(
        '1'=>'文本',
        '2'=>'选择',
        '3'=>'综合计算'        
    );
}

/**
 * 显示科目类型*
 *
 * @param int $type
 */
function showSubjectType($subjectType){
    $text = "";
    $arrSubjectType = getSubjectType();
    if(key_exists($subjectType,$arrSubjectType)){
        $text = $arrSubjectType[$subjectType];
    }
    return $text;
}
function getAllDepartMentIds($arrs){
    $return = array();
    foreach($arrs as $department){
        $return[] = $department['id'];
        if(isset($department['childen'])){
            $ret = getAllDepartMentIds($department['childen']);
            $return = array_merge($ret,$return);
        }
    }
    return $return;
    
}
/**
 * 获取客户签约状态*
 *
 * @return unknown
 */
function getSignStatus(){
    return array(
        '0'=>'未签约',
        '1'=>'已签约',
        '2'=>'合约期已过'
    );
}

/**
 * 显示客户签约状态*
 *
 * @param int $type
 */
function showSignStatus($sign_status){
    $text = "";
    $arrSignStatus = getSignStatus();
    if(key_exists($sign_status,$arrSignStatus)){
        $text = $arrSignStatus[$sign_status];
    }
    return $text;
}

/**
 * 获取客户等级*
 *
 * @return unknown
 */
function getCustomerLevel(){
    return array(
        '1'=>'普通',
        '2'=>'高级',
        '3'=>'尊享'
    );
}

/**
 * 显示客户签约状态*
 *
 * @param int $type
 */
function showCustomerLevel($level){
    $text = "";
    $arrCustomerLevel = getCustomerLevel();
    if(key_exists($level,$arrCustomerLevel)){
        $text = $arrCustomerLevel[$level];
    }
    return $text;
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

/**
 * 获取事业部机会确认状态*
 *
 * @return unknown
 */
function getMaterialConfirmStatus(){
    return array(
        '0'=>'未确认',
        '1'=>'有效',
        '2'=>'无效',
        '3'=>'修改'
    );
}

/**
 * 显示事业部机会确认状态*
 *
 * @return unknown
 */
function showConfirmStatus($status){
    $text = "";
    $arrConfirmStatus = getMaterialConfirmStatus();
    if(key_exists($status,$arrConfirmStatus)){
        $text = $arrConfirmStatus[$status];
    }
    return $text;
}

function getFlowPassingDepartmentLevel(){
    return array("0"=>"无部门限制",
                 "1"=>"流程发起者同部门用户",
                 "2"=>"流程发起者上级部门用户",
                 "3"=>"流程发起者同部门以及上级部门用户",
    );
}
?>