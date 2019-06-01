<?php
namespace app\admin\controller;

use app\admin\model\FrameTypeModel;

class FrameType extends Base
{
    public function index()
    {
//        $frame = new FrameModel;
//        $selectResult = $frame->getFrameByWHere([], 10, 10);
//        dump($selectResult);
        if(request()->isAjax()){
//            dump(input('param.'));
            $param = input('param.');
            $param['pageSize'] = 10;
            $param['pageNumber'] = 1;
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] -1) * $limit;

            $where = [];
            // 判断是否根据标题搜索
            if(!empty($param['searchText'])){
                $where['title'] = ['like ', '%' . $param['searchText'] . '%'];
            }

            $frame = new FrameTypeModel;
            $selectResult = $frame->getFrameTypeByWHere($where, $offset, $limit);

            foreach ($selectResult as $key=>$vo) {
//                dump($this->makeButton($vo['id']));
//                echo " ------------------------ <br/>";
//                dump(showOperate($this->makeButton($vo['id'])));

                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $frame->getAllFrameType($where);
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    public function frameTypeAdd()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['add_time'] = date('Y-m-d H:i:s');
            $frameType = new FrameTypeModel();
            $flag = $frameType->addFrameType($param);
            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }


    public function frameTypeEdit()
    {
        $frame = new FrameTypeModel();
        if(request()->isPost()){
            $param = input('param.');
            $flag = $frame->editFrameType($param);
            return msg($flag['code'], $flag['data'], $flag['msg']);
        }

        $id = input('param.id');

        $this->assign([
            'frameType' => $frame->getOneFrameType($id)
        ]);
        return $this->fetch();

//        echo "frame edit";
    }

    public function frameTypeDel()
    {
            $id = input('param.id');
            $frame = new FrameTypeModel();
            $flag = $frame->delFrameType($id);
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
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'frametype/frametypeedit',
                'href' => url('frametype/frametypeedit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'frametype/frametypedel',
                'href' => "javascript:frameTypedel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

}