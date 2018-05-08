<?php
namespace Udp\Controller;
use Think\Controller;

class JpushController extends Controller\RestController {

    public function test() {
        $begin = date("Y-m-d H:i:s", strtotime("+60 seconds"));
        echo $begin;
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
            $this->shangban($companyId, $WORK_TIME, $NOW_TIME, $setting['attendance_7']);
            // 下班时间
            $CLOSING_TIME = timeToTimeLong($setting['attendance_2']);
            $this->xiaban($companyId, $CLOSING_TIME, $NOW_TIME, $setting['attendance_7']);
        }
        echo "上下班打卡提醒执行完毕";
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
        echo "月度报表推送执行完毕";
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
            $item["push_status"] = 1;
            $updateResult = $model->save($item);

            $dataMap = array(
                "user_id" => 0,
                "push_tag" => "webpage",
                "push_time" => strtotime($item["push_time"]),
                "push_content" => $item["title"],
            );
            $jpushRecordModel->add($dataMap);
            if ($updateResult) {
                jpush($item["title"], "als://webpage/".$item["id"]);
            }
        }
        echo "后台手动定时推送执行完毕";
    }

    private function shangban($companyId, $workTime, $nowTime, $attendance_7) {
        $notifiTime = $workTime - ($attendance_7 * 60);
        $notifiDate = timeLongToDate($notifiTime);

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
                    $dataMap["push_content"] = "马上就要上班了，记得打卡哦！";
                    $addResult = M('JpushRecord')->add($dataMap);
                    if ($addResult) {
                        jpushToUser($user["jpush_register_id"], $dataMap["push_content"]);
                    }
                }
            }
        }
    }

    private function xiaban($companyId, $closTime, $nowTime, $attendance_7) {
        $notifiTime = $closTime - ($attendance_7 * 60);
        $notifiDate = timeLongToDate($notifiTime);

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
                    $dataMap["push_content"] = "马上就要下班了，记得打卡哦！";
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
                    $addResult = M('JpushRecord')->add($dataMap);
                    if ($addResult) {
                        jpushToUser($user["jpush_register_id"], $dataMap["push_content"], "als://attendance");
                    }
                }
            }
        }
    }
}