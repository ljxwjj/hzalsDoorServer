<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/26
 * Time: 13:58
 */

namespace Home\Controller;


class WebPageController extends CommonController {

    public function _initialize() {
        $this->assign('pagetitle',"页面管理");
        parent::_initialize();
    }

    public function index() {
        parent::index();
        $voList = $this->voList;
        $this->assign('list', $voList);
        $this->display();
    }

    public function save() {
        $model = M("WebPage");
        $id = I("id");
        $data = $model->create($_REQUEST);
        if(!$data){
            $error = $model->getError();
            $this->assign('vo',$_REQUEST);
            $this->assign('error',$error);
            $this->display('AppSetting/webPageList');
        }else{
            if (I("push_now")) {
                $data["push_time"] = null;
            } else {
                $data["push_status"] = 0;
            }
            if($id){
                $data["update_time"] = time();
                $result = $model->save($data);
            }else{
                $data["create_time"] = time();
                $result = $model->add($data);
            }
            if ($result && I("push_now")) {
                jpush($data["title"], "als://webpage/$result");
            }
            if($result){
                $this->success('数据已保存！',$this->getReturnUrl());
            }else{
                $this->error('数据未保存！',$this->getReturnUrl());
            }
        }
    }

    public function edit($name="") {
        $this->keepSearch();
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $id = (int)I($model->getPk());
        if (empty($id)) {
            $this->error('请选择要编辑的数据！');
            exit;
        }
        $vo = $model->getById($id);
        if($vo){
            if (empty($vo["push_time"])) {
                $vo["push_now"] = 1;
            }
            $this->assign('vo', $vo);
            $this->display();
        }else{
            $this->error('没有找到要编辑的数据！');
        }
    }

    public function view() {
        $model = M('WebPage');
        $id = I('id');
        $vo = $model->where("id=$id")->find();
        if ($vo) {
            $this->assign('vo', $vo);
            $this->assign('pagetitle',$vo["title"]);
            layout('Layout/webPageLayout');
            $this->display();
        } else {
            $this->error("页面未找到");
        }
    }

    /**
     * 设置查询条件*
     *
     * @param array $map  查询条件
     * @param array $search 搜索数组
     */
    protected function setMap(&$map, &$search){

        foreach ($_REQUEST as $key => $val) {
            if($val == "") {
                continue;
            }
            if (ereg("^search_", $key)) {
                $field = str_replace('search_','',$key);
                $search[$key] = $val;

                switch($field){
                    case 'title':
                        $map['title'] = array('like',"%$val%");
                        break;
                    case 'time_start':
                        $map["create_time"][] = array('egt',strtotime($val));
                        break;
                    case 'time_end':
                        $map["create_time"][] = array('lt',strtotime($val)+86400);
                        break;
                    default:
                        $map[$field] = $val;
                        break;
                }

            }
        }
    }

}