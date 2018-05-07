<?php
namespace Api\Controller;

class RepairRecordController extends CommonRestController {
    protected $allowMethod    = array('get','post','put'); // REST允许的请求类型列表
    protected $allowType      = array('html','xml','json'); // REST允许请求的资源类型列表

    public function add() {
        $model = M("RepairRecord");
        $data = $model->field('user_id,company_name,phone,address,describe_text')->create($_REQUEST);
        if ($data) {
            if ($_FILES['image_file']) {
                $upload = new \Think\Upload();
                $upload->maxSize = 3145728;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath = C('public_dir'); // 设置附件上传根目录
                $upload->savePath = C('user_image_dir'); // 设置附件上传（子）目录
                $upload->saveName = array('uniqid', '');
                $upload->autoSub = true;
                // 上传文件
                $info = $upload->upload();

                if (!$info) {
                    $error = $upload->getError();
                    $result = $this->createResult(0, $error);
                } else {
                    $imagesPath = array();
                    foreach($info as $file) {
                        $imagesPath[] = $file['savepath'] . $file['savename'];
                    }
                    $data['image'] = implode(";", $imagesPath);
                }
            }

            if (!$result) {
                $data['status'] = 0;
                $data['create_time'] = time();
                $addResult = $model->add($data);
                if ($addResult) {
                    $result = $this->createResult(200, "保存成功");
                } else {
                    $result = $this->createResult(0, "操作失败");
                }
            }
        } else {
            $result = $this->createResult(0, "操作失败");
        }
        $this->response($result,'json');
    }



}