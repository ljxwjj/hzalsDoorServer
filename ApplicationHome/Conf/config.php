<?php
return array(
	//'配置项'=>'配置值'
    // 系统配置
    'SHOW_PAGE_TRACE'     =>  true,                // 开启右下角的调试信息小图标
    'LAYOUT_ON'=>true,
    'LAYOUT_NAME'=>'layout',
    'SESSION_OPTIONS'           =>array(
        'expire' => 1800,
    ),

    'AUTOLOAD_NAMESPACE'        => array(
        'Lib'     => MODULE_PATH.'Lib',
    ),

    // 自定义配置
    'USER_AUTH_TYPE'			=>  2,		       // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY'             =>  'lcsomauthId',	   // 用户认证SESSION标记
    'ADMIN_AUTH_KEY'			=>  'administrator', // 系统管理员认证标记
    'USER_AUTH_MODEL'           =>  'User',	       // 默认验证数据表模型
    'USER_AUTH_GATEWAY'         =>  'Public/login',// 默认认证网关
    'USER_AUTH_ON'              =>  true,          // 是否开启用户认证
    'PUBLIC_PAGES'              =>  array(         // 免登录页面清单
        'Public' => '*',
        'RequestUse'   => array ('add', 'save'),
    ),
    'GUEST_AUTH_ON'             =>  false,         // 是否开启游客授权访问
    'GUEST_AUTH_ID'             =>  0,             // 游客的用户ID
    'LIST_ROWS'                 => 20,             // 分页页面每页记录数
    'DEFAULT_FILTER'            => 'trim,strip_tags,htmlspecialchars',

);