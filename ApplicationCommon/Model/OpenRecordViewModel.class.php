<?php
namespace Common\Model;


class OpenRecordViewModel extends CommonModel {

    // 实际数据表名（包含表前缀）
    protected $trueTableName    =   'open_record';

    public $_link = array(


    );

    public $_validate = array(
    );

    public $patchValidate = true;

    public $_auto		=	array(
//        array('status','1',MODEL_INSERT),
    );

    // 表达式过滤回调方法
    protected function _options_filter(&$options) {
        $options['table'] = 'open_record';
        //var_dump($options);exit;
    }

    /**
     * 查询数据集
     * 弃用视图，由于视图中用了太多关联查询，导致查询速度巨慢，改用拼装sql的方式要快很多
     * @access public
     * @param array $options 表达式参数
     * @return mixed
     */
    public function select($options=array()) {
        $sql = parent::select(false);
        $sql = "select ord.`id` AS `id`,ord.`controller_id` AS `controller_id`,ord.`door_id` AS `door_id`,`hzals_door`.`door`.`name` AS `door_name`,ord.`open_time` AS `open_time`,ord.`feedback_time` AS `feedback_time`,ord.`user_id` AS `user_id`,ord.`way` AS `way`,ord.`mark` AS `mark`,`hzals_door`.`door_controller`.`name` AS `controller_name`,`hzals_door`.`user`.`nickname` AS `user_nickname`,`hzals_door`.`user`.`company_id` AS `company_id`,`hzals_door`.`company`.`name` AS `company_name` from (((($sql ord left join `hzals_door`.`door_controller` on((`hzals_door`.`door_controller`.`id` = ord.`controller_id`))) left join `hzals_door`.`door` on(((`hzals_door`.`door`.`controller_id` = ord.`controller_id`) and (`hzals_door`.`door`.`door_index` = ord.`door_id`)))) left join `hzals_door`.`user` on((`hzals_door`.`user`.`id` = ord.`user_id`))) left join `hzals_door`.`company` on((`hzals_door`.`company`.`id` = `hzals_door`.`user`.`company_id`)))";
        return $this->query($sql);
    }
}