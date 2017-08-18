<?php
namespace Api\Controller;
class UcenterController extends CommonRestController {

    public function detail()
    {
        $user = session('user');
        if ($user) {
            $result = $this->createResult(200, "操作成功", $user);
        } else {
            $result = $this->createResult(0, "操作失败");
        }
        $this->response($result, "json");
    }

    // 显示二维码
    public function qrCode() {
        $user_id = I("user_id");
        $token = createToeken(8);
        $update_time = time();
        $model = D('UserQrcode');
        $data = $model->find($user_id);
        if ($data) {
            $data['token'] = $token;
            $data['update_time'] = $update_time;
            $result = $model->save($data);
        } else {
            $data = compact('user_id', 'token', 'update_time');
            $result = $model->add($data);
        }
        if ($result) {
            $tokenNumber = hexdec($token);
//            $url = "http://qr.topscan.com/api.php?text=$tokenNumber";
            $this->response($this->createResult(200, "", $tokenNumber), "json");
        } else {
            $this->response($this->createResult(0, "系统错误"), "json");
        }
    }

    public function authAccess() {

    }

    public function modifyPassword()
    {
        $password = I('password', '', 'md5');
        $new_password = I('new_password', '', 'md5');

        $User = M('User');
        $user = $User->find(I('user_id'));

        if (!$user) {
            $result = $this->createResult(1, '非系统用户');
        } else if (empty($user['password'])) {
            $result = $this->createResult(2, '帐号未激活');
        } else if ($user['status'] === -1) {
            $result = $this->createResult(3, '用户被禁用');
        } else if ($user['password'] !== $password){
            $result = $this->createResult(0, '原密码错误');
        } else {
            $user['password'] = $new_password;
            $user['update_time'] = time();
            $saveFlag = $User->save($user);

            if ($saveFlag) {
                $result = $this->createResult(200, '找回成功');
            } else {
                $result = $this->createResult(0, '找回失败');
            }
        }

        $this->response($result,'json');
    }

    public function edit() {
        $User = M('User');
        $user = $User->find($_REQUEST['user_id']);
        if (!$User) {
            $result = $this->createResult(0, '用户不存在！');
            $this->response($result,'json');
            return;
        }

        $upload = new \Think\Upload();
        $upload->maxSize = 3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     C('public_dir'); // 设置附件上传根目录
        $upload->savePath  =     C('user_image_dir'); // 设置附件上传（子）目录
        $upload->saveName  =     array('uniqid','');
        $upload->subName   =     array('date','Ymd');
        // 上传文件
        $info   =   $upload->uploadOne($_FILES['head_image']);
        if(!$info) {// 上传错误提示错误信息
            $result = $this->createResult(0, $upload->getError());
        }else{// 上传成功
            $user['head_image'] = $info['savepath'].$info['savename'];
            $User->save($user);
            $result = $this->createResult(200, '上传成功！');
        }
        $this->response($result,'json');
    }
}