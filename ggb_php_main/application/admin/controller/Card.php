<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\BatchModel;
use app\admin\model\CardModel;
use think\Session;
class Card extends Base
{
    //卡列表
    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            $where["is_del"] = 0;
            if (!empty($param['searchText'])) {
                $where['card_number'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $article = new CardModel();
            $selectResult = $article->getCardByWhere($where, $offset, $limit);
            
            if(authCheck('card/showsecret')){
                $showsecret ="1";
            }else{
                $showsecret="0";
            }
            foreach($selectResult as $key=>$vo){
                if($showsecret !='1'){
                     $selectResult[$key]['card_secret']="****";
                }
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $article->getAllCard($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
         $this->assign("card_info", CardModel::getCardanalysis());   
        return $this->fetch("returnCard");
    }

    //删除
    public function cardDel()
    {
        $id = input('param.id');
        $card = new CardModel();
        $flag = $card->delCard($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /**
     * 查看卡信息
     */
    public function seeCardMessage()
    {
        if(request()->isAjax()){
            $param = input('param.');
            //获取卡的ID
            $card_id = Session::get("send_id");
            //根据ID查出归属人ID
            $query = BatchModel::queryBatch($card_id);
            //根据卡的ID请求C#获取用户的信息
            $data = CardModel::where("id", $card_id)->find();
            if ($data["employ"] == "未使用") $data = [];
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['card_number'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $article = new CardModel();
            $selectData[] = $data;
            if (empty($selectData[0])) $selectData = [];
            $return['total'] = count($selectData);  // 总数据
            $return['rows'] = $selectData;

            return json($return);
        }
    }

    public function returnMessage($id)
    {
        Session::set("send_id", $id);
        //返回页面
        return $this->fetch("particulars");
    }
    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '查看卡信息' => [
                'auth' => 'card/returnmessage',
                'href' => url('card/returnmessage', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'card/carddel',
                'href' => "javascript:cardDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

    //会员卡列表
//    public function batchIndex()
//    {
//        if(request()->isAjax()){
//
//            $param = input('param.');
//            $limit = $param['pageSize'];
//            $offset = ($param['pageNumber'] - 1) * $limit;
//            $where = [];
//            if (!empty($param['searchText'])) {
//                $where['card_number'] = ['like', '%' . $param['searchText'] . '%'];
//            }
//
//            $article = new BatchModel();
//            $selectResult = $article->getCardByWhere($where, $offset, $limit);
//            foreach($selectResult as $key=>$vo){
//                $selectResult[$key]["card_count"] = $vo["card_count"] . "张";
//                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
//            }
//
//            $return['total'] = $article->getAllCard($where);  // 总数据
//            $return['rows'] = $selectResult;
//
//            return json($return);
//        }
//
//        return $this->fetch("batch");
//    }

}
