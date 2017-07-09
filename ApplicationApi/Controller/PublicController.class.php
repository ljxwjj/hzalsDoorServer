<?php
namespace Api\Controller;

class PublicController extends CommonRestController {

    public function login($account = '', $password = ''){
        $result = array();
        if ($password) $password = md5($password);
        $User = M('User');  // D('User');
        $map['account'] = $account;
        $user = $User->where($map)->find();
        if ($user && $user['password'] == $password) {
            $result['code'] = 200;
            $result['message'] = '登录成功';
            $result['data'] = $user;

            $userToken = array('user_id'=>$user['id'], 'token'=>createToekn(), 'update_time'=>time());
            $model = M('UserToken');
            $model->create($userToken);
            $model->save();
        } else {
            $result['code'] = 0;
            $result['message'] = '登录失败';
            $result['data'] = (object)array();
        }
        $this->response($result,'json');
    }
}