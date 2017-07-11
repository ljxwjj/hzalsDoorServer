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
}