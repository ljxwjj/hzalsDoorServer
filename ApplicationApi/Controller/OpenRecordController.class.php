<?php
namespace Api\Controller;

class OpenRecordController extends CommonRestController {

    public function _filter(&$map) {
        if (session(C('ADMIN_AUTH_KEY'))) {

        } else {
            $user_id = session("user")["id"];
            $role_id = M('AuthRoleUser')->where(array('user_id'=>$user_id))->getField('role_id');
            if ($role_id > 21) { // > 21即非管理员用户
                $map['user_id'] = $user_id;
            } else {
                $map['company_id'] = session("user")["company_id"];
            }
        }
    }

    /**
     * 默认依据url传参，生成搜索条件*
     *
     * @param array $map 查询数组
     */
    protected function setMap(&$map){
        foreach ($_REQUEST as $key => $val) {
            if($val == "") {
                continue;
            }
            if (ereg("^search_", $key)) {
                $field = str_replace('search_','',$key);

                if ($field == 'open_time_start') {
                    $map['open_time'][] = array('EGT', $val + 0);
                } else if ($field == 'open_time_end') {
                    $map['open_time'][] = array('ELT', $val + 0);
                } else if ($field == 'door_controller_id') {
                    $map['controller_id'] = $val;
                } else {
                    $map[$field] = $val;
                }
            }
        }
    }

    public function lists() {
        $map = array();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $this->setMap($map);

        $model = D('OpenRecordView');
        if ($model) {
            $this->_list($model, $map, 'id');
        }
        $voList = $this->voList;
        foreach ($voList as $i=>$vo) {
            if (empty($vo['door_name'])) {
                $voList[$i]['door_name'] = $vo['door_id']."号门";
            }
        }
        $result = $this->createResult(200, "", $voList);

        $this->response($result,'json');
    }

    public function attendance() {
        $timeStart = I("search_time_start");
        $timeEnd = I("search_time_end");

        if (empty($timeStart)) {
            $this->response($this->createResult(0, "请指定开始时间！"), "json");
            return;
        }
        if (empty($timeEnd)) {
            $this->response($this->createResult(0, "请指定结束时间！"), "json");
            return;
        }

        $model = M('OpenRecordView');
        $map = array();
        $map["open_time"][] = array('egt',strtotime($timeStart));
        $map["open_time"][] = array('lt',strtotime($timeEnd)+86400);
        $map["way"] = array('in', "2,5");
        $userIds = I("search_user_ids");
        if ($userIds) {
            $strIds = explode(',',$userIds);
            $map["user_id"] = array("in", $strIds);
        } else {
            $map["company_id"] = session("user")["company_id"];
        }
        $voList = $this->_attendance_list($model, $map, $timeStart, $timeEnd);

        $resultList = array();
        foreach ($voList as $vo) {
            $item = array();
            $item["user_id"] = $vo["user_id"];
            $item["user_nickname"] = $vo["user_nickname"];
            $item["work_day_count"] = $vo["work_day_count"];
            $item["late_day_count"] = $vo["late_day_count"];
            $item["leave_day_count"] = $vo["leave_day_count"];
            $item["absenteeism_day_count"] = $vo["absenteeism_day_count"];
            $item["overtime_day_count"] = $vo["overtime_day_count"];
            $item["overtime_day_time"] = toWorkHours($vo["overtime_day_time"]);
            $resultList[] = $item;
        }
        $result = $this->createResult(200, "", $resultList);

        $this->response($result,'json');

    }

