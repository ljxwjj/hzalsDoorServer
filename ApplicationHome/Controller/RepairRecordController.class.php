<?php
namespace Home\Controller;

use Lib\ORG\Util\Page;

class RepairRecordController extends CommonController {

    public function _initialize() {
        parent::_initialize();
        $this->assign('pagetitle',"报修记录");
    }

    protected function _filter(&$map) {
        if (session(C('ADMIN_AUTH_KEY'))) {
            // 管理员不做任何限制

        } else {
            // 无权限
            $this->error('非法操作');
        }
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
        $model = M('RepairRecord');
        if (!empty($model)) {
            $this->_list($model, $map, 'id');
        }

        //保持分页记录
        $nowpage = (int)I('p')?(int)I('p'):(int)I('search_p');
        if($nowpage){
            $this->assign('nowpage', $nowpage);
        }

        $voList = $this->voList;
        foreach ($voList as $i=>$vo) {
            $image = $vo["image"];
            if ($image) {
                $voList[$i]["image"] = explode(";", $image);
            }
        }
        $this->assign('list', $voList);
        $this->display();
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
        $count = $model->join("join user ON user.id = repair_record.user_id")->where($map)->count('repair_record.id');
        if ($count > 0) {
            import ( '@.Lib.ORG.Util.Page' );
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = C('LIST_ROWS');
            }
            $p = new Page($count, $listRows);
            //分页查询数据
            $this->voList = $model
                ->field(array('repair_record.id'=>'id','user_id',
                    'company_name', 'phone', 'address', 'describe_text', 'image', 'repair_record.status'=>'status',
                    'user.nickname'=>'user_nickname', 'repair_record.create_time'=>'create_time'))
                ->join("left join user ON user.id = repair_record.user_id")
                ->where($map)->order("`" . $order . "` " . $sort)
                ->limit($p->firstRow . ',' . $p->listRows)->select();

            //分页显示
            $page = $p->show();

            //列表排序显示
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
            $sort = $sort == 'desc' ? 1 : 0; //排序方式
            //模板赋值显示
            $this->assign('sort', $sort);
            $this->assign('order', $order);
            $this->assign('sortImg', $sortImg);
            $this->assign('sortType', $sortAlt);
            $this->assign("page", $page);
            $this->assign('map',$p->parameter);

        }
        cookie('_currentUrl_', __SELF__);
        return;
    }

    /**
     * 设置查询条件*
     *
     * @param array $map  查询条件
     * @param array $search 搜索数组
     */
    protected function setMap(&$map,&$search){

        foreach ($_REQUEST as $key => $val) {
            if($val == "") {
                continue;
            }
            if (ereg("^search_", $key)) {
                $field = str_replace('search_','',$key);
                $search[$key] = $val;

                switch($field){
                    case 'company_name':
                        $map['repair_record.company_name'] = array('like',"%$val%");
                        break;
                    case 'user_nickname':
                        $map['user.nickname'] = array('like',"%$val%");
                        break;
                    case 'time_start':
                    case 'time_end':
                        break;
                    default:
                        $map[$field] = $val;
                        break;
                }

            }
        }
    }

    protected function addSearchCondition(&$map,$child=0) {
        $searchPrefix = $child ? 'search_child_' : 'search_'.'' ;
        if($_REQUEST[$searchPrefix.'time_start'] != ''){
            $map["repair_record.create_time"][] = array('egt',strtotime(I($searchPrefix.'time_start')));
        }
        if($_REQUEST[$searchPrefix.'time_end'] != ''){
            $map["repair_record.create_time"][] = array('lt',strtotime(I($searchPrefix.'time_end'))+86400);
        }
    }

    public function handleRepair() {
        $id = I('id');
        $model = M('RepairRecord');
        $model->where(array('id'=>$id))->setField("status", 1);
        $this->redirect("RepairRecord/index");
    }
}