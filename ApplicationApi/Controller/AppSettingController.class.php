<?php
namespace Api\Controller;

class AppSettingController extends CommonRestController {
    protected $allowMethod    = array('get','post','put'); // REST允许的请求类型列表
    protected $allowType      = array('html','xml','json'); // REST允许请求的资源类型列表

    public function splashImage() {
        $model = M("AppSetting");
        $splashImage = $model->where(array("code_name"=>"splash_image"))->find();
        if ($splashImage) {
            $imageUrl = getHttpRooDir().'/Public'.$splashImage["code_value"];
            $result = $this->createResult(200, "", $imageUrl);
        } else {
            $result = $this->createResult(0, "操作失败");
        }
        $this->response($result,'json');
    }

    public function lunboImages() {
        $model = M("AppSetting");
        $lunboImages = $model->where(array("code_name"=>"lunbo_image"))->order("weight")->getField("code_value", true);
        if ($lunboImages) {
            foreach ($lunboImages as $k=>$v) {
                $lunboImages[$k] = getHttpRooDir().'/Public'.$v;
            }
            $result = $this->createResult(200, "", $lunboImages);
        } else {
            $result = $this->createResult(0, "操作失败");
        }
        $this->response($result,'json');
    }

}