<?php
namespace Home\Controller;

class OpenRecordController extends CommonController {

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"出入记录");
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
            $this->_list($model, $map);
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
}