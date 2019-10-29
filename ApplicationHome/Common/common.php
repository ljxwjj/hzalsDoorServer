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
    $__PUBLIC__ = __ROOT__.'/Public';
    switch ($status) {
        case 0 :
            $showText = '禁用';
            $showImg = "<IMG SRC='$__PUBLIC__/Images/disable.png' WIDTH='15' HEIGHT='15' BORDER='0' ALT='禁用'>";
            break;
        case 2 :
            $showText = '待审';
            $showImg = "<IMG SRC='$__PUBLIC__/Images/prected.gif' WIDTH='15' HEIGHT='15' BORDER='0' ALT='待审'>";
            break;
        case - 1 :
            $showText = '删除';
            $showImg = "<IMG SRC='$__PUBLIC__/Images/delete.png' WIDTH='15' HEIGHT='15' BORDER='0' ALT='删除'>";
            break;
        case 1 :
        default :
            $showText = '正常';
            $showImg = "<IMG SRC='$__PUBLIC__/Images/ok.png' WIDTH='15' HEIGHT='15' BORDER='0' ALT='正常'>";

    }
    return ($imageShow === true) ?  $showImg  : $showText;
}

function getUserStatus($status, $imageShow = true) {
    $__PUBLIC__ = __ROOT__.'/Public';
    switch ($status) {
        case 0 :
            $showText = '未激活';
            $showImg = "<IMG SRC='$__PUBLIC__/Images/disable.png' WIDTH='15' HEIGHT='15' BORDER='0' ALT='禁用'>";
            break;
        case - 1 :
            $showText = '删除';
            $showImg = "<IMG SRC='$__PUBLIC__/Images/delete.png' WIDTH='15' HEIGHT='15' BORDER='0' ALT='删除'>";
            break;
        case 1 :
        default :
            $showText = '正常';
            $showImg = "<IMG SRC='$__PUBLIC__/Images/ok.png' WIDTH='15' HEIGHT='15' BORDER='0' ALT='正常'>";

    }
    return ($imageShow === true) ?  $showImg  : $showText;
}

