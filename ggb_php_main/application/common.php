<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Request;

// 导出 EXCEL 
function loadExcel($data, $fileName)
{
    //定义标题
    $title[] = [
            "card_number" => "卡号",
            "card_secret" => "密码"
        ];
    $data = array_merge($title, $data);
    // 头部标题
    $content = '';
    foreach ($data as $k => $v) {
        $content .= implode(','."\t", $v) . PHP_EOL;
    }
    $csvData = $content;
//        halt($csvData);
    header("Content-type:text/csv;");
    header("Content-Disposition:attachment;filename=" . $fileName."."."csv");
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');
    echo $csvData;
}


// 获取当前的域名


function domain()
{
    // 获取当前域名
    $request = Request::instance();
    $domain = $request->domain();
    return $domain;

}

function reJson($code, $msg, $data)
{
    return ['code' => $code, 'msg' => $msg, 'data' => $data];
}


//参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
/**
 * @param $url
 * @param string $data
 * @param string $cookie
 * @param int $returnCookie
 * @return mixed|string
 */
function curl_request($url,$data='',$cookie='', $returnCookie=0){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
    if($data) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, array('Content-Type: application/json','Content-Length:'. strlen($data)));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    if($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
//    if($returnCookie){
//        list($header, $body) = explode("\r\n\r\n", $result, 2);
//        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
//        $info['cookie']  = substr($matches[1][0], 1);
//        $info['content'] = $body;
//        return $info;
//    }else{
        return $result;
//    }
}


function send_curl_xld($url)
{
//    if (empty($send_data["UserName"])) $send_data["UserName"] = "17332764459";
    //请求URL地址
//    $url = "http://xiaoyao.ssup.cn/api/UserBind/GetUserIdByUserNameOrCellPhone?UserName=" . $send_data["UserName"];
    //请求数据格式
    $header = array("Content-type: application/json");
    //初始化
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_VERBOSE, '1');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $tmpInfo = curl_exec($curl);     //返回api的json对象
    $result = ((array) json_decode($tmpInfo, true));
    //关闭URL请求
    curl_close($curl);
    return $result;    //返回json对象
}

function send_url_post($url, $arr)
{
    //请求数据格式
    $header = array("Content-type: application/json");
    //初始化
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_VERBOSE, '1');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arr));
    //提交
    $result = curl_exec($ch);
    //进行转换
    $data = (array) json_decode($result);
    //写日志
    curl_close($ch);
    return $data;
}

// 跨域中转使用
function send_curl($url)
{
//    if (empty($send_data["UserName"])) $send_data["UserName"] = "17332764459";
    //请求URL地址
//    $url = "http://xiaoyao.ssup.cn/api/UserBind/GetUserIdByUserNameOrCellPhone?UserName=" . $send_data["UserName"];
    //请求数据格式
    $header = array("Content-type: application/json");
    //初始化
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_VERBOSE, '1');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $tmpInfo = curl_exec($curl);     //返回api的json对象
    //关闭URL请求
    curl_close($curl);
    return $tmpInfo;    //返回json对象
}

// 跨域中转使用
function curlPost($url,$data)
{
    //请求URL地址
//    $url = "http://cs.ssup.cn/api/NewFenHong/PostFirstOrderGiveRice";
//请求数据格式
    $header = array("Content-type: application/json");
//初始化
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_VERBOSE, '1');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//提交
    $result = curl_exec($ch);
//进行转换
//    $data = (array) json_decode($result);
//    $beg = $this->WriteLog("order", "首单推送", json_encode($arr), json_encode($data));
//进行状态修改
    curl_close($ch);
    return $result;
}


/**
 * 获取 sign
 * @param $arr
 * @return string
 *
 * author : FrankLee  dateTime : 2019-04-17 14:59
 */
function signPDD($arr)
{
    ksort($arr);
    $str = '';
    foreach ($arr as $key => $val){
        $str .= $key . $val;
    }
//    dump($str);
    $clientSecret = config('pdd_client_secret');
    return strtoupper(md5($clientSecret . $str . $clientSecret));
}

/**
 * 从拼多多获取相应的信息。
 * @param $arr
 * @return array|bool|mixed|string
 *
 * author : FrankLee  dateTime : 2019-04-17 16:19
 */
function getProductInfoFromPDD($arr)
{
    $dataTime = time();
    $arr['timestamp'] = $dataTime;
    $arr['sign'] = signPDD($arr);
//    dump($arr);
    $result = curlPost(config('pdd_api'),$arr);
    $result = (array)json_decode($result,true);
    if (array_key_exists('error_response', $result)){
        $result = $result['error_response'];
        return $result['error_msg'];
    }else{
        return $result;
    }
}

/**
 * 二维数组根据指定 $key 对应的值去重
 * @param $arr
 * @param $key
 * @return mixed
 *
 * author : FrankLee  dateTime : 2019-04-21 15:32
 */
function assoc_unique($arr, $key) {

    $tmp_arr = array();

    foreach ($arr as $k => $v) {

        if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true

            unset($arr[$k]);

        } else {

            $tmp_arr[] = $v[$key];

        }

    }

    sort($arr); //sort函数对数组进行排序

    return $arr;

}

/**
 * 统一返回信息
 * @param $code
 * @param $data
 * @param $msge
 */
function msg($code, $data, $msg)
{
    return compact('code', 'data', 'msg');
}
