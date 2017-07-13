<?php
namespace Api\Controller;
class UcenterController extends CommonRestController {

    public function detail()
    {
        $_REQUEST['id'] = I('user_id');
        parent::detail("User");
    }

    // 显示二维码
    public function qrCode() {
        $user_id = I("user_id");
        $url = "http://qrcode.com?u=$user_id&s=aaa";
        $this->response($this->createResult(200, "", $url), "json");
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
            $result = createResult(1, '非系统用户');
        } else if (empty($user['password'])) {
            $result = createResult(2, '帐号未激活');
        } else if ($user['status'] === -1) {
            $result = createResult(3, '用户被禁用');
        } else if ($user['password'] !== $password){
            $result = createResult(0, '原密码错误');
        } else {
            $data['password'] = $new_password;
            $data['update_time'] = time();
            $saveFlag = $User->save($data);

            if ($saveFlag) {
                $result = createResult(200, '找回成功');
            } else {
                $result = createResult(0, '找回失败');
            }
        }

        $this->response($result,'json');
    }
}