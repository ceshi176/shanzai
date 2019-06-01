<?php

namespace app\admin\controller;
use app\admin\model\BatchLogModel;
use app\admin\model\BatchModel;
use app\admin\model\CardModel;
use think\Session;
/**
 * Description of Batchlog
 *
 * @author ljl
 * @mark 发卡历史记录表
 */
class Batchlog extends Base{
     /**
     * 批次
     */
    public function index()
    {
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            $where["is_del"] = 0 ;
            if (!empty($param['searchText'])) {
                $where['batch_name|belonger_name'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $batch = new BatchLogModel();
            $selectResult = $batch->getCardByWhere($where, $offset, $limit);
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]["card_count"] = $vo["card_count"] . "张";
                //$selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $batch->getAllCard($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch("batch");
    }
    /**
     * 根据进行分配
     */
    public function batchreallocation()
    {
        if (request()->isPost()) {
            $allotted = input("post.");
            //验证批次是否被分配并且账号存在
            //重新分配账号
            $allotted = BatchLogModel::allotteredCard($allotted);
            //var_dump($allotted);exit;
            return json(msg($allotted['code'], $allotted['data'], $allotted['msg']));
        }
        $id = input();
        $data = BatchLogModel::getBatchLogDetail($id["id"]);
        $mincardno = CardModel::getBatchMinIdById($data["id"]);
        $maxcardno =  $mincardno + $data["card_count"]-1;
        $this->assign("mincardno", $mincardno);
        $this->assign("maxcardno", $maxcardno);
        $this->assign("data", $data);
        $card = new BatchModel();
        $batchinfo = $card->getBatchDetail($data["batch_id"]);
        $this->assign("batchinfo", json_decode($batchinfo,1));
        return $this->fetch("batch_reallocation");
    }
     /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '重新分配' => [
                'auth' => 'batchlog/batchreAllocation',
                'href' => url('batchlog/batchreAllocation', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
        ];
    }
}
