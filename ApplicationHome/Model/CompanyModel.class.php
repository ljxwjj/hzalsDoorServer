<?php
namespace Home\Model;


class CompanyModel extends CommonModel {

    public $_link = array(
    );

    public $_validate = array(
        array('name','require','公司名称不能为空！'),
        array('admin_mobile','require','联系人电话不能为空！'),
    );

    public $patchValidate = true;

    public $_auto		=	array(
        array('status','0',MODEL_INSERT),
        array('create_time','time',self::MODEL_INSERT,'function'),
        array('update_time','time',self::MODEL_UPDATE,'function'),
    );


}