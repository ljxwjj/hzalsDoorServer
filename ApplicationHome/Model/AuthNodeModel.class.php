<?php
namespace Home\Model;
use Think\Model;

class AuthNodeModel extends CommonModel {
	public $_validate = array(
	    array('name','require','权限名必须！'), 
	    array('title','require','权限中文名必须！'), 
	    //不做过多验证了，权限英文名对应pup里面的model level1 或者action level2 
	    //自动生成的方案程序中随着建立自动建立权限 树状（业务模块level1(但不对应权限，只显示) 流程对model level2 步骤对action level3） 树状末尾是步骤；步骤与表单1对多；表单与顺序1对多（顺序可分租）
	    array('remark','0,100','备注长度在0到100之间！',Model::VALUE_VALIDATE,'length'), // remark不为空时，长度范围是0一100
	);
	
	public $patchValidate = true;
	
    public $_auto		=	array(
		array('status','1'),
    );
}