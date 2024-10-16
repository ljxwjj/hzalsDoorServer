<?php
namespace Udp\Controller;
use Think\Controller;

/**
 * 每天扫描即将到期的客户，并发送短信通知到客户
 * 每月15日统计下月即将到期的客户名单，并短信通知系统管理员
 */
class PaymentController extends Controller\RestController {

    public function paymentTaskByDay() {
        $this->checkNextMonth();
        $this->checkCompanyExpirationDate();
    }

    /**
     * 统计下月到期客户名单
     */
    private function checkNextMonth() {
        $mobile = "18058804397";
        $useTo = "receivable_list";
        $month = date('d');
        if ($month !== '16') return;
        
        $model = M('Company');
        $eqNextMonth = "DATE_FORMAT(expiration_date, '%Y-%m') = DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 MONTH), '%Y-%m')";
        $companyList = $model->where(array('status'=>0, '_string'=>$eqNextMonth))->getField('name', true);
        if (empty($companyList)) return;

        $message = "下个月到期的客户名单：". join("、", $companyList);
        $MSmsNotifaction = M('SmsNotifaction');
        $map = array("mobile"=>$mobile, "use_to"=>$useTo);
        $sms = $MSmsNotifaction->where($map)->order('send_time desc')->find();
        if ($sms && ($sms["send_time"] + 60*60*3) > time()) {
            echo "下月到期名单 send to $mobile 信息发送太频繁！";
        }

        try {
            $sendResult = doSendSms($mobile, $message, $useTo);
            if ($sendResult->Code !== 'OK') {
                echo '发送失败:'.$sendResult->Message;
            }
            $data = array(
                'mobile'=> $mobile,
                'send_time' => time(),
                'message' => $message,
                'use_to' => $useTo,
                'sms_request_id' => $sendResult->RequestId,
                'sms_biz_id' => $sendResult->BizId,
            );
            $smsId = $MSmsNotifaction->data($data)->add();
        } catch(\Exception $e){
            
        }

        if ($smsId) {
            echo '发送成功';
        } else {
            echo '发送失败';
        }
    }
    
    /**
     * 扫描即将到期的客户，并发送短信通知到客户
     */
    private function checkCompanyExpirationDate() {
        $dateNow = date('Y-m-d');
        $date1 = date('Y-m-d', strtotime("$dateNow -30 day"));
        $model = M('Company');
        $companyList = $model->where(array('status'=>0, 'expiration_date'=>$data1))->select();
        foreach ($companyList as $company) {
            $message = "您的门禁服务将于30天后到期，请及时续费，以免影响正常使用。";
            $this->pushToCompany($company, $message);
        }
        $companyList = $model->where(array('status'=>0, 'expiration_date'=>$dateNow))->select();
        foreach ($companyList as $company) {
            $message = "您的门禁服务于今日期，请及时续费，以免影响正常使用。";
            $this->pushToCompany($company, $message);
        }
    }

    /**
     * @param $companyId 公司ID
     * @param $expirationDate 到期时间
     */
    private function pushToCompany($company, $message) {
        $users = M('User')->where(array("company_id"=>$company['id'],
            "is_admin"=>1,
            "status" => 1
        ))->select();

        foreach ($users as $user) {
            $mobile = $user['mobile'];
    
            $MSmsNotifaction = M('SmsNotifaction');
            $map = array();
            $map["mobile"] = $mobile;
            $map["use_to"] = 'payment';
            $sms = $MSmsNotifaction->where($map)->order('send_time desc')->find();
            if ($sms && ($sms["send_time"] + 60*60*6) > time()) {
                echo "{$company['name']} send to $mobile 信息发送太频繁！";
            }

            try {
                $sendResult = doSendSms($mobile, $message, "payment");
                if ($sendResult->Code !== 'OK') {
                    echo '发送失败:'.$sendResult->Message;
                }
                $data = array(
                    'mobile'=> $mobile,
                    'send_time' => time(),
                    'message' => $message,
                    'use_to' => "payment",
                    'sms_request_id' => $sendResult->RequestId,
                    'sms_biz_id' => $sendResult->BizId,
                );
                $smsId = $MSmsNotifaction->data($data)->add();
            } catch(\Exception $e){

            }

            if ($smsId) {
                echo '发送成功';
            } else {
                echo '发送失败';
            }
        }
        
    }
}