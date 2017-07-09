<?php
namespace Home\Controller;

class RequestUseController extends CommonController {

    public function index() {
        $this->display();
    }

    public function save() {
        if(empty($_POST['company'])) {
            $error['company']='单位名称不能为空！';
        }
        if (empty($_POST['contacts'])){
            $error['contacts']='联系人姓名不能为空！';
        }
        if(empty($_POST['telphone'])) {
            $error['telphone']='联系电话不能为空！';
        }
        if (empty($_POST['order_number'])){
            $error['order_number']='订单号不能为空！';
        }

        if ($error) {
            $this->assign('error',$error);
            $this->assign('vo',$_POST);
            $this->display('add');
            return;
        }
        $map = array();
        $map["telphone"] = $_POST['contacts'];
        $map['state'] = array("neq", -1);
        $model = M("RequestUse");
        $data = $model->where($map)->find();
        if ($data) {
            $error['telphone'] = '该手机号已审请过，请等待审核';

            $this->assign('error',$error);
            $this->assign('vo',$_POST);
            $this->display('add');
            return;
        }

        $data = $model->create();
        if(!$data){
            $error = $model->getError();
            $this->assign('vo',$_REQUEST);
            $this->assign('error',$error);
            $this->display('add');
        }else{
            $result = $model->add($data);
            if($result){
                $this->success('数据已保存！',$this->getReturnUrl());
            }else{
                $this->error('数据未保存！',$this->getReturnUrl());
            }
        }
    }

    public function getReturnUrl() {
        return U("Index/index");
    }
}