    private function _attendance_list($model, $map, $timeStart, $timeEnd) {
        $dateStart = strtotime($timeStart);
        $dateEnd = strtotime($timeEnd);
        $companyId = session("user")["company_id"];
        $dateLine = array();
        while ($dateStart <= $dateEnd) {
            $dateLine[$timeStart] = isWorkDay($timeStart);
            $dateStart = strtotime("$timeStart +1 day");
            $timeStart = date("Y-m-d", $dateStart);
        }

        $smap['code_name'] = array('LIKE', 'attendance_%');
        $smap['company_id'] = $companyId;
        $setting = M('AppSetting')->where($smap)->getField('code_name,code_value');
        $WORK_TIME = timeToTimeLong($setting['attendance_1']);
        $CLOSING_TIME = timeToTimeLong($setting['attendance_2']);

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
        if (is_array($map["user_id"])) {
            if ($attendance) {
                $attendanceUsers = array_keys($attendance);
                $attendanceUsers = array_diff($map["user_id"][1], $attendanceUsers);
                $attendanceUsers = array_values($attendanceUsers);
                $unAttendanceUsers = $userModel->where(array("company_id"=>$companyId, "id"=>array("in", $attendanceUsers), "status"=>1))->select();
            } else {
                $unAttendanceUsers = $userModel->where(array("company_id"=>$companyId, "id"=>array("in", $map["user_id"][1]), "status"=>1))->select();
            }
        } else {
            if ($attendance) {
                $attendanceUsers = array_keys($attendance);
                $unAttendanceUsers = $userModel->where(array("company_id"=>$companyId, "id"=>array("not in", $attendanceUsers), "status"=>1))->select();
            } else {
                $unAttendanceUsers = $userModel->where(array("company_id"=>$companyId, "status"=>1))->select();
            }
        }

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
                    if (dateToTimeLong($dayInfo['begin']) - $WORK_TIME > $setting['attendance_5'] * 60) {// 上班旷工
                        $attendance[$userId]['absenteeism_day_count'] += 1;
                    } else if ($CLOSING_TIME - dateToTimeLong($dayInfo['end']) > $setting['attendance_5'] * 60) {// 下班旷工
                        $attendance[$userId]['absenteeism_day_count'] += 1;
                    } else {
                        if (dateToTimeLong($dayInfo['begin']) - $WORK_TIME > $setting['attendance_3'] * 60) {// 迟到
                            $attendance[$userId]['late_day_count'] += 1;
                        }
                        if ($CLOSING_TIME - dateToTimeLong($dayInfo['end']) > $setting['attendance_5'] * 60) {// 早退
                            $attendance[$userId]['leave_day_count'] += 1;
                        }
                    }

                    if (dateToTimeLong($dayInfo['end']) - $CLOSING_TIME > $setting['attendance_6'] * 60) {// 加班
                        $attendance[$userId]['overtime_day_count'] += 1;
                        $attendance[$userId]['overtime_day_time'] += (dateToTimeLong($dayInfo['end']) - $CLOSING_TIME);
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
        return $attendance;
    }

    public function attendanceDetail() {

        $timeStart = I("search_time_start");
        $timeEnd = I("search_time_end");
        $userId = I("id", 0, "int");

        if (empty($timeStart)) {
            $this->response($this->createResult(0, "请指定开始时间！"), "json");
            return;
        }
        if (empty($timeEnd)) {
            $this->response($this->createResult(0, "请指定结束时间！"), "json");
            return;
        }
        if ($userId === 0) {
            $this->response($this->createResult(0, "请指定用户ID！"), "json");
            return;
        }

        $model = D('OpenRecordView');
        $map = array();
        $map["open_time"][] = array('egt',strtotime($timeStart));
        $map["open_time"][] = array('lt',strtotime($timeEnd)+86400);
        $map["way"] = array('in', "2,5");
        $map["user_id"] = $userId;
        $voList = $this->_attendance_detail_list($model, $map, $timeStart, $timeEnd);

        $resultList = array();
        foreach ($voList as $key=>$vo) {
            $item = array();
            $item["day"] = $key;
            $item["week"] = dateToWeek($key);
            $item["begin"] = toDate($vo['begin'], 'H#i');
            $item["end"] = toDate($vo['end'], 'H#i');
            $item["absenteeism"] = $vo["absenteeism"];
            $item["late"] = $vo["late"];
            $item["leave"] = $vo["leave"];
            $item["overtime"] = toWorkHours($vo["overtime"]);
            $resultList[] = $item;
        }
        $result = $this->createResult(200, "", $resultList);
        $this->response($result,'json');
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
        $smap['company_id'] = session("user")["company_id"];
        $setting = M('AppSetting')->where($smap)->getField('code_name,code_value');
        $WORK_TIME = timeToTimeLong($setting['attendance_1']);
        $CLOSING_TIME = timeToTimeLong($setting['attendance_2']);

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
                if (dateToTimeLong($dayInfo['begin']) - $WORK_TIME > $setting['attendance_5'] * 60) {// 上班旷工
                    $attendance[$day]['absenteeism'] = "旷工";
                } else if ($CLOSING_TIME - dateToTimeLong($dayInfo['end']) > $setting['attendance_5'] * 60) {// 下班旷工
                    $attendance[$day]['absenteeism'] = "旷工";
                } else {
                    if (dateToTimeLong($dayInfo['begin']) - $WORK_TIME > $setting['attendance_3'] * 60) {// 迟到
                        $attendance[$day]['late'] = "迟到";
                    }
                    if ($CLOSING_TIME - dateToTimeLong($dayInfo['end']) > $setting['attendance_5'] * 60) {// 早退
                        $attendance[$day]['leave'] = "早退";
                    }
                }

                if (dateToTimeLong($dayInfo['end']) - $CLOSING_TIME > $setting['attendance_6'] * 60) {
                    $attendance[$day]['overtime'] = (dateToTimeLong($dayInfo['end']) - $CLOSING_TIME);
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
        ksort($attendance);
        return $attendance;
    }
}