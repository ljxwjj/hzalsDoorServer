<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/26
 * Time: 13:58
 */

namespace Home\Controller;


class AppSettingController extends CommonController {

    public function _initialize() {
        $this->assign('pagetitle',"APP配置");
        parent::_initialize();
    }

    public function index(){
        $model = M("AppSetting");
        $this->loadListData($model);
        $this->display();
    }

    private function loadListData($model) {
        if (!$model) $model = M("AppSetting");
        $splashImage = $model->where(array("code_name"=>"splash_image"))->find();
        $lunboImages = $model->where(array("code_name"=>"lunbo_image"))->order("weight")->select();
        $this->assign('splashImage',$splashImage);
        $this->assign('lunboImage',$lunboImages);
    }

    public function saveSplash() {
        $upload = new \Think\Upload();
        $upload->maxSize = 3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     C('public_dir'); // 设置附件上传根目录
        $upload->savePath  =     C('app_image_dir'); // 设置附件上传（子）目录
        $upload->saveName  =     array('uniqid','');
        $upload->autoSub   =     false;
        // 上传文件
        $info   =   $upload->uploadOne($_FILES['splash_image']);

        $model = M("AppSetting");
        $error = array();
        if(!$info) {// 上传错误提示错误信息
            $error['splash_image'] = "文件上传失败";
            $this->assign('error',$error);

            $this->loadListData($model);
            $this->display("index");
        }else{// 上传成功
            $imagePath = $info['savepath'].$info['savename'];
            $splashImage = $model->where(array("code_name"=>"splash_image"))->find();
            if ($splashImage) {
                $oldImagePath = $splashImage['code_value'];
                $fullPath = C('public_dir').$oldImagePath;
                @unlink ($fullPath);

                $splashImage['code_value'] = $imagePath;
                $splashImage['update_time'] = time();
                $model->save($splashImage);
            } else {
                $splashImage = array(
                    'code_name'  => 'splash_image',
                    'code_value' => $imagePath,
                    'create_time' => time(),
                    'update_time' => time()
                );
                $model->add($splashImage);
            }

            $this->redirect("AppSetting/index");
        }
    }

    public function saveLunbo() {
        $weight = I('weight', 0, 'int');

        $upload = new \Think\Upload();
        $upload->maxSize = 3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     C('public_dir'); // 设置附件上传根目录
        $upload->savePath  =     C('app_image_dir'); // 设置附件上传（子）目录
        $upload->saveName  =     array('uniqid','');
        $upload->autoSub   =     false;
        // 上传文件
        $info   =   $upload->uploadOne($_FILES['lunbo_image']);

        $model = M("AppSetting");
        $error = array();
        if(!$info) {// 上传错误提示错误信息
            $error['lunbo_image'] = "文件上传失败";
            $this->assign('error',$error);

            $this->loadListData($model);
            $this->display("index");
        }else{// 上传成功
            $imagePath = $info['savepath'].$info['savename'];
            $lunboImage = array(
                'code_name'  => 'lunbo_image',
                'code_value' => $imagePath,
                'weight'    => $weight,
                'create_time' => time(),
                'update_time' => time()
            );
            $model->add($lunboImage);
            $this->redirect("AppSetting/index");
        }

    }

    public function delLunbo() {
        $id = I('id', 0, 'int');
        $model = M("AppSetting");
        $lunboImage = $model->where(array('code_name'=>'lunbo_image', 'id'=> $id))->find();
        if ($lunboImage) {
            $oldImagePath = $lunboImage['code_value'];
            $fullPath = C('public_dir').$oldImagePath;
            @unlink ($fullPath);

            $model->where("id=$id")->delete();
        }
        $this->redirect("AppSetting/index");
    }
}