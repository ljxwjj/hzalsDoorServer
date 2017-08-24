<?php
namespace Api\Controller;

class DoorControllerController extends CommonRestController {

    public function _filter(&$map) {
        if (session(C('ADMIN_AUTH_KEY'))) {
            $map['status'] = 1;
            $map['company_id'] = array('GT', 0);
        } else {
            $map['status'] = 1;
            $map['company_id'] = session("user")["company_id"];
        }
    }

    // 公司门禁清单
    public function lists() {
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $this->setMap($map,$search);

        $model = M("DoorControllerView");
        if (!empty($model)) {
            $this->_list($model, $map, 'id');
        }
        $result = $this->createResult(200, "", $this->voList);

        $this->response($result,'json');
    }

    public function detail(){
        parent::detail();
    }

    public function doorLists($sortBy = '', $asc = false) {
        $user = session('user');
        $map = array();
        $map['status'] = 0;
        if ($user['is_admin']) {

        } else {
            $map['company_id'] = $user['company_id'];

            $role_id = M('AuthRoleUser')->where(array('user_id'=>$user['id']))->getField('role_id');
            if ($role_id > 21) { // > 21即非管理员用户
                $userDoors = getUserDoors($user['id']);
                if ($userDoors) {
                    $controllerIds = array_keys($userDoors);
                } else {
                    $controllerIds = array();
                }
                $map['id'] = array("IN", $controllerIds);
            }
        }

        $model = M('DoorController');

        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = $asc ? 'asc' : 'desc';
        }
        //取得满足条件的记录数
        $count = $model->where($map)->count($model->getPk());

        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST ['_listRows'])) {
                $listRows = $_REQUEST ['_listRows'];
            } else {
                $listRows = C('LIST_ROWS');
            }
            $nowPage = I('page')?I('page'):1;
            $firstRow = $listRows * ($nowPage - 1);
            //分页查询数据
            $voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($firstRow . ',' . $listRows)->select();
        } else {
            $voList = array();
        }

        load("Home.Array");
        $controllerIds = array_col_values($voList, "id");
        $doors = M('Door')->where(array('controller_id'=> array("IN", $controllerIds)))->select();
        foreach ($doors as $door) {
            $doorMap[$door['controller_id']][$door['door_index']] = $door;
        }

        $doorList = array();
        foreach ($voList as $vo) {
            for ($i = 0; $i < $vo['door_count']; $i++) {
                if ($role_id > 21 && !$userDoors[$vo['id']][$i]) { // > 21即非管理员用户
                    continue;
                }
                $door = $doorMap[$vo['id']][$i];
                if (is_array($door)) {
                    $door['controller_name'] = $vo['name'];
                    $doorList[] = $door;
                } else {
                    $doorList[] = array('controller_id'=>$vo['id'], 'door_index'=>$i, 'name'=>$i."号门", 'controller_name'=>$vo['name']);
                }
            }
        }

        $result = $this->createResult(200, "", $doorList);
        $this->response($result,'json');
    }

    // 添加控制器
    public function add() {
        parent::add();
    }

    // 删除控制器
    public function del() {
        parent::del();
    }

    // 开门
    public function openDoor() {
        $user_id = I('user_id');
        $controller_id = I('controller_id');
        $door_id = I('door_id');

        if (!session(C('ADMIN_AUTH_KEY'))) {
            $role_id = M('AuthRoleUser')->where(array('user_id'=>$user_id))->getField('role_id');
            if ($role_id > 21) { // > 21即非管理员用户
                $userDoors = getUserDoors($user_id);
                if (!$userDoors[$controller_id][$door_id]) {
                    $result = $this->createResult(0, "授权失败");
                    $this->response($result,'json');
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
            $OpenRecord = M('OpenRecord');
            $OpenRecord->create($openRecord);
            $addid = $OpenRecord->add();

            if ($data['product_type'] == 1) {
                $rv = $this->sendOpenDoorHttp($data['ip'], $data['port'], $door_id, $data['password']);
                if ($rv) {
                    $result = $this->createResult(201, "开门成功");
                } else {
                    $result = $this->createResult(0, "开门失败");
                }
                $this->response($result,'json');
            } else {
                $wait = intval($data['wait_time']);
                $this->sendOpenDoorUdpCode($data['ip'], $data['port'], $data['serial_number'], $door_id, $wait);
                $result = $this->createResult(200, "开门成功", array("id"=>$addid));
                $this->response($result,'json');
            }
        } else {
            $result = $this->createResult(0, "开门失败");
            $this->response($result,'json');
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

    protected function sendOpenDoorHttp($ip, $port, $doorId, $password) {
        $opts = array(
            'http'=>array('method'=>'GET', 'timeout'=>5)
        );
        //创建数据流上下文
        $context = stream_context_create($opts);
        if (empty($password)) {
            $url = "http://$ip:$port/t.cgi?T,access_io,door,$doorId,1";
        } else {
            $url = "http://$ip:$port/t.cgi?T$password,access_io,door,$doorId,1";
        }
        $html =file_get_contents($url, false, $context);
        if (strcasecmp($html, "ok") === 0) {
            return true;
        }
        return false;
    }

    public function openDoorFeedBack() {
        $id = I('id');
        if ($id) {
            $feedbackTime = M('OpenRecord')->where(array('id'=>$id))->getField('feedback_time');
            if ($feedbackTime > 0) {
                $result = $this->createResult(200, "开门成功");
            } else {
                $result = $this->createResult(1, "开门中");
            }
        } else {
            $result = $this->createResult(0, "非法请求");
        }
        $this->response($result,'json');
    }

    public function cameras() {
        $user_id = I('user_id');
        $controller_id = I('controller_id');
        $door_id = I('door_id');

        if (!session(C('ADMIN_AUTH_KEY'))) {
            $role_id = M('AuthRoleUser')->where(array('user_id'=>$user_id))->getField('role_id');
            if ($role_id > 21) { // > 21即非管理员用户
                $userDoors = getUserDoors($user_id);
                if (!$userDoors[$controller_id][$door_id]) {
                    $result = $this->createResult(0, "授权失败");
                    $this->response($result,'json');
                    exit;
                }
            }
        }
        $map = array('controller_id'=>$controller_id, 'door_id'=>$door_id);
        $cameras = M('Camera')->where($map)->select();
        if (!$cameras) $cameras = array();
        $result = $this->createResult(200, "", $cameras);
        $this->response($result,'json');
    }

    public function allCameras() {
        $user = session('user');
        $user_id = I('user_id');
        $company_id = I('company_id');

        $map = array();
        if ($user['is_admin']) {
            if ($company_id) {
                $controllerIds = M('DoorController')->where(array('company_id'=>$company_id))->getField('id', true);
                $map['controller_id'] = array('in', $controllerIds);
            }
        } else {
            $role_id = M('AuthRoleUser')->where(array('user_id'=>$user_id))->getField('role_id');
            if ($role_id > 21) { // > 21即非管理员用户
                $userDoors = getUserDoors($user_id);
                foreach ($userDoors as $controllerId=>$doorIds) {
                    foreach ($doorIds as $doorId=>$v) {
                        $and = array();
                        $and['controller_id'] = $controllerId;
                        $and['door_id'] = $doorId;
                        $map[] = $and;
                    }
                }
                $map['_logic'] = "or";
            } else {
                $controllerIds = M('DoorController')->where(array('company_id'=>$user['company_id']))->getField('id');
                $map['controller_id'] = array('in', $controllerIds);
            }
        }

        $model = M('Camera');

        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = 'asc';
        }
        //取得满足条件的记录数
        $count = $model->where($map)->count($model->getPk());

        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST ['_listRows'])) {
                $listRows = $_REQUEST ['_listRows'];
            } else {
                $listRows = C('LIST_ROWS');
            }
            $nowPage = I('page')?I('page'):1;
            $firstRow = $listRows * ($nowPage - 1);
            //分页查询数据
            $voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($firstRow . ',' . $listRows)->select();
        } else {
            $voList = array();
        }

        $result = $this->createResult(200, "", $voList);
        $this->response($result,'json');
    }
}