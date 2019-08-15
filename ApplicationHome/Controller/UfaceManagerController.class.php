<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/8
 * Time: 13:55
 */

namespace Home\Controller;


class UfaceManagerController extends CommonController {

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"UFACE设备管理");
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
        } else {
            $map['company_id'] = array('NEQ', 0);
        }
    }

    /**
     * 设置查询条件*
     *
     * @param array $map  查询条件
     * @param array $search 搜索数组
     */
    protected function setMap(&$map,&$search){
        $model = M("UfaceDevice");
        $dbFields = $model->getDbFields();
        foreach ($_REQUEST as $key => $val) {
            if($val == "") {
                continue;
            }
            if (ereg("^search_", $key)) {
                $field = str_replace('search_','',$key);

                if(in_array($field,$dbFields)) {
                    switch ($field) {
                        case 'name':
                            $map[$field] = array('like', "%" . $val . "%");
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
    }

    public function index($name = "")
    {
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
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
        $model = M('UfaceDevice');
        if (!empty($model)) {
            $this->_list($model, $map, 'id');
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
        $model = D('UfaceDevice');
        $id = (int)I($model->getPk());
        if (empty($id)) {
            $this->error('请选择要编辑的数据！');
            exit;
        }
        $vo = $model->getById($id);
        $vo['door'] = $vo['controller_id']."_".$vo['door_id'];
        if($vo){
            $this->assign('vo', $vo);
            $this->assign('doorList', $this->getDoorByCompany($vo['company_id']));
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
        $vo['door'] = 'a';

        $this->assign('vo', $vo);
        $this->assign('doorList', $this->getDoorByCompany($vo['company_id']));
        $this->keepSearch();

        $this->display("edit");
    }


    /**
     * 保存*
     *
     */
    public function save() {
        $this->keepSearch();
        $id = I('id');
        $deviceKey = I('device_key', '', 'trim,strtoupper');

        $model = D('UfaceDevice');

        if(empty($deviceKey)) {
            $error['device_key']='序列号不能为空！';
        }

        if (empty(I('company_id'))) {
            $_REQUEST['company_id'] = session('company_id');
        } else {
            if (I('company_id') != session('company_id') && !session(C('ADMIN_AUTH_KEY'))) {
                $error['company_id']='非法操作！';
            }
        }
        $door = I('door');
        if ($con_door = explode("_", $door)) {
            $_REQUEST["controller_id"] = $con_door[0];
            $_REQUEST["door_id"] = $con_door[1];
        }
        if ($id) {
            // 编辑时，当序列号变更时，判断序列号是否被占用
            $data = $model->find($id);
            if ($deviceKey != $data['device_key']) {
                $map['device_key'] = $deviceKey;
                if ($model->where($map)->find()) {
                    $error['serial_number']='序列号已经被绑定！';
                }
            }
        } else {
            // 当新增时，判断序列号是否被占用
            $map['device_key'] = $deviceKey;
            $data = $model->where($map)->find();
            if ($data) {
                if ($data['company_id'] == $_REQUEST['company_id']) {
                    $error['device_key']='该序列号不能重复绑定！';
                } else {
                    $error['device_key']='序列号已经被其它用户绑定！';
                }
            }
        }
        if (!$error) {
            if ($id) {
                $response = ufaceApiAutoParams('put', array(
                    C('UFACE_APP_ID'), '/device/', $deviceKey
                ), array(
                    'appId'    => C('UFACE_APP_ID'),
                    'deviceKey'=> $deviceKey,
                    'name'     => I('name'),
                    'tag'      => I('company_id'),
                ));
                if ($response->result != 1) {
                    $error['device_key'] = $response->msg;
                }
            } else {
                $response = ufaceApiAutoParams('post', array(
                    C('UFACE_APP_ID'), '/device'
                ), array(
                    'appId'    => C('UFACE_APP_ID'),
                    'deviceKey'=> $deviceKey,
                    'name'     => I('name'),
                    'tag'      => I('company_id'),
                ));
                if ($response->result != 1) {
                    $error['device_key'] = $response->msg;
                }
            }
        }
        if ($error) {
            $this->assign('vo', $_REQUEST);
            $this->assign('error', $error);
            if(!$id){
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
            if(!$id){
                $this->display('add');
            }else{
                $this->display('edit');
            }
        }else{
            $data['device_key'] = $deviceKey;
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
        $model = M('UfaceDevice');
        $condition = array('id' => $id);
        $vo = $model->where($condition)->find();
        if ($vo) {
            $response = ufaceApiAutoParams('get', array(
                C('UFACE_APP_ID'), "/device/", $vo['device_key']
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'deviceKey' => $vo['device_key'],
            ));
            if ($response->result == 1) {
                $vo = (array)$response->data;
                $vo['id'] = $id;
                $this->assign('vo', $vo);
                $this->display();
            } else {
                $this->error($response->msg);
            }

        } else {
            $this->error("页面未找到");
        }
    }

    /**
     * 默认删除操作
     * @param string $name 数据对象
     * @return string
     */
    public function del($name="") {
        $model = M('UfaceDevice');
        $id = I('id');
        $condition = array('id' => $id);
        $vo = $model->where($condition)->find();
        if ($vo) {
            $response = ufaceApiAutoParams('delete', array(
                C('UFACE_APP_ID'), "/device/", $vo['device_key']
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'deviceKey' => $vo['device_key'],
            ));

            if ($response->result == 1) {
                $list = $model->where($condition)->delete();
                if ($list !== false) {
                    $this->success('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            }

        } else {
            $this->error("页面未找到");
        }
    }

    public function deviceAuthorize() {
        $model = M('UfaceDevice');
        $id = I('id');
        $condition = array('id' => $id);
        $vo = $model->where($condition)->find();
        if ($vo) {
            $response = ufaceApiAutoParams('get', array(
                C('UFACE_APP_ID'), "/device/", $vo['device_key'], '/people'
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'deviceKey' => $vo['device_key'],
            ));

            $map = array('company_id'=>$vo['company_id'], 'status'=>array('in', "0,1"));
            $users = M('User')->where($map)->getField("id, account, nickname");
            if ($response->result == 1) {
                $peoplesPhone = array();
                foreach ($response->data as $people) {
                    $peoplesPhone[] = $people->phone;
                }
                foreach ($users as $i=>$user) {
                    $users[$i]['checked'] = in_array($user['account'], $peoplesPhone);
                }
            }
            $this->assign('vo', $vo);
            $this->assign("arrList", $users);
            $this->display();
        } else {
            $this->error("页面未找到");
        }
    }

    public function saveDeviceAuthorize() {
        $model = M('UfaceDevice');
        $id = I('id');
        $condition = array('id' => $id);
        $vo = $model->where($condition)->find();
        if ($vo) {
            // 查询设备授权人员列表
            $response = ufaceApiAutoParams("get", array(
                C('UFACE_APP_ID'), "/device/", $vo['device_key'], '/people'
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'deviceKey' => $vo['device_key'],
            ));
            $oldGuids = array();
            if ($response->result == 1) {
                foreach ($response->data as $people) {
                    $oldGuids[] = $people->guid;
                }
            }

            $peoples = I("peopleId");
            $map = array('company_id'=>$vo['company_id'], 'status'=>array('in', "0,1"), 'id'=>array('in', $peoples));
            $users = M('User')->where($map)->getField("id, account, nickname");
            $guids = array();
            foreach ($users as $user) {
                $guids[] = $this->getUfaceGuidByUser($user);
            }

            $delGuids = array_diff($oldGuids, $guids);
            $addGuids = array_diff($guids, $oldGuids);

            // 取消勾选人员，消权
            $response = ufaceApiAutoParams('post', array(
                C('UFACE_APP_ID'), "/device/", $vo['device_key'], '/people/delete'
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'deviceKey' => $vo['device_key'],
                'personGuids' => implode(",", $delGuids),
            ));

            // 新增勾选人员，授权
            $response = ufaceApiAutoParams('post', array(
                C('UFACE_APP_ID'), "/device/", $vo['device_key'], '/people'
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'deviceKey' => $vo['device_key'],
                'personGuids' => implode(",", $addGuids),
                'passTimes'   => '00:00:00,23:59:59',
            ));

            $this->success('数据已保存！',$this->getReturnUrl());
        } else {
            $this->error("页面未找到");
        }
    }

    private function getUfaceGuidByUser($user) {
        $userid = $user['id'];
        $model = M('UfaceUser');
        $userGuid = $model->where("user_id=$userid")->getField('uface_guid');
        if ($userGuid) {
            return $userGuid;
        } else {
            $response = ufaceApiAutoParams('post', array(
                C('UFACE_APP_ID'), "/person"
            ), array(
                'appId' => C('UFACE_APP_ID'),
                'name' => $user['nickname'],
                'phone' => $user['account'],
                'type'  => $user['id'],
            ));
            if ($response->result == 1) {
                $guid = $response->data->guid;
                $model->add(array(
                    'user_id' => $user['id'],
                    'uface_guid' => $guid,
                ));
                return $guid;
            }
        }
    }

    private function getDoorByCompany($id) {
        //取得所有门禁
        $Node = D('DoorController');
        $map = array();
        $map['company_id'] = $id;
        $map['status'] = 0;
        $controllers = $Node->where($map)->getField("id,name,door_count");

        $cid = array_keys($controllers);
        $map = array('controller_id'=>array('in', $cid));
        $doors = M('Door')->where($map)->select();
        foreach ($doors as $door) {
            $doorsMap[$door['controller_id']][$door['door_index']] = $door;
        }

        $arrTree = array();
        foreach ($controllers as $value) {
            for ($j = 0; $j < $value['door_count']; $j++) {
                $door = $doorsMap[$value['id']][$j];
                if (!$door) {
                    $door = array();
                    $door['controller_id'] = $value['id'];
                    $door['door_index'] = $j;
                    $door['name'] = $j."号门";
                }
                $door['controller_name'] = $value['name'];
                $door['door_key'] = $door['controller_id'].'_'.$door['door_index'];
                $door['display_name'] = $door['controller_name'].'-'.$door['name'];
                $arrTree[]=$door;
            }
        }
        return $arrTree;
    }

}