<?php
namespace Api\Controller;


class PushRecordController extends CommonRestController {

    public function lists() {
        $userId = $_REQUEST['user_id'];
        $map = array(
            'user_id' => array('in', "0,$userId"),
            'push_tag' => array('in', 'attendance_9,door_offline,door_warngin,webpage'),
        );
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $this->setMap($map,$search);

        $model = M("JpushRecord");
        if (!empty($model)) {
            $this->_list($model, $map, 'push_time', false);
        }
        $voList = $this->voList;
        foreach ($voList as $i=>$vo) {
            switch ($vo['push_tag']) {
                case 'attendance_9':
                    $voList[$i]['title'] = '月度考勤';
                    break;
                case 'door_offline':
                    $voList[$i]['title'] = '设备掉线';
                    break;
                case 'door_warngin':
                    $voList[$i]['title'] = '非法闯入';
                    break;
                case 'webpage':
                    $voList[$i]['title'] = '文章';
                    break;
            }
            unset($voList[$i]['user_id']);
            unset($voList[$i]['push_tag']);
        }
        $result = $this->createResult(200, "", $voList);

        $this->response($result,'json');
    }
}