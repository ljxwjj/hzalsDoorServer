<?php
namespace Udp\Controller;
use Think\Controller;

class JpushController extends Controller\RestController {

    public function test() {// 裕华测试：1a0018970a8b0d196d8    效果：13165ffa4e258cb8da3      A8:120c83f76009f2fa771
        //jpushToUser("170976fa8a8a6c38aa2", "通知通知 明天放假");
        jpush("推送到tag [all], 无参");
        echo "推送成功";
    }

    // 上下班打卡提醒
    public function workCheckinNotification() {
        if (!isWorkDay(date("Y-m-d"))) {
            return;
        }

        $NOW_TIME = dateToTimeLong(time());

        $model = M('AppSetting');
        $items = $model->where(array("code_name"=>"attendance_7", "code_value"=>1))->select();
        foreach ($items as $item) {
            $companyId = $item['company_id'];

            $smap['code_name'] = array('LIKE', 'attendance_%');
            $smap['company_id'] = $companyId;
            $setting = M('AppSetting')->where($smap)->getField('code_name,code_value');
            // 上班时间
            $WORK_TIME = timeToTimeLong($setting['attendance_1']);
            $this->shangban($companyId, $WORK_TIME, $NOW_TIME, $setting['attendance_8'], $setting['attendance_7_1']);
            // 下班时间
            $CLOSING_TIME = timeToTimeLong($setting['attendance_2']);
            $this->xiaban($companyId, $CLOSING_TIME, $NOW_TIME, $setting['attendance_8'], $setting['attendance_7_2']);
        }
        echo "\n上下班打卡提醒执行完毕\t\t" . date("m-d H:i:s");
    }

    // 月度报表推送
    public function attendanceReportNotification() {
        $NOW_DAY = intval(date('d'));

        $model = M('AppSetting');
        $items = $model->where(array("code_name"=>"attendance_9", "code_value"=>1))->select();
        foreach ($items as $item) {
            $companyId = $item['company_id'];

            $smap['code_name'] = array('LIKE', 'attendance_%');
            $smap['company_id'] = $companyId;
            $setting = M('AppSetting')->where($smap)->getField('code_name,code_value');
            //
            $attendance_10 = $setting['attendance_10'];
            $pushDay = trim(substr($attendance_10, 0, 2));
            $pushTime = trim(substr($attendance_10, 2));

            if ($pushDay == $NOW_DAY) {
                $this->pushAttendanceByMonth($companyId, $pushTime);
            }
        }
        echo "\n月度报表推送执行完毕 \t\t" . date("m-d H:i:s");
    }

    // 网页定时推送
    public function webpageNotification() {
        $begin = date("Y-m-d H:i:s", strtotime("-60 seconds"));
        $end = date("Y-m-d H:i:s", strtotime("+60 seconds"));
        $model = M("WebPage");
        $jpushRecordModel = M("JpushRecord");
        $items = $model->where(array(
            "status"  => 0,
            "push_time" => array("BETWEEN", "$begin,$end"),
            "push_status" => 0,
        ))->select();
        foreach ($items as $item) {
            $uri = "als://webpage/".$item["id"];
            $item["push_status"] = 1;
            $updateResult = $model->save($item);

            $dataMap = array(
                "user_id" => 0,
                "push_tag" => "webpage",
                "push_time" => strtotime($item["push_time"]),
                "push_content" => $item["title"],
                "uri" => $uri,
            );
            $jpushRecordModel->add($dataMap);
            if ($updateResult) {
                jpush($item["title"], $uri);
            }
        }
        echo "\n后台手动定时推送执行完毕\t\t" . date("m-d H:i:s");
    }

    // 设备掉线通知
    public function doorControllerOfflineNotification() {
        $MDoorController = M('DoorController');
        $now = time();
        $map = array(
            "product_type" => 2,
            "status" => 0,
            "company_id" => array("GT", 0),
            "last_connect_time" => array("between", array($now-60, $now-30)),
        );
        $controllerData = $MDoorController->where($map)->select();
        foreach ($controllerData as $controller) {
            $message = $this->onControllerOffline($controller);
            echo $message;
        }
        echo "\n后台设备掉线通知推送执行完毕\t\t" . date("m-d H:i:s");
    }

