<?php
namespace Api\Controller;

class OpenRecordController extends CommonRestController {

    public function _filter(&$map) {
        if (session(C('ADMIN_AUTH_KEY'))) {

        } else {
            $map['company_id'] = session("user")["company_id"];
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
                    $map['open_time'][] = array('LT', $val + 24 * 60 * 60);
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

        $model = M("OpenRecordView");
        if ($model) {
            $this->_list($model, $map, 'id');
        }
        $result = $this->createResult(200, "", $this->voList);

        $this->response($result,'json');
    }
}