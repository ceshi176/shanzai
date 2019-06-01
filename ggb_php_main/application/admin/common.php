<?php
use think\Request;
use app\admin\model\NormalModel;


function getMallNameById($id)
{
    foreach(\think\Config::get('mall') as $key=>$val)
    {
        if ($id == $key){
            return $val['title'];
        }
    }
}

function getNormalTitLeById($id)
{
    $normal = new NormalModel();
    return $normal->where('id', $id)->value('title');
}

/**
 * 生成操作按钮
 * @param array $operate 操作按钮数组
 */
function showOperate($operate = [])
{
    if(empty($operate)){
        return '';
    }

    $option = '';
    foreach($operate as $key=>$vo){
        if(authCheck($vo['auth'])){
            $option .= ' <a href="' . $vo['href'] . '"><button type="button" class="btn btn-' . $vo['btnStyle'] . ' btn-sm">'.
                '<i class="' . $vo['icon'] . '"></i> ' . $key . '</button></a>';
        }
    }

    return $option;
}

/**
 * 将字符解析成数组
 * @param $str
 */
function parseParams($str)
{
    $arrParams = [];
    parse_str(html_entity_decode(urldecode($str)), $arrParams);
    return $arrParams;
}

/**
 * 子孙树 用于菜单整理
 * @param $param
 * @param int $pid
 */
function subTree($param, $pid = 0)
{
    static $res = [];
    foreach($param as $key=>$vo){

        if( $pid == $vo['pid'] ){
            $res[] = $vo;
            subTree($param, $vo['id']);
        }
    }

    return $res;
}

/**
 * 整理菜单住方法
 * @param $param
 * @return array
 */
function prepareMenu($param)
{
    $param = objToArray($param);
    $parent = []; //父类
    $child = [];  //子类

    foreach($param as $key=>$vo){

        if(0 == $vo['type_id']){
            $vo['href'] = '#';
            $parent[] = $vo;
        }else{
            $vo['href'] = url($vo['control_name'] .'/'. $vo['action_name']); //跳转地址
            $child[] = $vo;
        }
    }

    foreach($parent as $key=>$vo){
        foreach($child as $k=>$v){

            if($v['type_id'] == $vo['id']){
                $parent[$key]['child'][] = $v;
            }
        }
    }
    unset($child);

    return $parent;
}

/**
 * 解析备份sql文件
 * @param $file
 */
function analysisSql($file)
{
    // sql文件包含的sql语句数组
    $sqls = array ();
    $f = fopen ( $file, "rb" );
    // 创建表缓冲变量
    $create = '';
    while ( ! feof ( $f ) ) {
        // 读取每一行sql
        $line = fgets ( $f );
        // 如果包含空白行，则跳过
        if (trim ( $line ) == '') {
            continue;
        }
        // 如果结尾包含';'(即为一个完整的sql语句，这里是插入语句)，并且不包含'ENGINE='(即创建表的最后一句)，
        if (! preg_match ( '/;/', $line, $match ) || preg_match ( '/ENGINE=/', $line, $match )) {
            // 将本次sql语句与创建表sql连接存起来
            $create .= $line;
            // 如果包含了创建表的最后一句
            if (preg_match ( '/ENGINE=/', $create, $match )) {
                // 则将其合并到sql数组
                $sqls [] = $create;
                // 清空当前，准备下一个表的创建
                $create = '';
            }
            // 跳过本次
            continue;
        }

        $sqls [] = $line;
    }
    fclose ( $f );

    return $sqls;
}



/**
 * 对象转换成数组
 * @param $obj
 */
function objToArray($obj)
{
    return json_decode(json_encode($obj), true);
}

/**
 * 权限检测
 * @param $rule
 */
function authCheck($rule)
{
    $control = explode('/', $rule)['0'];
    if(in_array($control, ['login', 'index'])){
        return true;
    }

    if(in_array($rule, cache(session('role_id')))){
        return true;
    }

    return false;
}
/**
 * $list  为查询 出来的二维数组
 **/
function getree($list,$pid=0,$itemprefix = '')
{
    static $icon = array('│', '├', '└');
    static $nbsp = "&nbsp;";
    static $arr = array();
    $number = 1;
    foreach ($list as $row) {
        if ($row['pid'] == $pid) {
            $brotherCount = 0;
            //判断当前有多少个兄弟分类
            foreach ($list as $r) {
                if ($row['pid'] == $r['pid']) {
                    $brotherCount++;
                }
            }
            if ($brotherCount > 0) {
                $j = $k = '';
                if ($number == $brotherCount) {
                    $j .= $icon[2];
                    $k = $itemprefix ? $nbsp : '';
                } else {
                    $j .= $icon[1];
                    $k = $itemprefix ? $icon[0] : '';
                }
                $spacer = $itemprefix ? $itemprefix . $j : '';
                $row['name'] = $spacer . $row['name'];
                $arr[] = $row;
                $number++;
                getree($list, $row['id'], $itemprefix . $k . $nbsp);
            }
        }
    }
    return $arr;
}
/**
 * 整理出tree数据 ---  layui tree
 * @param $pInfo
 * @param $spread
 */
function getTree($pInfo, $spread = true)
{

    $res = [];
    $tree = [];
    //整理数组
    foreach($pInfo as $key=>$vo){

        if($spread){
            $vo['spread'] = true;  //默认展开
        }
        $res[$vo['id']] = $vo;
        $res[$vo['id']]['children'] = [];
    }
    unset($pInfo);

    //查找子孙
    foreach($res as $key=>$vo){
        if(0 != $vo['pid']){
            $res[$vo['pid']]['children'][] = &$res[$key];
        }
    }

    //过滤杂质
    foreach( $res as $key=>$vo ){
        if(0 == $vo['pid']){
            $tree[] = $vo;
        }
    }
    unset( $res );

    return $tree;
}

/**
 * 数据统一分装返回json
 * @param type $code     //提示码
 * @param type $message  //提示信息
 * @param type $data	 //数据
 * @param $count    	 //总数
 * @param $pagecount     //总页数
 * @param $page          //当前页数
 */
//function json($code = "0", $message = "", $info = null, $count = "", $pagecount = '', $page = '')
//{
//    $data['code'] = $code;
//    $data['message'] = $message;
//    if ($count) {
//        $data['counts'] = $count;
//    }
//    if ($pagecount) {
//        $data['pagecount'] = $pagecount;
//    }
//    if ($page) {
//        $data['page'] = $page;
//    }
//    if ($info || is_array($info)) {
//        $data['data'] = $info;
//    }else{
//        $data['data'] = (object)array();
//    }
//    exit(json_encode($data, JSON_UNESCAPED_UNICODE));
//}
