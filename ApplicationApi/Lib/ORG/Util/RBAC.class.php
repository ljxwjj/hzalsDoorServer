<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: RBAC.class.php 2947 2012-05-13 15:57:48Z liu21st@gmail.com $

/**
 +------------------------------------------------------------------------------
 * 基于角色的数据库方式验证类
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: RBAC.class.php 2947 2012-05-13 15:57:48Z liu21st@gmail.com $
 +------------------------------------------------------------------------------
 */
// 配置文件增加设置
// USER_AUTH_ON 是否需要认证
// USER_AUTH_TYPE 认证类型
// USER_AUTH_KEY 认证识别号
// REQUIRE_AUTH_MODULE  需要认证模块
// NOT_AUTH_MODULE 无需认证模块
// USER_AUTH_GATEWAY 认证网关
// RBAC_DB_DSN  数据库连接DSN
// RBAC_ROLE_TABLE 角色表名称
// RBAC_USER_TABLE 用户表名称
// RBAC_ACCESS_TABLE 权限表名称
// RBAC_NODE_TABLE 节点表名称
/*
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `think_access` (
  `role_id` smallint(6) unsigned NOT NULL,
  `node_id` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `think_node` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `sort` smallint(6) unsigned DEFAULT NULL,
  `pid` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `pid` (`pid`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `think_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `pid` smallint(6) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `think_role_user` (
  `role_id` mediumint(9) unsigned DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/
namespace Lib\ORG\Util;

class RBAC {
    // 认证方法
    static public function authenticate($map,$model='') {
        if(empty($model)) $model =  C('USER_AUTH_MODEL');
        //使用给定的Map进行认证
        return M($model)->where($map)->find();
    }

	// 取得模块的所属记录访问权限列表 返回有权限的记录ID数组
	static function getRecordAccessList($authId=null,$module='') {
        if(null===$authId)   $authId = session(C('USER_AUTH_KEY'));
        if(empty($module))  $module	=	MODULE_NAME;
        //获取权限访问列表
        $accessList = RBAC::getModuleAccessList($authId,$module);
        return $accessList;
	}

    //检查当前操作是否需要认证   (权判断 module  action  在配置文件中是否要权限验证)
    static function checkAccess() {
        //如果项目要求认证，并且当前模块需要认证，则进行权限认证
        if ( C('USER_AUTH_ON') ) {
            $_public_pages = C('PUBLIC_APIS');
            $_public_actions = $_public_pages[CONTROLLER_NAME];
            if ($_public_actions === '*') {
                return false;
            } else if (is_array($_public_actions) && in_array(ACTION_NAME, $_public_actions)) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    static function AccessToken() {
        $user_id = I('user_id');
        $token = I('token');
        $model = M('UserToken');
        $result = $model->where(compact('user_id'))->find();
        return strcmp($token, $result['token']) === 0;
    }

    static function checkParamsSign() {
        if (C('CHECK_PARAMS_SIGN')) {
            $sign_key = C('API_PARAMS_SIGN_KEY');
            $sign = I($sign_key);
            if (!$sign) {
                return false;
            }
            $params = array();
            $params = array_merge($params, $_REQUEST);
            unset($params[$sign_key]);
            ksort($params);
            $paramsStr = implode("", $params);
            $paramsStr = strrev($paramsStr);
            $paramsStr =  $paramsStr. C('API_PARAMS_SIGN_VALUE');
            $paramsMd5 = md5($paramsStr);
            return strcasecmp($paramsMd5, $sign) === 0;
        } else {
            return true;
        }
    }

    //权限认证的过滤器方法
    static public function AccessDecision() {
        //检查是否需要认证
        if(RBAC::checkAccess()) {
            $user = M('User')->find(I('user_id'));
            if ($user) {
                session('user', $user);
                if ($user['is_admin']) {
                    session(C('ADMIN_AUTH_KEY'), true);
                }
            } else {
                return false;
            }

            if(empty(session(C('ADMIN_AUTH_KEY')))) {
                //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                //通过数据库进行访问检查
                $accessList = RBAC::getAccessList($user['id']);

                if(!isset($accessList[strtoupper(MODULE_NAME)][strtoupper(CONTROLLER_NAME)][strtoupper(ACTION_NAME)])) {
                    return false;
                }
            }else{
                //管理员无需认证
				return true;
			}
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * 取得当前认证号的所有权限列表
     +----------------------------------------------------------
     * @param integer $authId 用户ID
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    static public function getAccessList($authId) {
        //角色权限
        //$access = RBAC::getRoleAccessArray($authId);
        //用户权限
        //合并权限

        $Model = M();
        $sql = "select node.id, node.name, node.pid from ".
            "user, auth_role_user as role_user,".
            "auth_role as role, ".
            "auth_access as role_node, ".
            "auth_node as node ".
            "where user.id = {$authId} and user.id=role_user.user_id and role_user.role_id = role.id and role_node.role_id=role.id and role_node.node_id = node.id and role.status=1 and node.status=1 order by node.pid desc, node.id desc";
        $nodes = $Model->query($sql);//var_dump($nodes);exit;
        $nodemap = array();
        foreach ($nodes as $node) {
            if (is_array( $nodemap[$node['id']] )) {
                $parray = $nodemap[$node['id']];
                $nodemap[$node['pid']][$node['name']] = array_change_key_case($parray, CASE_UPPER);
            } else {
                $nodemap[$node['pid']][$node['name']] = $node['id'];
            }

        }
        $access = array_change_key_case($nodemap[0],CASE_UPPER);;

        return $access;
    }
    
    protected function getRoleAccessArray($authId){
        $Model = M();
        $sql    =   "select node.id,node.name from ".
            "user as user,".
            "auth_role_user as role_user,".
            "auth_role as role,".
            "auth_access as role_node ,".
            "auth_node as node ".
            "where user.id='{$authId}' and user.id=role_user.user_id and role_user.role_id = role.id and role_node.role_id=role.id and role_node.node_id = node.id and role.status=1 and node.level=1 and node.status=1";
        $apps =   $Model->query($sql);


        //角色模块
        $access =  array();
        foreach($apps as $key=>$app) {
            $appId	=	$app['id'];
            $appName	 =	 $app['name'];
            // 读取项目的模块权限
            $access[strtoupper($appName)]   =  array();
            $sql    =   "select node.id,node.name from ".
                "user as user,".
                "auth_role_user as role_user,".
                "auth_role as role,".
                "auth_access as role_node ,".
                "auth_node as node ".
                "where user.id='{$authId}' and user.id=role_user.user_id and role_user.role_id = role.id and role_node.role_id=role.id and role_node.node_id = node.id and role.status=1 and node.level=2 and node.pid={$appId} and node.status=1";
            $modules =   $Model->query($sql);
            //模块动作
            // 判断是否存在公共模块的权限
            $publicAction  = array();
            foreach($modules as $key=>$module) {
                $moduleId	 =	 $module['id'];
                $moduleName = $module['name'];
                if('PUBLIC'== strtoupper($moduleName)) {
                $sql    =   "select node.id,node.name from ".
                    "user as user,".
                    "auth_role_user as role_user,".
                    "auth_role as role,".
                    "auth_access as role_node ,".
                    "auth_node as node ".
                    "where user.user_id='{$authId}' and user.id=role_user.user_id and role_user.role_id = role.id and role_node.role_id=role.id and role_node.node_id = node.id and role.status=1 and node.level=3 and node.pid={$moduleId} and node.status=1";
                    $rs =   $Model->query($sql);
                    foreach ($rs as $a){
                        $publicAction[$a['name']]	 =	 $a['id'];
                    }
                    unset($modules[$key]);
                    break;
                }
            }
            // 依次读取模块的操作权限
            foreach($modules as $key=>$module) {
                $moduleId	 =	 $module['id'];
                $moduleName = $module['name'];
                $sql    =   "select node.id,node.name from ".
                    "user as user,".
                    "auth_role_user as role_user,".
                    "auth_role as role,".
                    "auth_access as role_node ,".
                    "auth_node as node ".
                    "where user.id='{$authId}' and user.id=role_user.user_id and role_user.role_id = role.id and role_node.role_id=role.id and role_node.node_id = node.id and role.status=1 and node.level=3 and node.pid={$moduleId} and node.status=1";
                $rs =   $Model->query($sql);
                $action = array();
                foreach ($rs as $a){
                    $action[$a['name']]	 =	 $a['id'];
                }
                // 和公共模块的操作权限合并
                $action += $publicAction;
                $access[strtoupper($appName)][strtoupper($moduleName)]   =  array_change_key_case($action,CASE_UPPER);
            }
        }
        return $access;
    }

	// 读取模块所属的记录访问权限
	static public function getModuleAccessList($authId,$module) {
        // Db方式
        $db     =   Db::getInstance(C('RBAC_DB_DSN'));        
        $access	=	array();
        $table  = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
		
        $modulename = ucfirst(strtolower($module));
        $sql     = "select node.id,node.name from ".$table['node']." as node where node.name ='{$modulename}' and node.status=1";
        $pidNodeRecord =   $db->query($sql);        
        if($pidNodeRecord){
        	$modulePid = $pidNodeRecord[0]['id'];
        	
        	$sql = "select node.id,node.name from ".
                $table['role']." as role,".
                $table['user']." as user,".
                $table['access']." as access ,".
                $table['node']." as node ".
                "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  ) and role.status=1 and access.node_id=node.id and ( ( node.level=3 and node.pid ='{$modulePid}') or node.id = '{$modulePid}') and node.status=1";      
          
       	   $rs =   $db->query($sql);
       	   foreach ($rs as $node){
            	$access[strtoupper($node['name'])]	=	$node['id'];
       	   }
        }
		return $access;
	}
	
	static public function checkAuth($authId,$module,$action){
		if(null===$authId)  $authId  =  session(C('ADMIN_AUTH_KEY'));
		$appName = APP_NAME;
        if(empty($module))  $module	 =  defined('P_MODULE_NAME')?   P_MODULE_NAME   :   MODULE_NAME;
        if(empty($action))  $action	 =	ACTION_NAME;
        
       
         //存在认证识别号，则进行进一步的访问决策
        $accessGuid   =   md5($appName.$module.$action);
        
        $appName  = strtoupper($appName);
        $module   = strtoupper($module);
        $action   = strtoupper($action);
        
        if(session(C('ADMIN_AUTH_KEY')) != $authId) {
            if(C('USER_AUTH_TYPE')==2) {
                //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                //通过数据库进行访问检查
                $accessList = RBAC::getAccessList(session(C('USER_AUTH_KEY')));
            }else {
                // 如果是管理员或者当前操作已经认证过，无需再次认证
                if( session($accessGuid)) {
                    return true;
                }
                //登录验证模式，比较登录后保存的权限访问列表
                $accessList = session('_ACCESS_LIST');
            }            
            if(!isset($accessList[$appName][$module][$action])) {                
                return false;
            }else {
            	return true;
            }
        }
        return true;
	}
	
	/**
	 * 获取具有对应模块的具体操作权限的用户列表
	 *
	 * @param unknown_type $appName
	 * @param unknown_type $module
	 * @param unknown_type $action
	 */
	static public function getAuthUserList($appName,$module,$action){
		$appName = APP_NAME;
        if(empty($module))  $module	 =  defined('P_MODULE_NAME')?   P_MODULE_NAME   :   MODULE_NAME;
        if(empty($action))  $action	 =	ACTION_NAME;
        
        // Db方式
        $db     =   Db::getInstance(C('RBAC_DB_DSN'));        
        $arrUser	=	array();
        $table  = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
		
        $modulename = ucfirst(strtolower($module));
        $sql     = "select node.id,node.name from ".$table['node']." as node where node.name ='{$modulename}' and node.status=1";
        $pidNodeRecord =   $db->query($sql);
      
        if($pidNodeRecord){
        	$modulePid = $pidNodeRecord[0]['id'];
        	
        	$sql = "select DISTINCT user.user_id  from ".
                $table['role']." as role,".
                $table['user']." as user,".
                $table['access']." as access ,".
                $table['node']." as node ".
                "where user.role_id=role.id and ( access.role_id=role.id  ) and role.status=1 and access.node_id=node.id and (  node.level=3 and node.pid ='{$modulePid}' and node.name = '{$action}') and node.status=1";      
           //dump($sql);
       	   $arr_user =   $db->query($sql);
       	   if($arr_user){
	       	   $user = D('user');	       	   
		       $arrUserId = array_col_values($arr_user,"user_id");
			   $arrUser = $user->where(array("id"=>array("in",$arrUserId),"status"=>array("eq","1")))->select();
       	   }
        }
		return $arrUser;
	}
	
	
}