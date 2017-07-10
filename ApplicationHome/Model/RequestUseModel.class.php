<?php
namespace Home\Model;


class RequestUseModel extends CommonModel {

    public $_link = array(


    );

    public $_validate = array(
    );

    public $patchValidate = true;

    public $_auto		=	array(
        array('status','1',MODEL_INSERT),
    );

}