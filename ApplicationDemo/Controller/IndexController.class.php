<?php
namespace Demo\Controller;
use Think\Controller;
class IndexController extends Controller {

    public function index(){
        $model = M('DoorControllerView');

        $map = array(
            "company_id"=>15,
            "status"=>0,
        );
        $controllerList = $model->where($map)->select();
        load("Home.Array");
        $ids = array_col_values($controllerList, "id");
        $map = array(
            "controller_id" => array("in", $ids),
        );
        $doorList = M("Door")->where($map)->select();
        foreach ($doorList as $door) {
            $doorMap[$door["controller_id"]][$door["door_index"]] = $door;
        }
        $arrList = array();
        foreach ($controllerList as $controller) {
            for ($i = 0; $i < $controller["door_count"]; $i++) {
                $door = $doorMap[$controller["id"]][$i];
                if (!$door) {
                    $door = array(
                        "controller_id" => $controller["id"],
                        "door_index" => $i,
                        "name" => $i."号门",
                    );
                }
                $arrList[] = $door;
            }
        }
        $this->assign('arrList', $arrList);
        $this->display();
    }

    public function add() {
        $this->display();
    }

    public function save() {
        $model = D('DoorController');

        $serialNumber = I('serial_number', '', 'trim,strtoupper');
        $productType = 2;
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
        $company_id = 15;

        // 当新增时，判断序列号是否被占用
        $map['serial_number'] = I('serial_number');
        $map['status'] = 0;
        $data = $model->where($map)->find();
        if ($data && $data['company_id']) {
            if ($data['company_id'] == $company_id) {
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

        if ($error) {
            $this->assign('vo', $_REQUEST);
            $this->assign('error', $error);
            $this->display('add');
            return;
        }

        $data = $model->create($_REQUEST);
        if(!$data){
            $error = $model->getError();
            $this->assign('vo',$_REQUEST);
            $this->assign('error',$error);
            $this->display('add');
        }else{
            $data['company_id'] = $company_id;
            $data['door_count'] = $doorCount;
            $result = $model->add($data);
            if($result){
                $this->success('数据已保存！',U("Index/index"));
            }else{
                $this->error('数据未保存！',U("Index/index"));
            }
        }
    }

    public function lists() {
        $map = array(
            "user_id" => 16,
        );
        $arrList = M('OpenRecord')->field("open_record.open_time as open_time, door.name as door_name, open_record.door_id as door_id")
            ->join("left join door on door.controller_id = open_record.controller_id and door.door_index = open_record.door_id")
            ->where($map)->order('open_time desc')->limit('0,50')->select();
        //var_dump($arrList);exit;
        foreach ($arrList as $k=>$v) {
            if (empty($v['door_name'])) {
                $arrList[$k]['door_name'] = $v['door_id']."号门";
            }
        }
        $this->assign('arrList', $arrList);
        $this->display();
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


        $data = M('DoorController')->find($controller_id);
        if ($data && $data["company_id"] == 15) {
            $openRecord['controller_id'] = $controller_id;
            $openRecord['door_id'] = $door_id;
            $openRecord['open_time'] = time();
            $openRecord['user_id'] = 16;
            $openRecord['way'] = 1;
            M('OpenRecord')->add($openRecord);

            $wait = intval($data['wait_time']);
            $this->sendOpenDoorUdpCode($data['ip'], $data['port'], $data['serial_number'], $door_id, $wait);
            $result['code'] = 200;
            $result['message'] = "OK";
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

    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @return void
     */
    protected function response($data) {
        header('HTTP/1.1 200 OK');
        header('Status:200 OK');
        exit(json_encode($data));
    }
}