    private function shangban($companyId, $workTime, $nowTime, $attendance_8, $pushContent) {
        $notifiTime = $workTime - ($attendance_8 * 60);
        $notifiDate = timeLongToDate($notifiTime);
        if (!$pushContent) {
            $pushContent = "马上就要上班了，记得打卡哦！";
        }

        if (abs($notifiTime - $nowTime) <= 70) {// 符合推送时间
            $users = M('User')->where(array("company_id"=>$companyId,
                "jpush_register_id"=>array('exp','is not null'),
                "status" => 1
            ))->select();

            foreach ($users as $user) {
                $dataMap = array(
                    "user_id" => $user['id'],
                    "push_tag" => "attendance_7",
                    "push_time" => $notifiDate,
                );
                $pushCount = M('JpushRecord')->where($dataMap)->count();

                if (!$pushCount) {
                    $dataMap["push_content"] = $pushContent;
                    $addResult = M('JpushRecord')->add($dataMap);
                    if ($addResult) {
                        jpushToUser($user["jpush_register_id"], $dataMap["push_content"]);
                    }
                }
            }
        }
    }

    private function xiaban($companyId, $closTime, $nowTime, $attendance_8, $pushContent) {
        $notifiTime = $closTime - ($attendance_8 * 60);
        $notifiDate = timeLongToDate($notifiTime);
        if (!$pushContent) {
            $pushContent = "马上就要下班了，记得打卡哦！";
        }

        if (abs($notifiTime - $nowTime) <= 70) {// 符合推送时间
            $users = M('User')->where(array("company_id"=>$companyId,
                "jpush_register_id"=>array('exp','is not null'),
                "status" => 1
            ))->select();

            foreach ($users as $user) {
                $dataMap = array(
                    "user_id" => $user['id'],
                    "push_tag" => "attendance_7",
                    "push_time" => $notifiDate,
                );
                $pushCount = M('JpushRecord')->where($dataMap)->count();

                if (!$pushCount) {
                    $dataMap["push_content"] = $pushContent;
                    $addResult = M('JpushRecord')->add($dataMap);
                    if ($addResult) {
                        jpushToUser($user["jpush_register_id"], $dataMap["push_content"]);
                    }
                }
            }
        }
    }

    /**
     * @param $companyId 公司ID
     * @param $pushTime 推送时间
     */
    private function pushAttendanceByMonth($companyId, $pushTime) {
        $notifiTime = timeToTimeLong($pushTime);
        $notifiDate = timeLongToDate($notifiTime);
        $nowTime = dateToTimeLong(time());
        if (abs($notifiTime - $nowTime) <= 70) {// 符合推送时间
            $users = M('User')->where(array("company_id"=>$companyId,
                "jpush_register_id"=>array('neq',''),
                "status" => 1
            ))->select();

            foreach ($users as $user) {
                $dataMap = array(
                    "user_id" => $user['id'],
                    "push_tag" => "attendance_9",
                    "push_time" => $notifiDate,
                );
                $pushCount = M('JpushRecord')->where($dataMap)->count();

                if (!$pushCount) {
                    $dataMap["push_content"] = "上个月的考勤报表已统计完毕，请注意查收！";
                    $dataMap["uri"] = "als://attendance";
                    $addResult = M('JpushRecord')->add($dataMap);
                    if ($addResult) {
                        jpushToUser($user["jpush_register_id"], $dataMap["push_content"], "als://attendance");
                    }
                }
            }
        }
    }

    private function onControllerOffline($controllerData) {
        $controller_id = $controllerData['id'];
        $company_id = $controllerData['company_id'];
        $notifiDate = $controllerData['last_connect_time'];
        $warningTime = date("Y年m月d日 H时i分s秒", $notifiDate);
        $pushContent = $controllerData['name']." ".$warningTime."与服务器失去连接，请及时检查设备状态！";

        $MUser = M('User');
        $map = array();
        $map['status'] = 1;
        $map['company_id'] = $company_id;
        $map["jpush_register_id"] = array('neq','');
        $map['role_id'] = array("LT", 22);
        $userData = $MUser->join("auth_role_user on auth_role_user.user_id = user.id")->where($map)->select();
        $message = "$pushContent 通知以下用户:";
        foreach ($userData as $user) {
            $dataMap = array(
                "user_id" => $user['id'],
                "push_tag" => "door_offline",
                "push_time" => $notifiDate,
            );
            $pushCount = M('JpushRecord')->where($dataMap)->count();

            if (!$pushCount) {
                $dataMap["push_content"] = $pushContent;
                $addResult = M('JpushRecord')->add($dataMap);
                if ($addResult) {
                    $message .= $user["id"]."(".$user["nickname"]."),";
                    jpushToUser($user["jpush_register_id"], $dataMap["push_content"]);
                }
            }
        }
        return $message;
    }
}