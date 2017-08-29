<?php
namespace Api\Model;
use Think\Model;

class UserModel extends Model
{
    public function forbid($options,$field='status'){

        if(FALSE === $this->where($options)->setField($field,2)){
            $this->error =  L('_OPERATION_WRONG_');
            return false;
        }else {
            return True;
        }
    }

}