<?php
/**
 * 删除二维数组重复值
 */
function remove_dup($matriz) 
{
   $aux_ini=array();
   $entrega=array();
   for($n=0;$n<count($matriz);$n++) 
   {
     $aux_ini[]=serialize($matriz[$n]);
   }
   $mat=array_unique($aux_ini);
   for($n=0;$n<count($matriz);$n++) 
   {
     $entrega[]=unserialize($mat[$n]);
   }
   foreach ($entrega as $key => $row)
   {
     if (!is_array($row)) { unset($entrega[$key]); }
   }
   return $entrega;
}

/**
 * 从数组中删除空白的元素（包括只有空白字符的元素）
 *
 * @param array $arr
 * @param boolean $trim
 */
function array_remove_empty(& $arr, $trim = true)
{
    foreach ($arr as $key => $value) {
        if (is_array($value)) {
            array_remove_empty($arr[$key]);
        } else {
            $value = trim($value);
            if ($value == '') {
                unset($arr[$key]);
            } elseif ($trim) {
                $arr[$key] = $value;
            }
        }
    }
}

/**
 * 从一个二维数组中返回指定键的所有值
 *
 * @param array $arr
 * @param string $col
 *
 * @return array
 */
function array_col_values(& $arr, $col)
{
    $ret = array();
    foreach ($arr as $row) {
        if (isset($row[$col])) { $ret[] = $row[$col]; }
    }
    return $ret;
}

/**
 * 将一个二维数组转换为 hashmap
 *
 * 如果省略 $valueField 参数，则转换结果每一项为包含该项所有数据的数组。
 *
 * @param array $arr
 * @param string $keyField
 * @param string $valueField
 *
 * @return array
 */
function array_to_hashmap(& $arr, $keyField, $valueField = null)
{
    $ret = array();
    if ($valueField) {
        foreach ($arr as $row) {
            $ret[$row[$keyField]] = $row[$valueField];
        }
    } else {
        foreach ($arr as $row) {
            $ret[$row[$keyField]] = $row;
        }
    }
    return $ret;
}

/**
 * 将一个二维数组按照指定字段的值分组
 *
 * @param array $arr
 * @param string $keyField
 *
 * @return array
 */
function array_group_by(& $arr, $keyField)
{
    $ret = array();
    if(is_array($arr)){
	    foreach ($arr as $row) {
	        $key = $row[$keyField];
	        $ret[$key][] = $row;
	    }
    }
    return $ret;
}

/**
 * 取两个二维数组差集
 */
function arrays_diff($arr1, $arr2){	
	$resultArray = array();
	if(!is_array($arr1) || count($arr1)<1){
		return $resultArray;
	}
	if(!is_array($arr2) || count($arr2)<1){
		return $arr1;
	}
	foreach($arr1 as $k=>$v){
		$tempArr1[$k] = implode('',$v);
	}
	foreach($arr2 as $k=>$v){
		$tempArr2[$k] = implode('',$v);
	}
	$tempArray = array_diff($tempArr1, $tempArr2);

	foreach($tempArray as $k=>$v){
		$resultArray[] = $arr1[$k];
	}
	
	return $resultArray;
}

/**
 * 将一个平面的二维数组按照指定的字段转换为树状结构
 *
 * 当 $returnReferences 参数为 true 时，返回结果的 tree 字段为树，refs 字段则为节点引用。
 * 利用返回的节点引用，可以很方便的获取包含以任意节点为根的子树。
 *
 * @param array $arr 原始数据
 * @param string $fid 节点ID字段名
 * @param string $fparent 节点父ID字段名
 * @param string $fchildrens 保存子节点的字段名
 * @param boolean $returnReferences 是否在返回结果中包含节点引用
 *
 * return array
 */
