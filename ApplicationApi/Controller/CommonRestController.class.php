<?php
namespace Api\Controller;
use Lib\ORG\Util\RBAC;
use Think\Controller\RestController;


class CommonRestController extends RestController {
    /**
     * 权限判断**
     *
     */
    public function _initialize() {
        import('Lib.ORG.Util.RBAC');

        //检查参数签名
        if(!RBAC::checkParamsSign()){
            $this->response($this->createResult(1, "签名错误"),'json');
            exit;
        }
        // 用户权限检查
        if (C('USER_AUTH_ON') && RBAC::checkAccess()) {
            if (!RBAC::AccessToken()) {
                $this->response($this->createResult(2, "登录授权失败"),'json');
                exit;
            }
            // 没有权限 抛出错误
            if (!RBAC::AccessDecision()) {
                $this->response($this->createResult(3, "当前用户未授权"),'json');
                exit;
            }
        }
    }

    /**
     * 数据列表*
     *
     * @param string $name 模型名称
     */
    protected function lists($name="") {
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $this->setMap($map,$search);

        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $result = $this->createResult(200, "", $this->voList);

        $this->response($result,'json');
    }

    /**
     * 根据表单生成查询条件
     * 进行列表过滤
     * @param Model $model 数据对象
     * @param HashMap $map 过滤条件
     * @param string $sortBy 排序
     * @param boolean $asc 是否正序
     * @return void
     */
    protected function _list($model, $map, $sortBy = '', $asc = false) {
        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = $asc ? 'asc' : 'desc';
        }
        //取得满足条件的记录数
        $count = $model->where($map)->count($model->getPk());

        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST ['_listRows'])) {
                $listRows = $_REQUEST ['_listRows'];
            } else {
                $listRows = C('LIST_ROWS');
            }
            $nowPage = I('page')?I('page'):1;
            $firstRow = $listRows * ($nowPage - 1);
            //分页查询数据
            $this->voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($firstRow . ',' . $listRows)->select();
        } else {
            $this->voList = array();
        }
        return;
    }

    protected function detail($name='') {
        $name = $name ? $name : $this->getActionName();
        $model = M($name);
        $pk = $model->getPk();
        $id = I($pk);

        if (isset($id)) {
            $list = $model->find($id);
            if ($list !== false) {
                $result = $this->createResult(200, "操作成功", $list);
            } else {
                $result = $this->createResult(0, "操作失败");
            }
        } else {
            $result = $this->createResult(0, "非法操作");
        }

        $this->response($result, "json");
    }

    /**
     * 新建*
     *
     */
    protected function add($name='') {
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $id = (int)I($model->getPk());
        $data = $model->create($_REQUEST);
        if(!$data){
            $error = $model->getError();
            $message = implode(",", $error);
            $result = $this->createResult(0, $message);
        }else{
            $result = $model->add($data);
            if($result){
                $result = $this->createResult(200, "操作成功", $result);
            }else{
                $result = $this->createResult(0, "操作失败");
            }
        }
        $this->response($result, "json");
    }


    /**
     * 保存*
     *
     * @param string $name 数据对象
     * @param string $tpl  模板名称
     */
    protected function edit($name='') {
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $id = (int)I($model->getPk());
        $data = $model->create($_REQUEST);
        if(!$data){
            $error = $model->getError();
            $message = implode(",", $error);
            $result = $this->createResult(0, $message);

        }else{
            $result = $model->save($data);
            if($result){
                $result = $this->createResult(200, "操作成功");
            }else{
                $result = $this->createResult(0, "操作失败");
            }
        }
        $this->response($result, "json");
    }

    /**
     * 默认删除操作
     * @param string $name 数据对象
     * @return string
     */
    protected function del($name="") {
        //虚拟删除指定记录
        $name = $name ? $name : $this->getActionName();
        $model = M($name);
        $pk = $model->getPk();
        $id = I($pk);

        if (isset($id)) {
            $condition = array($pk => array('in', explode(',', $id)));
            $list = $model->where($condition)->setField('status', -1);
            if ($list !== false) {
                $result = $this->createResult(200, "操作成功");
            } else {
                $result = $this->createResult(0, "操作失败");
            }
        } else {
            $result = $this->createResult(0, "非法操作");
        }

        $this->response($result, "json");
    }

    /**
     * 物理删除*
     *
     * @param string $name 数据对象
     */
    protected function foreverdel($name='') {
        //物理删除指定记录
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        if (isset($id)) {
            $condition = array($pk => array('in', explode(',', $id)));
            if (false !== $model->where($condition)->delete()) {
                $result = $this->createResult(200, "操作成功");
            } else {
                $result = $this->createResult(0, "操作失败");
            }
        } else {
            $result = $this->createResult(0, "非法操作");
        }

        $this->response($result, "json");
    }



    /**
     * 默认禁用操作
     *
     * @param string 模型对象
     */
    protected function forbid($name='') {
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => array('in', $id));
        $list = $model->forbid($condition);
        if ($list !== false) {
            $result = $this->createResult(200, "操作成功");
        } else {
            $result = $this->createResult(0, "操作失败");
        }
        $this->response($result, "json");
    }

    /**
     * 默认恢复操作
     *
     * @param string 模型对象
     */
    protected function resume($name='') {
        //恢复指定记录
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => array('in', $id));
        if (false !== $model->resume($condition)) {
            $result = $this->createResult(200, "操作成功");
        } else {
            $result = $this->createResult(0, "操作失败");
        }
        $this->response($result, "json");
    }

    /**
     * 默认还原操作
     *
     * @param string 模型对象
     */
    protected function recycle($name='') {
        $name = $name ? $name : $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = I($pk);
        $condition = array($pk => array('in', $id));
        if (false !== $model->recycle($condition)) {
            $result = $this->createResult(200, "操作成功");
        } else {
            $result = $this->createResult(0, "操作失败");
        }
        $this->response($result, "json");
    }

    /**
     * 默认依据url传参，生成搜索条件*
     *
     * @param array $map 查询数组
     * @param array $search 搜索数组
     */
    protected function setMap(&$map,&$search){
        foreach ($_REQUEST as $key => $val) {
            if($val == "") {
                continue;
            }
            if (ereg("^search_", $key)) {
                $field = str_replace('search_','',$key);
                $map[$field] = array('eq',$val);
                $search[$key] = $val;
            }
            //break;
        }
    }

    protected function getActionName() {
        return CONTROLLER_NAME;
    }

    protected function createResult($code, $message, $data = array()) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        $result['data'] = (object)$data;
        return $result;
    }
}