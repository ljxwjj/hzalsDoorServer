<?php
return array(
	//'配置项'=>'配置值'
    'DB_TYPE'      =>   'mysql',
    'DB_HOST'      =>   'localhost',
    'DB_NAME'      =>   'hzals_door',
    'DB_USER'      =>   'root',
    'DB_PWD'       =>   '0123456789',
    'DB_PORT'      =>    '',
    'DB_PREFIX'    =>    '',           // 数据库表前缀

    'AUTH_CONFIG'  => array(
        'AUTH_ON'           => true,                      // 认证开关
        'AUTH_TYPE'         => 1,                         // 认证方式，1为实时认证；2为登录认证。
        'AUTH_GROUP'        => 'auth_group',        // 用户组数据表名
        'AUTH_GROUP_ACCESS' => 'auth_group_access', // 用户-用户组关系表
        'AUTH_RULE'         => 'auth_rule',         // 权限规则表
        'AUTH_USER'         => 'user'             // 用户信息表
    ),
    //***********************************URL设置**************************************
    'MODULE_ALLOW_LIST'      => array('Home','Admin','Api','User','App'), //允许访问列表
    'URL_HTML_SUFFIX'        => '',  // URL伪静态后缀设置
    'URL_MODEL'              => 1,  //启用rewrite
    'APP_SUB_DOMAIN_DEPLOY'  => 1,
    'APP_SUB_DOMAIN_RULES'   => array(
         'admin.hzals.com' => 'Home',
         'api' => 'Api',
    ),


    // 自定义配置
    'yourAccessKeyId'   => 'LTAIylWfQ2JvmsOG',
    'yourAccessKeySecret' => 'Ou3LWjUicUY3Sz1F16R9TSjCVxIgM1',
    'public_dir'          => 'C:\AppServ\www\door\Public',
    'user_image_dir'      => '\data\image'
);