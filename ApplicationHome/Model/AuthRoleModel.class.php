<?php
namespace Home\Model;

class AuthRoleModel extends CommonModel{
	public $_validate = array(	  
	);
	
	public $patchValidate = true;
	
    public $_auto		=	array(
		array('status','1',MODEL_INSERT),
        array('create_time','time',self::MODEL_INSERT,'function'),
        array('update_time','time',self::MODEL_UPDATE,'function'),
    );
    
    
    protected $_link = array(
        'User'  =>  array(
            'mapping_type'=>MANY_TO_MANY,
            'mapping_name'=>'Role_Users',
            'foreign_key'=>'role_id',
            'relation_foreign_key'=>'user_id',
            //'relation_table'=>"role_user",       
        ),        
    );
    
    public function getRoleByCode($code,$rel=false){
        $map['code'] = $code;
        $roleRecord = $this->where($map)->relation($rel)->find();        
        return $roleRecord;
    }
    
    public function getValidRole($rel=false){
        $map['status'] = 1;
        $arrRole = $this->where($map)->relation($rel)->select();        
        return $arrRole;
    }
}