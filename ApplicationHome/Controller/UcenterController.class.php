<?php
namespace Home\Controller;

use Lib\ORG\Util\CheckError;

class UcenterController extends CommonController {

    public function _initialize() {
        $this->assign('pagetitle',"个人中心");
        parent::_initialize();
    }
    /**
     * 用户中心首页*
     *
     */
    public function index(){
        $map['account'] = $_SESSION['account'];
        $model = D('User');
        $vo = $model->where($map)->find();
        load("@.Array");


        $sql = " SELECT * FROM auth_role as r,auth_role_user as ru ".
            " WHERE ru.role_id = r.id AND ru.user_id = {$vo['id']}";
        $roleList = $model->query($sql);
        if($roleList){
            $arrRole = array_col_values($roleList,'name');
            $role = implode(",",$arrRole);
        }

        $this->assign('vo',$vo);
        $this->assign('role',$role);
        $this->display();
    }

    /**
     * 修改资料页面*
     *
     */
    public function modifyProfile(){
        load("@.Array");
        $map['id'] = $_GET['id'];
        $userList = D('user')->where($map)->select();
        $model = D('User');

        $sql = " SELECT * FROM auth_role as r,auth_role_user as ru ".
            " WHERE ru.role_id = r.id AND ru.user_id = {$map['id']}";
        $roleList = $model->query($sql);
        if($roleList){
            $arrRole = array_col_values($roleList,'name');
            $role = implode(",",$arrRole);
        }
        $this->assign('vo',$userList[0]);
        $this->assign('role',$role );
        $this->display();
    }

    /**
     * 保存资料*
     *
     */
    public function save(){
        $map['id'] = $_POST['id'];
        $_POST['update_time'] = time();
        import("@.Lib.ORG.Util.CheckError");
        $objError = new CheckError();
        $objError->checkError();
        $objError->doFunc(array("姓名","nickname",45),array("EXIST_CHECK","MAX_LENGTH_CHECK"));
        $objError->doFunc(array("邮箱","email"),array("EXIST_CHECK","EMAIL_CHECK"));
        if($_POST['mobile']){
            $objError->doFunc(array("移动电话","mobile"),array("CN_MOBILE_CHECK"));
        }
        if ($_FILES['head_image']['name']) {
            $upload = new \Think\Upload();
            $upload->maxSize = 3145728 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =     C('public_dir'); // 设置附件上传根目录
            $upload->savePath  =     C('user_image_dir'); // 设置附件上传（子）目录
            $upload->saveName  =     array('uniqid','');
            $upload->subName   =     array('date','Ymd');
            // 上传文件
            $info   =   $upload->uploadOne($_FILES['head_image']);
            if ($info) {
                $_POST['head_image'] = $info['savepath'].$info['savename'];
            } else {
                $objError->arrErr['head_image'] = $upload->getError();
            }
        }

        if(count($objError->arrErr) == 0){
            $model = D('User');
            $data = $model->field('head_image,nickname,sex,mobile,email')->create();
            $result = $model->where($map)->save($data);
            if($result > 0){
                if ($_POST['head_image']) {
                    $oldHeadImage = $_SESSION['head_image'];
                    $filePath = C('public_dir').$oldHeadImage;
                    @unlink($filePath);
                    $_SESSION['head_image'] = $_POST['head_image'];
                }
                $this->success('数据保存成功','index');
            }else{
                $this->error('数据保存时出错');
            }
        }else{
            $this->assign("error",$objError->arrErr);
            $this->assign("vo",$_POST);
            $this->display("modifyProfile");
        }
    }

    /**
     * 修改密码*
     *
     */
    public function modifyPwd(){
        if(!$_POST['submit']){
            $this->display();
            exit();
        }
        $pwd = md5($_POST['pwd']);
        $map['account'] = $_SESSION['account'];
        $password = D('User')->where($map)->getField('password');

        $error = array();
        import("@.Lib.ORG.Util.CheckError");
        $objError = new CheckError();
        $objError->checkError();
        $objError->doFunc(array("新密码","new",20),array("EXIST_CHECK","MAX_LENGTH_CHECK"));
        $objError->doFunc(array("重复新密码","renew",20),array("EXIST_CHECK","MAX_LENGTH_CHECK"));
        if(!$objError->arrErr['renew']){
            $objError->doFunc(array("新密码","重复新密码","new","renew"),array("EQUAL_CHECK"));
        }
        $error = $objError->arrErr;
        if($pwd !== $password){
            $error['pwd'] = '输入的原密码不正确';
        }

        if(empty($error)){
            $map['account'] = $_SESSION['account'];
            $data['password'] = md5($_POST['new']);
            $data['update_time'] = time();
            $result = D('User')->where($map)->save($data);
            if($result > 0){
                $this->success('数据保存成功','index');
                return;
            }
            $this->error('数据保存失败');
        }else{
            $this->assign('error',$error);
            $this->display();
        }
    }
}