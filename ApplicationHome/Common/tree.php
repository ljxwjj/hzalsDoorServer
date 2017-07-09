<?php
function getParentsArray($table, $pid_name, $id_name, $id) {
    $arrParents = array(0);
    $ret = $id;

    while($ret != "0" && !isBlank($ret)) {
        $arrParents[] = $ret;
        $ret = getParentsArraySub($table, $pid_name, $id_name, $ret);
    }

    $arrParents = array_reverse($arrParents);

    return $arrParents;
}

function getParentsArraySub($table, $pid_name, $id_name, $child) {
    if(isBlank($child)) {
        return false;
    }
	$map[$id_name] = array('eq',$child);
	$parent = $table->where($map)->field($pid_name)->find();
    return $parent[$pid_name];
}