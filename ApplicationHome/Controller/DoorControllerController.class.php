<?php
namespace Home\Controller;

use Lib\ORG\Util\Page;

class DoorControllerController extends CommonController {

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"控制器管理");
    }

    /**
     * 查询列表初始化搜索条件配置*
     *
     * @param array $map
     */
    public function _filter(&$map){
        $user_id = $_SESSION[C('USER_AUTH_KEY')];
        $company_id = session('company_id');
        if ($company_id > 1) {
            $map['company_id'] = $company_id;
        }
    }

    /**
     * 设置查询条件*
     *
     * @param array $map  查询条件
     * @param array $search 搜索数组
     */
    protected function setMap(&$map,&$search){
        foreach ($_REQUEST as $key => $val) {
            if($val == "") {
                continue;
            }
            if (ereg("^search_", $key)) {
                $field = str_replace('search_','',$key);
                $map[$field] = $val;
                switch($field){
                    case 'account':
                    case 'nickname':
                        $map[$field] = array('like',"%".$val."%");
                        $search[$key] = $val;
                        break;
                    default:
                        $map[$field] = $val;
                        $search[$key] = $val;
                        break;
                }
            }
        }
    }

    public function index($name = "")
    {
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $map['status'] = array("neq","-1");
        $this->setMap($map,$search);

        $company_id = session('company_id');
        if ($_REQUEST['company_id']) {
            $company_id = $_REQUEST['company_id'];
            $map['company_id'] = $company_id;
            $this->assign('company_id', $company_id);
        } else if ($company_id > 1) {
            $map['company_id'] = $company_id;
            $this->assign('company_id', $company_id);
        }

        $this->keepSearch();
        $model = M('DoorControllerView');
        if (!empty($model)) {
            $this->_list($model, $map);
        }

        //保持分页记录
        $nowpage = (int)I('p')?(int)I('p'):(int)I('search_p');
        if($nowpage){
            $this->assign('nowpage', $nowpage);
        }

        $voList = $this->voList;
        $this->assign('list', $voList);
        $this->display();
    }



    /**
     * 编辑*
     *
     * @param string $name 数据对象
     */
    function edit($name="") {
        $this->keepSearch();
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $id = (int)I($model->getPk());
        if (empty($id)) {
            $this->error('请选择要编辑的数据！');
            exit;
        }
        if (!$this->checkDoorControllerAccess($id)) {
            $this->error('没有该数据的操作权限！');
            exit;
        }
        $vo = $model->getById($id);
        if($vo){
            $this->assign('vo', $vo);
            $this->display();
        }else{
            $this->error('没有找到要编辑的数据！');
        }
    }

    public function add()
    {
        $myUserId = $_SESSION[C('USER_AUTH_KEY')];

        $companyId = I('company_id');
        if (!$companyId) {
            $companyId = M('User')->where(array('id'=>$myUserId))->getField('company_id');
        }
        $vo['company_id'] = $companyId;
        $vo['product_type'] = "0";

        $this->assign('vo', $vo);
        $this->keepSearch();

        $this->display();
    }


    /**
     * 保存*
     *
     */
    public function save() {
        $this->keepSearch();
        $is_add_tpl_file = $this->isAddTplFile();
        $name = $this->getActionName();
        $model = D($name);
        $id = (int)I($model->getPk());

        if (empty(I('product_type'))) {
            $error['product_type'] = "请选择产品类型！";
        }
        $serialNumber = I('serial_number', '', 'trim,strtoupper');
        $productType = I('product_type');
        if(empty($serialNumber)) {
            $error['serial_number']='序列号不能为空！';
        } else if ($productType == "1" && strpos($serialNumber, "ALS") === 0 && strlen($serialNumber) == 11) {
            $doorCount = substr($serialNumber, 3, 1);
        } else if ($productType == "2" && strpos($serialNumber, "000000000") === 0 && strlen($serialNumber) == 16) {
            $doorCount = substr($serialNumber, 9, 1);
        } else {
            $error['serial_number']='序列号格式错误！';
        }

        if ($doorCount) {
            if (!intval($doorCount)) {
                $error['serial_number']='序列号格式错误！';
            }
        }

        if (empty(I('company_id'))) {
            $_REQUEST['company_id'] = session('company_id');
        } else {
            if (I('company_id') != session('company_id') && !session(C('ADMIN_AUTH_KEY'))) {
                $error['company_id']='非法操作！';
            }
        }
        if ($id) {
            if (!$this->checkDoorControllerAccess($id)) {
                $this->error('没有该数据的操作权限！');
                exit;
            }
            // 编辑时，当序列号变更时，判断序列号是否被占用
            $data = $model->find($id);
            if (I('serial_number') != $data['serial_number']) {
                $map['serial_number'] = I('serial_number');
                $map['status'] = 0;
                if ($model->where($map)->find()) {
                    $error['serial_number']='序列号已经被绑定！';
                }
            }
        } else if (I('product_type') == "2") {
            // 当新增时，判断序列号是否被占用
            $map['serial_number'] = I('serial_number');
            $map['status'] = 0;
            $data = $model->where($map)->find();
            if ($data && $data['company_id']) {
                if ($data['company_id'] == $_REQUEST['company_id']) {
                    $error['serial_number']='该序列号不能重复绑定！';
                } else {
                    $error['serial_number']='序列号已经被其它用户绑定！';
                }
            }
            if ($data && !$error && !$data['company_id']) {
                // 控制器已经自动注册上来的数据
                $id = $data['id'];
                $_REQUEST['ip'] = $data['ip'];
                $_REQUEST['port'] = $data['port'];
            }
        }
        if ($error) {
            $this->assign('vo', $_REQUEST);
            $this->assign('error', $error);
            if(!$id && $is_add_tpl_file){
                $this->display('add');
            }else{
                $this->display('edit');
            }
            return;
        }

        $data = $model->create($_REQUEST);
        if(!$data){
            $error = $model->getError();
            $this->assign('vo',$_REQUEST);
            $this->assign('error',$error);
            if(!$id && $is_add_tpl_file){
                $this->display('add');
            }else{
                $this->display('edit');
            }
        }else{
            $data['door_count'] = $doorCount;
            if($id){
                $result = $model->where(array('id'=>$id))->save($data);
            }else{
                $result = $model->add($data);
            }
            if($result){
                $this->success('数据已保存！',$this->getReturnUrl());
            }else{
                $this->error('数据未保存！',$this->getReturnUrl());
            }
        }
    }

    public function view($id) {
        if (!$this->checkDoorControllerAccess($id)) {
            $this->error('没有该数据的操作权限！');
            exit;
        }
        $model = M('DoorControllerView');
        $condition = array('id' => $id);
        $vo = $model->where($condition)->find();
        if ($vo) {
            $doors = M('Door')->where(array('controller_id'=>$id))->select();
            $arrList = array();
            foreach ($doors as $door) {
                $arrList[$door['door_index']] = $door;
            }
            for ($i = 0; $i < $vo['door_count']; $i++) {
                if (!array_key_exists($i, $arrList)) {
                    $arrList[$i] = array('door_index'=>$i, 'controller_id'=>$id, 'name'=>$i."号门");
                }
            }
            ksort($arrList);
            $this->assign('vo', $vo);
            $this->assign('arrList', $arrList);
            $this->display();
        } else {
            $this->error("页面未找到", 'index');
        }
    }

    /**
     * 默认删除操作
     * @param string $name 数据对象
     * @return string
     */
    public function del($name="") {
        //虚拟删除指定记录
        $name = $name ? $name : $this->getActionName();
        $model = M($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = I($pk);

            if (isset($id)) {
                if (!$this->checkDoorControllerAccess($id)) {
                    $this->error('没有该数据的操作权限！');
                    exit;
                }
                $condition = array($pk => array('in', explode(',', $id)));
                $list = $model->where($condition)->setField('status', -1);
                if ($list !== false) {
                    $this->success('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
    }

    /**
     * ajax
     */
    public function openDoor() {
        $controller_id = I('controller_id');
        $door_id = I('door_id');
        if ($controller_id == null) {
            $error = "请选择控制器";
        }
        if ($door_id == null) {
            $error = "请选择门";
        }
        if ($error) {
            $result['code'] = 0;
            $result['message'] = $error;
            $this->response($result);
            exit;
        }

        if (!$this->checkDoorControllerAccess($controller_id)) {
            $result['code'] = 0;
            $result['message'] = "权限校验失败!";
            $this->response($result);
            exit;
        }

        if (!session(C('ADMIN_AUTH_KEY'))) {
            $user_id = session(C('USER_AUTH_KEY'));
            $role_id = M('AuthRoleUser')->where(array('user_id'=>$user_id))->getField('role_id');
            if ($role_id > 21) { // > 21即非管理员用户
                $userDoors = getUserDoors();
                if (!$userDoors[$controller_id][$door_id]) {
                    $result['code'] = 0;
                    $result['message'] = "授权失败!";
                    $this->response($result);
                    exit;
                }
            }
        }

        $data = M('DoorController')->find($controller_id);
        if ($data) {
            $openRecord['controller_id'] = $controller_id;
            $openRecord['door_id'] = $door_id;
            $openRecord['open_time'] = time();
            $openRecord['user_id'] = session(C('USER_AUTH_KEY'));
            $openRecord['way'] = 1;
            $addid = M('OpenRecord')->add($openRecord);

            $wait = intval($data['wait_time']);
            $this->sendOpenDoorUdpCode($data['ip'], $data['port'], $data['serial_number'], $door_id, $wait);
            $result['code'] = 200;
            $result['message'] = $addid;
            $this->response($result);
        }
    }

    /**
     * ajax
     */
    public function openDoorFeedBack() {
        $id = I('id');
        if ($id) {
            $feedbackTime = M('OpenRecord')->where(array('id'=>$id))->getField('feedback_time');
            if ($feedbackTime > 0) {
                $result['code'] = 200;
            } else {
                $result['code'] = 1;
            }
        } else {
            $result['code'] = 0;
        }
        $this->response($result);
    }

    /**
     * ajax
     */
    public function saveDoor() {
        $controller_id = I('controller_id');
        $door_index = I('door_index');
        $door_name = I('door_name');
        if ($controller_id == null) {
            $error = "请选择控制器";
        }
        if ($door_index == null) {
            $error = "请选择门";
        }
        if (empty($door_name)) {
            $error = "请输入名称";
        }
        if ($error) {
            $result['code'] = 0;
            $result['message'] = $error;
            $this->response($result);
            exit;
        }
        if (!$this->checkDoorControllerAccess($controller_id)) {
            $result['code'] = 0;
            $result['message'] = "权限校验失败!";
            $this->response($result);
            exit;
        }

        $MDoor = M('Door');
        $data = $MDoor->where(array('controller_id'=> $controller_id, 'door_index'=>$door_index))->find();
        if ($data) {
            $data['name'] = $door_name;
            $result = $MDoor->save($data);
        } else {
            $data['controller_id'] = $controller_id;
            $data['door_index'] = $door_index;
            $data['name'] = $door_name;
            $result = $MDoor->add($data);
        }
        if ($result) {
            $result = array();
            $result['code'] = 200;
            $result['message'] = "保存成功";
            $this->response($result);
        }
    }

    protected function sendOpenDoorUdpCode($ip, $port, $serialNumber, $doorId, $wait) {
        $handle = stream_socket_client("udp://127.0.0.1:9998", $errno, $errstr);
        if( !$handle ){
            die("ERROR: {$errno} - {$errstr}\n");
        }
        $sendMsg = "30030001"; // 开门指令
        $ips = explode(".", $ip);
        foreach ($ips as $i) {
            $sendMsg .= sprintf("%02x", $i);
        }
        $sendMsg .= sprintf("%04x", $port);
        $sendMsg .= "01";
        $sendMsg .= $serialNumber;
        $sendMsg .= sprintf("%02x", $doorId);
        $sendMsg .= sprintf("%04x", $wait);
        $sendMsg = hex2bin($sendMsg);
        fwrite($handle, $sendMsg);
        fclose($handle);
    }

    private function checkDoorControllerAccess($controller_id) {
        if (session(C('ADMIN_AUTH_KEY'))) return true;
        $MDoorController = M("DoorController");
        $map['id'] = $controller_id;
        $map['company_id'] = session('company_id');
        $data = $MDoorController->where($map)->find();
        if ($data) return true;
        return false;
    }

}