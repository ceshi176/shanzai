<?php
namespace app\admin\controller;

use app\admin\model\ActivityModel;
use app\admin\model\FrameModel;
use app\admin\model\FrameTypeModel;
use app\admin\model\GoodsModel;


class Activity extends Base {

    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $activity = new ActivityModel();
            $selectResult = $activity->getActivityByWhere($where, $offset, $limit);

            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['thumbnail'] = '<img src="' . $vo['thumbnail'] . '" width="40px" height="40px">';
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] = $activity->getAllActivity($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    public function activityAdd()
    {
        if(request()->isPost()){
            $param = input('post.');

            unset($param['file']);
            $param['add_time'] = date('Y-m-d H:i:s');

            $activity = new ActivityModel();
            $flag = $activity->addActivity($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        return $this->fetch();
    }

    public function activityEdit()
    {
        $activity = new ActivityModel();
        if(request()->isPost()){
            $param = input('param.');
            unset($param['file']);
            $flag = $activity->editActivity($param);

            return msg($flag['code'], $flag['data'], $flag['msg']);
        }

        $id = input('param.id');
        $this->assign([
                'activity' => $activity->getOneActivity($id)
        ]);

        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     */
    public function activityDel()
    {
        $id = input('param.id');

        $activity = new ActivityModel();
        $flag = $activity->delActivity($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /** 根据活动页面的 ID 查找相关信息:
     *  1. 归属于当前页面的 框架
     *  2. 框架的排序类型
     *  3. 属于当前框架的商品
     */
    public function activityInfo()
    {
        $id = input('param.id');
        $frame = new FrameModel();
        $frameType = new FrameTypeModel();
        $goods = new GoodsModel();

        // 获取所有绑定到此页面的框架
        $frames =  $frame->getFrameByActivityId($id);
        // 准备页面文件
        $tmpFileName = APP_PATH . '../public/h5/tmp/tmp.html';
        $tmpFileHandle = fopen($tmpFileName, 'wb');
//        $frames = $frameType->getOneFrameType(8);
//        $goodsRes = $goods->getGoodsByFrameId(8);
//        dump($frames);
//        echo "-------------------";
//        dump($goodsRes);
//        echo "===============================================<br>";
        foreach($frames as $key=>$val)
        {
            $frameRes = $frameType->getOneFrameType($val['frame_type_id']);

            // 准备读取框架文件
            $frameHandle = fopen(APP_PATH .'../public'. $frameRes['thumbnail'],'r');
            $frameString = file_get_contents(APP_PATH .'../public'. $frameRes['thumbnail']);
//            $frameString =  "H" ;
            // 查找属于当前框架的商品
//            echo $val['id']."<br>";
            $goodsRes = $goods->getGoodsByFrameId($val['id']);
            $activity = "";
            foreach($goodsRes as $goodsKey=> $goodsVal)
            {

                // 将图片资源的路径和链接替换到网页中。
                $activity = $activity . $frameString;
                $activity = str_replace('ahref', $goodsVal['url'], $activity);
                $activity = str_replace('imgsrc',$goodsVal['img_url'], $activity);
            }
            echo $activity;
//            dump($val);
//            echo "===============================================<br>";
        }
        // 获取到的带有网页所需要数据的 array;

//        dump($frames);


    }


    public function activityBindFrame()
    {
//        $activity = new ActivityModel();
//        if(request()->isPost()){
//            $param = input('param.');
//            unset($param['file']);
//            dump($param);
////            $flag = $activity->bindActivityFrame($param);
//
////            return msg($flag['code'], $flag['data'], $flag['msg']);
//        }
//        $where = [];
//        $frame = new FrameModel();
//        $this->assign([
//            'frame' => $frame->getFrameByWhere($where, 100, 1)
//        ]);
//
//        $id = input('param.id');
//        $this->assign([
//            'activity' => $activity->getOneActivity($id)
//        ]);
        echo "is building";
//        return $this->fetch();
    }

    public function upLoadImg()
    {
        if(request()->isAjax()){

            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            if($info){
                $src =  '/upload' . '/' . date('Ymd') . '/' . $info->getFilename();
                return json(msg(0, ['src' => $src], ''));
            }else{
                // 上传失败获取错误信息
                return json(msg(-1, '', $file->getError()));
            }
        }
    }

    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'activity/activityedit',
                'href' => url('activity/activityedit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '绑定框架' => [
                'auth' => 'activity/activitybindframe',
                'href' => url('activity/activitybindframe', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-link'
            ],
            '删除' => [
                'auth' => 'activity/activitydel',
                'href' => "javascript:activityDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]

        ];
    }
}