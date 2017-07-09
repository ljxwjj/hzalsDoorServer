<?php
namespace Home\Model;
use Think\Model;

class UserModel extends CommonModel {

    public $_link = array(
        'AuthUserRole' => array(
            'mapping_type'=>MANY_TO_MANY,
            'class_name'=>'AuthRole',
            'mapping_name'=>'UserRole',
            'foreign_key'=>'user_id',
            'relation_foreign_key'=>'role_id',
            'relation_table'=>'lc_role_user'
        ),

        'Company'=>array(
            'mapping_type'=>HAS_ONE,
            'class_name'=>'Company',
            'mapping_name'=>'id',
            'foreign_key'=>'company_id',
            ///'mappping_order'=>'id asc'
        ),

    );

    public $_validate = array(
    );

    public $patchValidate = true;

    public $_auto		=	array(
        array('status','1',MODEL_INSERT),
        array('password','pwdHash',self::MODEL_BOTH,'callback'),
        array('create_time','time',self::MODEL_INSERT,'function'),
        array('update_time','time',self::MODEL_UPDATE,'function'),
    );


    public function pwdHash() {
        if(isset($_POST['password'])) {
            return pwdHash($_POST['password']);
        }else{
            return false;
        }
    }

    public function uniqueAccount($value){
        $map['account'] = array('eq',trim($_REQUEST['account']));
        $map['status']  = array('neq',-1);
        if($_REQUEST['id']){
            $map['id'] = array('neq',$_REQUEST['id']);
        }
        $count = $this->where($map)->count();
        if($count > 0){
            return false;
        }
        return true;
    }

    public function uniqueEmail($value){
        $map['email'] = array('eq',trim($_REQUEST['email']));
        $map['status']  = array('neq',-1);
        if($_REQUEST['id']){
            $map['id'] = array('neq',$_REQUEST['id']);
        }
        $count = $this->where($map)->count();
        if($count > 0){
            return false;
        }
        return true;
    }

    /**
     * 根据名称查询*
     *
     * @param string $nickname
     * @param string $field
     * @return string
     */
    public function getFieldByNickname($nickname,$field){
        $map['nickname'] = array('eq',trim($nickname));
        return $this->where($map)->getField($field);
    }

    /**
     * 根据名称查询有效用户相关字段*
     *
     * @param string $nickname
     * @param string $field
     * @return string
     */
    public function getFieldByValidNickname($nickname,$field){
        $map['nickname'] = array('eq',trim($nickname));
        $map['status']  = array('neq',-1);
        return $this->where($map)->getField($field);
    }

    public function getUserlevel2MaterialCategory($userId){
        $Mlevel2MaterialCategory = D("Level2MaterialCategoryUser");
        $map['status'] = 1;
        $map['user_id'] = $userId;
        $record = $Mlevel2MaterialCategory->where($map)->select();
        return $record;
    }


    public function getUserRoleById($id){
        $map['id'] = $id;
        return $this->where($map)->relation('UserRole')->find();
    }

    public function getUserDepartmentById($id){
        $map['id'] = $id;
        return $this->where($map)->relation('UserDepartment')->find();
    }

    public function getByLike($likeField,$likeFieldValue,$queryFields=""){
        $map[$likeField] = array('like','%'.$likeFieldValue.'%');
        $result =  $this->where($map)->select();
        if($queryFields){
            load("@.Array");
            $result = array_col_values($result,$queryFields);
        }
        return $result;
    }
}