function getCompanyStatus($status, $imageShow = true) {
    $__PUBLIC__ = __ROOT__.'/Public';
    switch ($status) {
        case - 1 :
            $showText = '删除';
            $showImg = "<IMG SRC='$__PUBLIC__/Images/delete.png' WIDTH='15' HEIGHT='15' BORDER='0' ALT='删除'>";
            break;
        default :
            $showText = '正常';
            $showImg = "<IMG SRC='$__PUBLIC__/Images/ok.png' WIDTH='15' HEIGHT='15' BORDER='0' ALT='正常'>";

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

function showSex($sex) {
    switch ($sex) {
        case 0 :
            return '女';
        case 1:
            return '男';
    }
}

function showDoorController($controllerName, $ip) {
    if ($ip) {
        $controllerName = "$controllerName ($ip)";
    }
    return $controllerName;
}

function showRequestUseStatus($status, $id) {
    switch ($status) {
        case 0 :
            $info = '<a href="javascript:resume(\'id\',' . $id . ')" title="通过">通过</a>';
            $info .= ' <a href="javascript:forbid(\'id\',' . $id . ')" title="拒绝">拒绝</a>';
            break;
        case 1 :
            $info = '';
            break;
        case - 1 :
            $info = '<a href="javascript:forbid(\'id\',' . $id . ')" title="通过">通过</a>';
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



//验证方案操作权限
function checkScheme($modelName=MODULE_NAME,$actionName=ACTION_NAME,$id=""){

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

function generate_code($length = 6) {
    return substr(str_shuffle("012345678901234567890123456789"), 0, $length);
}

function doSendSms($mobile, $smsCode, $operation) {
    Vendor('api_sdk/smsUtil', COMMON_PATH . 'Vendor/', '.php');
    return sendSms($mobile, $smsCode, $operation);
}


function getUserDoors($user_id = null) {
    if (!$user_id) {
        $user_id = session(C('USER_AUTH_KEY'));
    }
    $user = M('User')->find($user_id);
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

function getUserDoors2($user) {
    $role_id = M('AuthRoleUser')->where(array('user_id'=>$user['id']))->getField('role_id');

    if ($role_id > 21) { // > 21即非管理员用户
        $joinStr = "door_controller on door_controller.id=user_door.controller_id and door_controller.product_type=2";
        $userDoors = M('UserDoor')->join($joinStr)->where(array('user_id'=>$user['id']))->select();
        foreach ($userDoors as $door) {
            $doorMap[$door['controller_id']][$door['door_id']] = 1;
        }

        $department = M('UserDepartment')->where(array('user_id'=>$user['id']))
            ->getField('department_id');
        while ($department) {
            $joinStr = "door_controller on door_controller.id=department_door.controller_id and door_controller.product_type=2";
            $departmentDoors = M('DepartmentDoor')->join($joinStr)->where(array('department_id'=>$department))
                ->select();
            foreach ($departmentDoors as $door) {
                $doorMap[$door['controller_id']][$door['door_id']] = 1;
            }
            $department = M('Department')->where(array('id'=>$department))
                ->getField('pid');
        }
    } else {
        $map = array('status'=> 0, 'company_id'=>$user['company_id'], 'product_type'=>2);
        $controllers = D('DoorController')->where($map)->select();
        foreach ($controllers as $value) {
            for ($j = 0; $j < $value['door_count']; $j++) {
                $doorMap[$value['id']][$j] = 1;
            }
        }
    }

    return $doorMap;
}

function updateUserCards($userId, $cardNumber, $controllerDoors) {
    $model = M("DoorControllerUserCard");
    $allCards = $model->where(array('user_id'=>$userId))->select();
    $oldCardNumber = M("User")->where("id=$userId")->getField("card_number");

    $result = true;
    $model->startTrans();
    foreach ($allCards as $cardItem) {
        $controllerId = $cardItem["controller_id"];
        $allCardMap[$cardItem['card_number']][$controllerId] = $cardItem["doors"];

        if (strcmp($cardItem['card_number'], $cardNumber) === 0) {
            if (is_array($controllerDoors[$controllerId])) {
                $doosStr = implode(",", $controllerDoors[$controllerId]);
                if (strcmp($doosStr, $cardItem["doors"]) !== 0) {// 权限发生改变的
                    $cardItem["doors"] = $doosStr;
                    $cardItem["last_sync_time"] = 0;
                    $cardItem["status"] = 0;
                    $result = saveCardItem($cardItem, $model);
                }
            } else {
                if (!empty($cardItem["doors"])) {// 被删除的权限同步
                    $cardItem["doors"] = "";
                    $cardItem["last_sync_time"] = 0;
                    $cardItem["status"] = 0;
                    $result = saveCardItem($cardItem, $model);
                }
            }
        } else { // 卡号发生改变
            if (strlen($cardItem["doors"])) {
                $cardItem["doors"] = "";
                $cardItem["last_sync_time"] = 0;
                $cardItem["status"] = 0;
                $result = saveCardItem($cardItem, $model);
            }
        }
        if (!$result) break;
    }
    if ($result) {
        if (!empty($cardNumber)) {// 新增名单
            foreach ($controllerDoors as $controllerId=>$doors) {
                if (!isset($allCardMap[$cardNumber][$controllerId])) {
                    $cardItem = array('controller_id'=>$controllerId, 'user_id'=>$userId, 'card_number'=>$cardNumber, 'doors'=>implode(",", $doors));
                    $cardItem["last_sync_time"] = 0;
                    $cardItem["status"] = 0;
                    $result = $model->add($cardItem);
                }
                if (!$result) break;
            }
        }
        if ($result && strcmp($oldCardNumber, $cardNumber) !== 0) {// 修改用户信息
            $result = $model->execute("update user set card_number='%s' where id=%d", $cardNumber, $userId);
        }
    }
    if ($result) {
        $model->commit();
    } else {
        $model->rollback();
    }
    return $result;
}

function merge_door($a1, $a2) {
    $result = array();
    foreach ($a1 as $item) {
        $controllerId = $item['controller_id'];
        $doorId = $item['door_id'];
        $result[$controllerId][] = $doorId;
    }
    foreach ($a2 as $item) {
        $controllerId = $item['controller_id'];
        $doorId = $item['door_id'];
        if (!in_array($doorId, $result[$controllerId])) {
            $result[$controllerId][] = $doorId;
        }
    }
    return $result;
}

function checkUserCardByDepartment($departmentId) {// 当部门权限发生变化时
    $departmentUsers = M("UserDepartment")->where(array('department_id'=>$departmentId))->getField("user_id", true);

    // 循环梳理部门下所有用户卡片授权信息
    foreach ($departmentUsers as $userId) {
        checkUserCardsByUser($userId);
    }
}

function checkUserCardByCompany($companyId) {
    $companyUsers = D("User")->where(array("company_id"=>$companyId))->getField("id", true);

    // 循环梳理公司下所有用户卡片授权信息
    foreach ($companyUsers as $userId) {
        checkUserCardsByUser($userId);
    }
}

function checkUserCardsByUser($userId) {// 当用户权限发生变化时
    $user = M("User")->where("id=$userId")->find();
    $cardNumber = $user["card_number"];
    $result = true;
    $model = M("DoorControllerUserCard");
    $allCards = $model->where(array('user_id'=>$userId))->select();
    if ($user["status"] == -1 || $user["status"] == 2) {// 离职等情况
        foreach ($allCards as $cardItem) {
            if (strlen($cardItem["doors"]) > 0) {
                $cardItem["doors"] = "";
                $cardItem["status"] = 0;
                $cardItem["last_sync_time"] = 0;
                $result = saveCardItem($cardItem, $model);
            }
        }
    } else {// 修改部门，修改权限
        $controllerDoors = getUserDoors2($user);
        foreach ($allCards as $cardItem) {
            $controllerId = $cardItem["controller_id"];
            $allCardMap[$cardItem['card_number']][$controllerId] = $cardItem["doors"];

            if (strcmp($cardItem['card_number'], $cardNumber) === 0) {
                if (is_array($controllerDoors[$controllerId])) {
                    $doosStr = implode(",", $controllerDoors[$controllerId]);
                    if (strcmp($doosStr, $cardItem["doors"]) !== 0) {// 权限发生改变的
                        $cardItem["doors"] = $doosStr;
                        $cardItem["last_sync_time"] = 0;
                        $cardItem["status"] = 0;
                        $result = saveCardItem($cardItem, $model);
                    }
                } else {
                    if (!empty($cardItem["doors"])) {// 被删除的权限同步
                        $cardItem["doors"] = "";
                        $cardItem["last_sync_time"] = 0;
                        $cardItem["status"] = 0;
                        $result = saveCardItem($cardItem, $model);
                    }
                }
            } else { // 卡号发生改变
                if (strlen($cardItem["doors"])) {
                    $cardItem["doors"] = "";
                    $cardItem["last_sync_time"] = 0;
                    $cardItem["status"] = 0;
                    $result = saveCardItem($cardItem, $model);
                }
            }
            if (!$result) break;
        }
        if ($result) {
            if (!empty($cardNumber)) {// 新增名单
                foreach ($controllerDoors as $controllerId=>$doors) {
                    if (!isset($allCardMap[$cardNumber][$controllerId])) {
                        $cardItem = array('controller_id'=>$controllerId, 'user_id'=>$userId, 'card_number'=>$cardNumber, 'doors'=>implode(",", $doors));
                        $cardItem["last_sync_time"] = 0;
                        $cardItem["status"] = 0;
                        $result = $model->add($cardItem);
                    }
                    if (!$result) break;
                }
            }
        }
    }
    if ($result) {
        $model->commit();
    } else {
        $model->rollback();
    }
    return $result;
}

function getArrayPart($array, $keys) {
    $result = array();
    foreach ($keys as $key) {
        $result[$key] = $array[$key];
    }
    return $result;
}

function saveCardItem($cardItem, $model) {
    if (!$model) $model = M("DoorControllerUserCard");
    $whereMap = getArrayPart($cardItem, array("controller_id","user_id","card_number"));
    return $model->where($whereMap)->save($cardItem);
}

function clearUserCardsByController($controllerId) {// 当控制器权限发生变化时
    $controller = M("DoorController")->where("id=$controllerId")->find();
    if ($controller["status"] == -1) {
        $userCards = M("DoorControllerUserCard")->where(array('controller_id'=>$controllerId, 'doors'=>array('neq',"")))->select();
        foreach ($userCards as $cardItem) {
            $cardItem["doors"] = "";
            $cardItem["status"] = 0;
            $cardItem["last_sync_time"] = 0;
            saveCardItem($cardItem);
        }
    }
}

function getFileName($path) {
    $pathinfo = pathinfo($path);
    return $pathinfo["basename"];
}

function checkUfaceUser($userId, $user) {
    $model = M("UfaceUser");
    $guid = $model->where("user_id=$userId")->getField("uface_guid");
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
    } else {
        $response = ufaceApiAutoParams('put', array(
            C('UFACE_APP_ID'), "/person/", $guid
        ), array(
            'appId' => C('UFACE_APP_ID'),
            'guid' => $guid,
            'name' => $user['nickname'],
            'phone' => $user['account'],
            'idNo'  => $user['card_number'],
            'type'  => $user['id'],
        ));
        if ($response->result == 1) {

        } else {
            $error = $response->msg;
        }
    }
    return $guid;
}

function getUfaceGuidOrCreate($user) {
    $userId = $user["id"];
    $model = M("UfaceUser");
    $guid = $model->where("user_id=$userId")->getField("uface_guid");
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
    return $guid;
}
?>