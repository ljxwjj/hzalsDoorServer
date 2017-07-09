<?php
return array(
	//'配置项'=>'配置值'
    'SHOW_PAGE_TRACE'     =>  true,                // 开启右下角的调试信息小图标
    'USER_AUTH_TYPE'			=>  2,		       // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY'       =>  'lcsomauthId',	   // 用户认证SESSION标记
    'ADMIN_AUTH_KEY'			=>  'administrator',
    'LAYOUT_ON'=>true,
    'LAYOUT_NAME'=>'layout',
    'USER_AUTH_MODEL'           =>  'User',	       // 默认验证数据表模型
    'USER_AUTH_GATEWAY'         =>  'Public/login',// 默认认证网关
    'USER_AUTH_ON'              =>  true,
    'PUBLIC_PAGES'              =>  array(         // 免登录页面清单
        'Public' => '*',
        'RequestUse'   => array ('add', 'save'),
    ),
    'GUEST_AUTH_ON'             =>  false,         // 是否开启游客授权访问
    'GUEST_AUTH_ID'             =>  0,             // 游客的用户ID

    'AUTOLOAD_NAMESPACE' => array(
        'Lib'     => MODULE_PATH.'Lib',
    ),
);