<?php

namespace app\api\controller;

use think\Config;
use think\Controller;
use app\admin\model\NormalModel;
use think\Request;

class Index extends Controller
{
    // 返回所有活动
    /**
     * @return array
     *   {
            "code": 1,
            "msg": "success",
            "data": [{
                "id": 9,
                "title": "帮你挑好货",
                "url": "http://faxian.gangubang.net/normal/9.html",
                "shareImg": "http://faxian.gangubang.net/upload/20190328/de4b3b8d84c917bb7daf1098c4f23804.png",
                "description": "亲，我已经帮你把好货都挑出来了，快来看一看吧"
            }]
        }
    **/
    public function getFindNormal()
    {
        Config::set('default_return_type','json');

        $normalModel = new NormalModel();
        $res =  $normalModel->getNormalForApi();
        $arr = [];
        foreach ($res as $key => $vo){
            if(null == $vo['url'] || null == $vo['thumbnail']){
                unset($res[$key]);
            }else{
                $arr[$key]['id'] = $vo['id'];
                $arr[$key]['title'] = $vo['title'];
                $arr[$key]['url'] = $vo['url'];
                $arr[$key]['shareImg'] = $vo['thumbnail'];
                $arr[$key]['description'] = $vo['description'];
            }
        }

        //如果$normalArr 中的索引不连贯 json_encode 会将索引默认为 key 值输出
        $data = array_values($arr);
        if(empty($data) || is_null($data))
        {
            return reJson(0,'当前没有活动',$data);
        }else{
            return reJson(1, 'success', $data);
        }
    }


    // 接口转发
    public function transferApi(Request $request)
    {
        Config::set('default_return_type','json');
        if($request->isPost()){
            $param = $request->post();
            $url = $param['url'];
            if('get' == $param['type']){
                $str = "";
                return send_curl($url);
            }elseif('post' == $param['type']){
                $data = $param['data'];
                return curlPost($url,$data);
            }else{
                return reJson(0, 'fail 哪种请求?',[]);
            }

        }else{
            return reJson(0, 'fail 不接收的请求类型',[]);
        }

    }

}