<?php
function getChildrenArray($tableObj, $pid_name, $id_name, $id) {
    $arrChildren = array();
    $arrRet = array($id);

    while(count($arrRet) > 0) {
        $arrChildren = array_merge($arrChildren, $arrRet);
        $arrRet = getChildrenArraySub($tableObj, $pid_name, $id_name, $arrRet);
    }

    return $arrChildren;
}

function getChildrenArraySub($tableObj, $pid_name, $id_name, $arrPID) {

	$map[$pid_name]  = array('in',$arrPID);
    $ret = $tableObj->where($map)->field($id_name)->select();

    $arrChildren = array();
    foreach ($ret as $val) {
        $arrChildren[] = $val[$id_name];
    }

    return $arrChildren;
}

function deleteRankRecord($tableObj, $colname, $id, $andwhere = array(), $delete = false) {
    $tableObj->startTrans();
    $map[$colname] = array('eq',$id);
    if(count($andwhere) > 0) {
    	$map = array_merge($map, $andwhere);
    }
    $rank = $tableObj->where($map)->getField('sort');

    //虚拟删除
    if(!$delete) {
		$data = array('sort'=>'0','status'=>'-1');
		$tableObj-> where($map)->setField($data);
    } else {
    	$tableObj->where($map)->delete();
    }

    unset($map);
    $map['sort'] = array('gt',$rank);
    if(count($andwhere) > 0) {
    	$map = array_merge($map, $andwhere);
    }
    $tableObj->where($map)->setDec('sort',1);
    $tableObj->commit();
}