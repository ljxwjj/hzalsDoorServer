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

    public function checkNewVersion() {
        $nowVersion = I('version');
        if (empty($nowVersion)) {
            $result = $this->createResult(0, "参数错误");
        } else {
            $model = M("ApkVersion");
            $map['version_code'] = array("GT", $nowVersion);
            $version = $model->where($map)->order('version_code desc')->limit(1)->find();
            if ($version) {
                $versionData = array(
                    'version_code' => $version['version_code'],
                    'version_des'  => $version['version_des'],
                    'update_level' => $version['update_level'],
                    'apk_url'      => getHttpRooDir().'/Public'.$version['apk_path'],
                );
                $result = $this->createResult(200, "", $versionData);
            } else {
                $result = $this->createResult(1, "无新版本");
            }
        }
        $this->response($result,'json');
    }
}