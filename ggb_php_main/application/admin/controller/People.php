<?php
/**
 * Created by PhpStorm.
 * User: ALIENWARE
 * Date: 2019/3/22
 * Time: 14:12
 */

namespace app\admin\controller;


use think\Controller;
use app\admin\model\PeopleModel;
use think\Session;
use app\admin\model\BatchModel;
use app\admin\model\CardModel;
class People extends Base
{
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
                $where['card_number'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $batch = new BatchModel();
            $selectResult = $batch->getCardByWhere($where, $offset, $limit);
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]["card_count"] = $vo["card_count"] . "张";
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $batch->getAllCard($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch("batch");
    }

    /**
     * 进行删除
     * @return \think\response\Json
     */
    public function peopleDel()
    {
        $id = input('param.id');
        $batch = new BatchModel();
        $flag = $batch->delBatch($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /**
     * 根据批次添加卡
     * @return mixed|\think\response\Json
     */
    public function batchAddCard()
    {
        if(request()->isPost()){
            $param = input('post.');
            $getStrlen = strlen($param["card_head"]);
            //验证卡标头是否存在
            $result = $this->validate($param, "BatchValidate");
            if(true !== $result){
                // 验证失败 输出错误信息
                return json(msg(1, "", $result));
            }
            //去掉批次号验证，只限制卡标头验证
           // $result = BatchModel::batchBatch($param);
           // if ($result["code"] == 0) return json(msg(1, '', "批次名已存在"));
            $validate = BatchModel::validateCardHead($param["card_head"]);
            if (!empty($validate)) return json(msg(1, '', "卡标头已存在"));
            //验证批次是否重复
            if ($getStrlen > 5 || $getStrlen < 5) return json(msg(1, '', "卡标头只能是5位纯数字"));
            if(substr($param["card_head"], -5,1) == 0) return json(msg(1, '', "卡标头不能以0开头"));
            if ($param["card_count"] < 1) return json(msg(1, '', "数量最小限额为1"));
            if ($param["card_count"] > 1000) return json(msg(1, '', "数量最大限额为1000"));
            $flag = BatchModel::addBatch($param);
            //返回信息
            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch("batch_add");
    }
    /**
     * 校验手机号 是否是平台用户
     */
    public function check_user(){
        $alloction_number = input('param.alloction_number');
        $getResultData = CardModel::checkAlloctionNumber($alloction_number);
        if($getResultData["Success"]){
              return json(msg('1', $getResultData['Data'],'' ));
        }else{
             return json(msg('0', '', '此手机号不是合伙人'));
        }
    }

    /**
     * 进行分配
     */
    public function peopleAllocation()
    {
        if (request()->isPost()) {
            $allotted = input("post.");
            //验证批次是否被分配并且账号存在
           // var_dump($allotted);exit;
            $allotted = PeopleModel::allottedPeople($allotted);
            return json(msg($allotted['code'], $allotted['data'], $allotted['msg']));
        }
        $id = input();
        $data = BatchModel::returnDefaultBatchName($id["id"]);
        $this->assign("data", $data["batch_name"]);
        $this->assign("id", $id["id"]);
        return $this->fetch("batch_allocation");
    }

    /**
     * 业务员添加
     */
    public function peopleAdd()
    {
        if(request()->isPost()){
            $param = input('post.');
            $people = new PeopleModel();
            $flag = $people->addPeople($param);
            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch("people_add");
    }

    /**
     * 进行人员修改
     */
    public function peopleUpt()
    {
        $people = new PeopleModel();
        if(request()->isPost()){
            $param = input('post.');
            $flag = $people->uptPeople($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'people' => $people->getOnPeople($id)
        ]);
        return $this->fetch("people_upt");
    }

    /**
     * 进行数据导出
     * @param $id
     * @return array
     */
    public function downLoadCardInfo()
    {
        $data = input("batch");
        if (isset($data)) {
            //定义文件名
            $whereData = [
                "batch_name" => $data,
                "is_del" => 0
            ];
            $query = db("batch")
                ->where($whereData)
                ->field("batch_name, denomination")
                ->find();
            $fileName = "会员卡" . "-" . $query["batch_name"] . "-" . $query["denomination"];
            //获取数据
            $queryData = [
                "batch" => $query["batch_name"],
                "c.is_del" => "0"
            ];
            $data = db("card")
                ->alias("c")
                ->where($queryData)
                ->join("batch b","b.batch_name = c.batch")
                ->field("card_number,card_secret")
                ->select();
            //进行数据导出
            loadExcel($data, $fileName);
        }
    }

    /**
     * 导出页面
     * @return mixed
     */
    public function returnDerive()
    {
        return $this->fetch("derive");
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '分配' => [
                'auth' => 'people/peopleallocation',
                'href' => url('people/peopleallocation', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'people/peopledel',
                'href' => "javascript:peopleDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
//            '编辑' => [
//                'auth' => 'people/peopleupt',
//                'href' => url('people/peopleupt', ['id' => $id]),
//                'btnStyle' => 'primary',
//                'icon' => 'fa fa-paste'
//            ],
        ];
    }
}