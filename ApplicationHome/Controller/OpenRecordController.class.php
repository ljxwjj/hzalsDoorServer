<?php
namespace Home\Controller;

use Lib\ORG\Util\Page;

class OpenRecordController extends CommonController {

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"出入记录");
    }

    protected function _filter(&$map) {
        if (session(C('ADMIN_AUTH_KEY'))) {
            // 管理员不做任何限制

        } else {
            // 判断用户角色
            $user_id = session(C('USER_AUTH_KEY'));
            $role_id = M('AuthRoleUser')->where(array('user_id'=> $user_id))->getField('role_id');
            if (in_array($role_id, array(18, 19))) {
                // 系统管理员不做任何限制
            } else if (in_array($role_id, array(20, 21))) {
                // 客户管理员
                $map['company_id'] = session('company_id');
            } else {
                // 普通用户
                $map['user_id'] = $user_id;
            }
        }
    }

    public function index(){
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $this->setMap($map,$search);
        $this->addSearchCondition($map);
        if (I('controller_id')) {
            $map['controller_id'] = I('controller_id');
        }

        $this->keepSearch();
        $model = M('OpenRecordView');
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
     * 设置查询条件*
     *
     * @param array $map  查询条件
     * @param array $search 搜索数组
     */
    protected function setMap(&$map,&$search){
        $model = M("OpenRecordView");
        $dbFields = $model->getDbFields();
        foreach ($_REQUEST as $key => $val) {
            if($val == "") {
                continue;
            }
            if (ereg("^search_", $key)) {
                $field = str_replace('search_','',$key);
                $search[$key] = $val;

                if(in_array($field,$dbFields)){
                    switch($field){
                        case 'controller_name':
                        case 'user_nickname':
                            $map[$field] = array('like',"%$val%");
                            break;
                        default:
                            $map[$field] = $val;
                            break;
                    }
                }
            }
        }
    }

    protected function addSearchCondition(&$map,$child=0) {
        $searchPrefix = $child ? 'search_child_' : 'search_'.'' ;
        if($_REQUEST[$searchPrefix.'open_time_start'] != ''){
            $map["open_time"][] = array('egt',strtotime(I($searchPrefix.'open_time_start')));
        }
        if($_REQUEST[$searchPrefix.'open_time_end'] != ''){
            $map["open_time"][] = array('lt',strtotime(I($searchPrefix.'open_time_end'))+86400);
        }
    }

    public function attendance() {

        $timeStart = I("search_time_start");
        $timeEnd = I("search_time_end");

        $this->keepSearch();

        if (!empty($timeStart) && !empty($timeEnd)) {
            $model = M('OpenRecordView');
            $map = array();
            $map["open_time"][] = array('egt',strtotime($timeStart));
            $map["open_time"][] = array('lt',strtotime($timeEnd)+86400);
            $map["way"] = array('in', "2,5");
            $userIds = I("search_user_ids");
            if ($userIds) {
                $strIds = explode(',',$userIds);
                foreach ($strIds as $strId) {
                    $ids[] = substr($strId, 2);
                }
                $map["user_id"] = array("in", $ids);
            } else {
                $map["company_id"] = session('company_id');
            }
            $this->_attendance_list($model, $map, $timeStart, $timeEnd);

            $voList = $this->voList;
            $this->assign('list', $voList);
        }
        $this->display();
    }

    private function _attendance_list($model, $map, $timeStart, $timeEnd) {
        $dateStart = strtotime($timeStart);
        $dateEnd = strtotime($timeEnd);
        $companyId = session('company_id');
        $dateLine = array();
        while ($dateStart <= $dateEnd) {
            $dateLine[$timeStart] = isWorkDay($timeStart);
            $dateStart = strtotime("$timeStart +1 day");
            $timeStart = date("Y-m-d", $dateStart);
        }

        $smap['code_name'] = array('LIKE', 'attendance_%');
        $smap['company_id'] = $companyId;
        $setting = M('AppSetting')->where($smap)->getField('code_name,code_value');
        $ONE_DAY = 24*60*60;
        $WORK_TIME = hitotime($setting['attendance_1']);
        $CLOSING_TIME = hitotime($setting['attendance_2']);

        //分页查询数据
        $voList = $model->where($map)->order("`open_time` asc")->select();
        $attendance = array();
        foreach ($voList as $record) {
            $userId = $record['user_id'];
            $day = date('Y-m-d',$record['open_time']);
            $attendance[$userId]['user_id'] = $userId;
            $attendance[$userId]['user_nickname'] = $record['user_nickname'];
            if (is_array($attendance[$userId]['work_day'][$day])) {
                $attendance[$userId]['work_day'][$day]['end'] = $record['open_time'];
            } else {
                $attendance[$userId]['work_day'][$day] = array('begin'=>$record['open_time'], 'end'=>$record['open_time']);
            }
        }
        $userModel = M("User");
        $attendanceUsers = array_keys($attendance);
        $unAttendanceUsers = $userModel->where(array("company_id"=>$companyId, "id"=>array("not in", $attendanceUsers), "status"=>1))->select();
        foreach ($unAttendanceUsers as $user) {
            $userId = $user["id"];
            $attendance[$userId]['user_id'] = $user["id"];
            $attendance[$userId]['user_nickname'] = $user['nickname'];
        }
        foreach ($attendance as $userId=>$detail) {
            $attendance[$userId]['work_day_count'] = count($detail['work_day']);  // 出勤
            $attendance[$userId]['late_day_count'] = 0;               // 迟到
            $attendance[$userId]['leave_day_count'] = 0;              // 早退
            $attendance[$userId]['absenteeism_day_count'] = 0;        // 旷工
            $attendance[$userId]['overtime_day_count'] = 0;           // 加班
            $attendance[$userId]['overtime_day_time'] = 0;            // 加班时长
            foreach ($detail['work_day'] as $day=>$dayInfo) {
                if (isWorkDay($day)) {
                    if ($dayInfo['begin'] % $ONE_DAY - $WORK_TIME > $setting['attendance_5'] * 60) {// 上班旷工
                        $attendance[$userId]['absenteeism_day_count'] += 1;
                    } else if ($CLOSING_TIME - $dayInfo['end'] % $ONE_DAY > $setting['attendance_5'] * 60) {// 下班旷工
                        $attendance[$userId]['absenteeism_day_count'] += 1;
                    } else {
                        if ($dayInfo['begin'] % $ONE_DAY - $WORK_TIME > $setting['attendance_3'] * 60) {// 迟到
                            $attendance[$userId]['late_day_count'] += 1;
                        }
                        if ($CLOSING_TIME - $dayInfo['end'] % $ONE_DAY > $setting['attendance_5'] * 60) {// 早退
                            $attendance[$userId]['leave_day_count'] += 1;
                        }
                    }

                    if ($dayInfo['end'] % $ONE_DAY - $CLOSING_TIME > $setting['attendance_6'] * 60) {// 加班
                        $attendance[$userId]['overtime_day_count'] += 1;
                        $attendance[$userId]['overtime_day_time'] += ($dayInfo['end'] % $ONE_DAY - $CLOSING_TIME);
                    }
                } else {
                    $attendance[$userId]['overtime_day_count'] += 1;
                    $attendance[$userId]['overtime_day_time'] += ($dayInfo['end'] - $dayInfo['begin']);
                }
            }
            //  未上班的工作日计旷工
            foreach ($dateLine as $day=>$isWorkDay) {
                if ($isWorkDay && !is_array($attendance[$userId]['work_day'][$day])) {
                    $attendance[$userId]['absenteeism_day_count'] += 1;
                }
            }
        }
        $this->voList = $attendance;
        cookie('_currentUrl_', __SELF__);
        return;
    }

    public function attendanceDetail() {

        $timeStart = I("search_time_start");
        $timeEnd = I("search_time_end");
        $userId = I("id", 0, "int");

        $this->keepSearch();

        if ($userId) {
            $vo = M("User")->where("id=$userId")->find($userId);
            $this->assign('vo', $vo);
        }

        if (!empty($timeStart) && !empty($timeEnd) && $userId > 0) {
            $model = M('OpenRecordView');
            $map = array();
            $map["open_time"][] = array('egt',strtotime($timeStart));
            $map["open_time"][] = array('lt',strtotime($timeEnd)+86400);
            $map["way"] = array('in', "2,5");
            $map["user_id"] = $userId;
            $this->_attendance_detail_list($model, $map, $timeStart, $timeEnd);

            $voList = $this->voList;
            $this->assign('list', $voList);
        }
        $this->display();
    }

    private function _attendance_detail_list($model, $map, $timeStart, $timeEnd) {
        $dateStart = strtotime($timeStart);
        $dateEnd = strtotime($timeEnd);
        $dateLine = array();
        while ($dateStart <= $dateEnd) {
            $dateLine[$timeStart] = isWorkDay($timeStart);
            $dateStart = strtotime("$timeStart +1 day");
            $timeStart = date("Y-m-d", $dateStart);
        }

        $smap['code_name'] = array('LIKE', 'attendance_%');
        $smap['company_id'] = session('company_id');
        $setting = M('AppSetting')->where($smap)->getField('code_name,code_value');
        $ONE_DAY = 24*60*60;
        $WORK_TIME = hitotime($setting['attendance_1']);
        $CLOSING_TIME = hitotime($setting['attendance_2']);

        //分页查询数据
        $voList = $model->where($map)->order("`open_time` asc")->select();
        $attendance = array();
        foreach ($voList as $record) {
            $day = date('Y-m-d',$record['open_time']);
            if (is_array($attendance[$day])) {
                $attendance[$day]['end'] = $record['open_time'];
            } else {
                $attendance[$day] = array('begin'=>$record['open_time'], 'end'=>$record['open_time']);
            }
        }
        foreach ($attendance as $day=>$dayInfo) {
            if (isWorkDay($day)) { // 工作日打卡
                if (($dayInfo['begin']%$ONE_DAY) - $WORK_TIME > $setting['attendance_5'] * 60) {// 上班旷工
                    $attendance[$day]['absenteeism'] = "旷工";
                } else if ($CLOSING_TIME - ($dayInfo['end']%$ONE_DAY) > $setting['attendance_5'] * 60) {// 下班旷工
                    $attendance[$day]['absenteeism'] = "旷工";
                } else {
                    if ($dayInfo['begin']%$ONE_DAY - $WORK_TIME > $setting['attendance_3'] * 60) {// 迟到
                        $attendance[$day]['late'] = "迟到";
                    }
                    if ($CLOSING_TIME - $dayInfo['end']%$ONE_DAY > $setting['attendance_5'] * 60) {// 早退
                        $attendance[$day]['leave'] = "早退";
                    }
                }

                if ($dayInfo['end']%$ONE_DAY - $CLOSING_TIME > $setting['attendance_6'] * 60) {
                    $attendance[$day]['overtime'] = ($dayInfo['end']%$ONE_DAY - $CLOSING_TIME);
                }
            } else { // 非工作日打卡
                $attendance[$day]['overtime'] = ($dayInfo['end'] - $dayInfo['begin']);
            }
        }
        //  未上班的工作日计旷工
        foreach ($dateLine as $day=>$isWorkDay) {
            if ($isWorkDay && !is_array($attendance[$day])) {
                $attendance[$day]['absenteeism'] = "旷工";
            }
        }

        $this->voList = $attendance;
        cookie('_currentUrl_', __SELF__);
        return;
    }

    public function attendanceConfig() {
        $mode = I("mode");
        $attendance_1 = I("attendance_1");
        $attendance_2 = I("attendance_2");
        $attendance_3 = I("attendance_3", -1, "int");
        $attendance_4 = I("attendance_4", -1, "int");
        $attendance_5 = I("attendance_5", -1, "int");
        $attendance_6 = I("attendance_6", -1, "int");
        if (strcasecmp($mode, "save") === 0) {
            if (strtotime($attendance_1) === false) {
                $error["attendance_1"] = "时间格式错误";
            }
            if (strtotime($attendance_2) === false) {
                $error["attendance_2"] = "时间格式错误";
            }
            if ($attendance_3 < 0) {
                $error["attendance_3"] = "请输入一个正常数";
            }
            if ($attendance_4 < 0) {
                $error["attendance_4"] = "请输入一个正常数";
            }
            if ($attendance_5 < 0) {
                $error["attendance_5"] = "请输入一个正常数";
            }
            if ($attendance_6 < 0) {
                $error["attendance_6"] = "请输入一个正常数";
            }
            if ($error) {
                $this->assign("error", $error);
            } else {
                $this->saveAttendanceConfig("attendance_1", $attendance_1);
                $this->saveAttendanceConfig("attendance_2", $attendance_2);
                $this->saveAttendanceConfig("attendance_3", $attendance_3);
                $this->saveAttendanceConfig("attendance_4", $attendance_4);
                $this->saveAttendanceConfig("attendance_5", $attendance_5);
                $this->saveAttendanceConfig("attendance_6", $attendance_6);
                $this->assign("save_success", true);
            }
        }
        $this->loadAttendanceConfig();
        $this->display();
    }

    private function loadAttendanceConfig() {
        $map['code_name'] = array('LIKE', 'attendance_%');
        $map['company_id'] = session('company_id');
        $vo = M('AppSetting')->where($map)->getField('code_name,code_value');
        if (empty($vo['attendance_1'])) $vo['attendance_1'] = "09:00";
        if (empty($vo['attendance_2'])) $vo['attendance_2'] = "17:00";
        $this->assign('vo', $vo);
    }

    private function saveAttendanceConfig($key, $value) {
        $companyId = session('company_id');
        $model = M('AppSetting');
        $item = $model->where(array("code_name"=>$key, "company_id"=>$companyId))->find();
        if ($item) {
            $item["code_value"] = $value;
            $model->save($item);
        } else {
            $item = array("code_name"=>$key, "code_value"=>$value, "company_id"=>$companyId);
            $model->add($item);
        }
    }

    /**
     * ajax
     */
    public function allUserTree() {
        $companyId = session('company_id');
        $departments = M("Department")->where(array("company_id"=>$companyId, "status"=>1))->getField("id, name");
        $users = M("User")->where(array("company_id"=>$companyId, "status"=>1))->getField("id, nickname");

        $departmentIds = array_keys($departments);
        $departmentUsers = M("UserDepartment")->where(array("department_id"=>array("in", $departmentIds)))->select();
        $dus = array();// 在部门下的用户ids
        $departmentUserIds = array();
        foreach ($departmentUsers as $item) {
            $departmentUserIds[$item["department_id"]][] = $item["user_id"];
            $dus[] = $item["user_id"];
        }

        $jsonData = array();
        foreach ($departments as $did=>$dname) {
            $departmentJson = array("id"=>"d_$did", "text"=>$dname, "state"=>array("selected"=>true), "type"=>"department");
            $userIds = $departmentUserIds[$did];
            if ($userIds) {
                $dusers = array();
                foreach ($userIds as $uid) {
                    $dusers[] = array("id"=>"u_$uid", "text"=>$users[$uid], "state"=>array("selected"=>true), "type"=>"user");
                }
                $departmentJson["children"] = $dusers;
            }
            $jsonData[] = $departmentJson;
        }
        foreach ($users as $uid=>$uname) {
            if (!in_array($uid, $dus)) {
                $jsonData[] = array("id"=>"u_$uid", "text"=>$uname, "state"=>array("selected"=>true), "type"=>"user");
            }
        }

        $this->response($jsonData);
    }
}