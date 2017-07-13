<?php
return array(
	//'配置项'=>'配置值'
    'SESSION_AUTO_START'        => false,     // 接口中不自动开启session
    'CHECK_PARAMS_SIGN'         => true,      // 是否开启参数签名验证
    'USER_AUTH_ON'              =>  true,
    'ADMIN_AUTH_KEY'			=>  'administrator',
    'API_PARAMS_SIGN_KEY'       => 'sign',
    'API_PARAMS_SIGN_VALUE'     => '8djUK*014kJ',
    'PUBLIC_APIS'               => array('Public'=>'*'),   // 免登录接口清单
    'LIST_ROWS'                 => 20,

    'AUTOLOAD_NAMESPACE'        => array(
        'Lib'     => MODULE_PATH.'Lib',
    ),
);