<?php
namespace app\admin\controller;

use app\admin\model\FrameModel;
use app\admin\model\FrameTypeModel;
use app\admin\model\ActivityModel;

class Frame extends Base
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

            $frame = new FrameModel;
            $selectResult = $frame->getFrameByWHere($where, $offset, $limit);

            foreach ($selectResult as $key=>$vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $frame->getAllFrame($where);
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    public function frameAdd()
    {
        if(request()->isPost()){
            $param = input('post.');
            $param['add_time'] = date('Y-m-d H:i:s');
            $frame = new FrameModel();
            $flag = $frame->addFrame($param);
            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        // 获取框架类型列表
        $where = [];
        $offset = 0;
        $limit = 100;
        $frameType = new FrameTypeModel();
        $resFrameType = $frameType->getFrameTypeByWhere($where, $offset, $limit);
//        dump($resFrameType);
        $this->assign([
            'frameType' => $resFrameType
        ]);

        // 获取页面列表
        $activity = new ActivityModel();
        $resActivity = $activity->getActivityByWhere($where, $offset, $limit);

        $this->assign([
            'activity' => $resActivity
        ]);
        return $this->fetch();
    }


    public function frameEdit()
    {
        $frame = new FrameModel();
        if(request()->isPost()){
            $param = input('param.');
            $flag = $frame->editFrame($param);
            return msg($flag['code'], $flag['data'], $flag['msg']);
        }

        //
        $where = [];
        $offset = 0;
        $limit = 100;
        $frameType = new FrameTypeModel();
        $res = $frameType->getFrameTypeByWhere($where, $offset, $limit);
        $this->assign([
            'frameType' => $res
        ]);

        // 获取页面列表
        $activity = new ActivityModel();
        $resActivity = $activity->getActivityByWhere($where, $offset, $limit);

        $this->assign([
            'activity' => $resActivity
        ]);

        $id = input('param.id');
        $this->assign([
            'frame' => $frame->getOneFrame($id)
        ]);
        return $this->fetch();

//        echo "frame edit";
    }

    public function framedel()
    {
            $id = input('param.id');
            $frame = new FrameModel();
            $flag = $frame->delFrame($id);
            return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'frame/frameedit',
                'href' => url('frame/frameedit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'frame/framedel',
                'href' => "javascript:frameDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

}