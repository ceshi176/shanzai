<?php
namespace app\admin\controller;

use app\admin\model\ProducttitleModel;
use app\admin\model\NormalModel;

class Producttitle extends Base
{
    // 文章列表
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

            $producttitle = new ProducttitleModel();
            $selectResult = $producttitle->getProducttitleByWhere($where, $offset, $limit);

            foreach($selectResult as $key=>$vo){
                // $selectResult[$key]['normal_title'] = getNormalTitLeById($selectResult[$key]['normal_id']);
                $selectResult[$key]['thumbnail'] = '<img src="' . $vo['thumbnail'] . '" width="40px" height="40px">';
                $selectResult[$key]->append(['normal_title']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] = $producttitle->getAllProducttitle($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    // 添加文章
    public function producttitleAdd()
    {
        if(request()->isPost()){
            $param = input('post.');

            unset($param['file']);
            $param['add_time'] = date('Y-m-d H:i:s');

            $producttitle = new ProducttitleModel();
            $flag = $producttitle->addProducttitle($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        $this->assign([
            'normal'=> $this->getAllNormalInfo()
        ]);

        return $this->fetch();
    }

    public function producttitleEdit()
    {
        $producttitle = new ProducttitleModel();
        if(request()->isPost()){

            $param = input('post.');
            unset($param['file']);
            $flag = $producttitle->editProducttitle($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'producttitle' => $producttitle->getOneProducttitle($id),
            'normal'       => $this->getAllNormalInfo()
        ]);


        return $this->fetch();
    }

    public function producttitleDel()
    {
        $id = input('param.id');

        $producttitle = new ProducttitleModel();
        $flag = $producttitle->delProducttitle($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    // 上传缩略图
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

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'producttitle/producttitleedit',
                'href' => url('producttitle/producttitleedit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'producttitle/producttitledel',
                'href' => "javascript:producttitleDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

    /**
     * 获取所有的页面列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function getAllNormalInfo()
    {
        $normal = new NormalModel();
        $where = [];
        $offset  = 0;
        $limit = 100;
        return $normal->getNormalByWhere($where, $offset, $limit);

    }
}
