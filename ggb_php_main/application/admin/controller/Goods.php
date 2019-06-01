<?php
namespace app\admin\controller;

use app\admin\model\GoodsModel;
use app\admin\model\FrameModel;

class Goods extends Base
{
    public function index()
    {
//        dump(request());
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $goods = new GoodsModel();
            $selectResult = $goods->getGoodsByWhere($where, $offset, $limit);
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['img_url'] = '<img src="' . $vo['img_url'] . '" width="100px" height="100px">';
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $goods->getAllGoods($where);  // 总数据
            $return['rows'] = $selectResult;
        }
            return json($return);
        return $this->fetch();
    }

    public function goodsAdd()
    {
        if(request()->isPost()){
            $param = input('post.');
            unset ($param['file']);
            $param['add_time'] = date('Y-m-d H:i:s');

            $goods = new GoodsModel();
            $flag = $goods->addGoods($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        $frameType = new FrameModel();
        $where = [];
        $res = $frameType->getAllFrameByWhere($where, 100, 1);
        $this->assign([
            'frame' => $res
        ]);

        return $this->fetch();
    }

    public function goodsEdit()
    {
        $goods = new GoodsModel();
        if(request()->isPost()){

            $param = input('post.');
            unset($param['file']);
            $flag = $goods->editGoods($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        // 获取 frame
        $frameType = new FrameModel();
        $where = [];
        $res = $frameType->getAllFrameByWhere($where, 100, 1);
        $this->assign([
            'frame' => $res
        ]);

        $id = input('param.id');
        $this->assign([
            'goods' => $goods->getOneGoods($id)
        ]);
        return $this->fetch();
    }

    public function goodsDel()
    {
        $id = input('param.id');
        $goods = new GoodsModel();
        $flag = $goods->delGoods($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }


    // 上传图片
    public function uploadImg()
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

    // 组装操作按钮
    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'goods/goodsedit',
                'href' => url('goods/goodsedit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'goods/goodsdel',
                'href' => "javascript:goodsDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }



}