function array_to_tree($arr, $fid, $fparent = 'parent_id',
    $fchildrens = 'childrens', $returnReferences = true)
{
    $pkvRefs = array();
    foreach ($arr as $offset => $row) {
        $pkvRefs[$row[$fid]] =& $arr[$offset];
    }

    $tree = array();
    foreach ($arr as $offset => $row) {
        $parentId = $row[$fparent];
        if ($parentId) {
            if (!isset($pkvRefs[$parentId])) { continue; }
            $parent =& $pkvRefs[$parentId];
            $parent[$fchildrens][] =& $arr[$offset];
        } else {
            $tree[] =& $arr[$offset];
        }
    }
    if ($returnReferences) {
        return array('tree' => $tree, 'refs' => $pkvRefs);
    } else {
        return $tree;
    }
}

/**
* 将一个平面的二维数组按照指定的字段转换为具有树状结构关系的并且按照树结构循环排列的新的二维数组
*
* @param array  $cate_formerly 原始数组
* @param &array $cate_array    新数组 此数组从外面传送进来
* @param string $idKey         节点id字段名
* @param string $parentKey     父id节点 键名（字段名）
* @param int    $cate_parent   父id节点 值（子数开始的地方 0为从根开始）
* @param int    $cate_lv       节点级别（子树亦从0计算，即提出的树的节点级别，而非原始数组中原始的节点级别）
*
* @return array
*/
function array_to_tree2($cate_formerly,&$cate_array,$idKey='cate_id',$parentKey='cate_fatherId',$cate_parent=0,$cate_lv=0)
{
	static  $i=0;  //从0开始
	if (is_array($cate_formerly))
	{
		foreach ($cate_formerly as $value) 
		{
			if ($value[$parentKey]==$cate_parent)
			{
				$value['cate_lv']=$cate_lv;
				if($value['cate_lv']!==0)
				{
					for($j=1;$j<=$cate_lv;$j++)
					{
						$blank .= '&nbsp;&nbsp;&nbsp;';
					}
					$value['cate_namepre'] = $blank;
				}
				else
				{
					$value['cate_namepre'] = '' ;
				}
				unset($blank);
				$cate_array[$i]=$value;
				$i++;
				$cate_lv++;
				array_to_tree2($cate_formerly,$cate_array,$idKey,$parentKey,$value[$idKey],$cate_lv--);
			}
		}
	}
	return $cate_array;
}

/**
 * 将树转换为平面的数组
 *
 * @param array $node
 * @param string $fchildrens
 *
 * @return array
 */
function tree_to_array(& $node, $fchildrens = 'childrens')
{
    $ret = array();
    if (isset($node[$fchildrens]) && is_array($node[$fchildrens])) {
        foreach ($node[$fchildrens] as $child) {
            $ret = array_merge($ret, tree_to_array($child, $fchildrens));
        }
        unset($node[$fchildrens]);
        $ret[] = $node;
    } else {
        $ret[] = $node;
    }
    return $ret;
}

/**
 * 根据指定的键值对二维数组排序
 *
 * @param array $array 要排序的数组
 * @param string $keyname 键值名称
 * @param int $sortDirection 排序方向
 *
 * @return array
 */
function array_column_sort($array, $keyname, $sortDirection = SORT_ASC)
{
	foreach ($array as $key => $row) 
	{
	    $volume[$key]  = $row[$keyname];
	}
	array_multisort($volume,$sortDirection,$array);
	return $array;
}

/**
 * 将数组保存进一个文件
 */
function array_save($array, $file, $arrayname=false)
{	
	$data = var_export($array,TRUE);
	if(!$arrayname)
	{
		$data = "<?php\n return " .$data.";\n?>";
	}
	else
	{
		$data = "<?php\n " .$arrayname . "=\n" .$data . ";\n?>";
	}
	if(PHP5)
	{
		return file_put_contents($file,$data);
	}
	if(PHP4)
	{
		$fp = @fopen($file,'w');
		if (!$fp) 
		{
			return false;
		} 
		else 
		{
			fwrite($fp,$data);
			fclose($fp);
			return true;
		}
	}
}