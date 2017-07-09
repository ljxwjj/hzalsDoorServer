<?php
namespace Home\Model;


class CompanyModel extends CommonModel {

    public $_link = array(


    );

    public $_validate = array(
    );

    public $patchValidate = true;

    public $_auto		=	array(
        array('status','1',MODEL_INSERT),
        array('create_time','time',self::MODEL_INSERT,'function'),
        array('update_time','time',self::MODEL_UPDATE,'function'),
    );


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