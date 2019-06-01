<?php
/**
 * Created by PhpStorm.
 * User: ALIENWARE
 * Date: 2019/3/22
 * Time: 13:47
 */

namespace app\admin\model;


use think\Model;

class BatchModel extends Model
{
    protected $name = 'batch';

    /**
     * 查询批次是否重复
     */
    static public function batchCard($card_batch)
    {
        $batchData = new BatchModel();
        //查询数据
        $query = $batchData
            ->where("batch_name", $card_batch["batch_name"])
            ->find();
        if (empty($query)) return ["code"=>0,"data"=>"","msg"=>"批次不存在"];
        if ($query["belonger"] != "未分配"){
            return ["code"=>0,"data"=>"","msg"=>"批次已分配"];
        }elseif($query["card_count"] < $card_batch['batch_nums']){
            return ["code"=>0,"data"=>"","msg"=>"批次剩余数量不足"];
        } 
       if (!(is_int($card_batch['batch_nums']+0)&&($card_batch['batch_nums']+0)>0))
        {
           return ["code"=>0,"data"=>"","msg"=>"填写分配数量必须为正整数"];
        }
        $search = '/^[0][0-9]+$/';
        if(preg_match($search,$card_batch["batch_nums"])) {
              return ["code" => 0, "data" => "", "msg" => "分配数量不能以0开头!"];
       }
        return ["code"=>1,"data"=>$query,"msg"=>"success"];
    }

    /**
     * 查询批次是否重复
     */
    static public function batchBatch($card_batch)
    {
        $batchData = new BatchModel();
        //查询数据
        $query = $batchData
            ->where("batch_name", $card_batch["batch_name"])
            ->find();
        if (!empty($query)) return ["code"=>0,"data"=>"","msg"=>"批次已存在"];
        return ["code"=>1,"data"=>"","msg"=>"success"];
    }
    /**
     * 根据批次激活
     */
    static public function activateBatch($activateData)
    {
        //查询批次是否存在
        $query = self::where("card_batch", $activateData)->find();
        if ($query == false) return ["code" => 0, "data" => "", "msg" => "批次不存在"];
        //修改批次状态
        $editData = self::where("card_batch", $activateData)
            ->field("is_status")
            ->find();
        if ($editData["is_status"] == 1) return ["code" => 0, "data" => "", "msg" => "该前缀已激活"];
        //拼装修改数据
        $uptData["is_status"] = 1;
        //修改状态
        $modify = self::where("card_batch", $activateData)->update($uptData);
        //进行修改状态
        $card = new CardModel();
        $uptStatus = $card->batchCard($uptData, $query["id"]);
        return $uptStatus;
    }

    public function getCardByWhere($where, $offset, $limit)
    {
        $data = $this->where($where)->field('id,batch_name,denomination,card_count,card_head,card_count,create_date,belonger,belonger_name,is_del')->order("card_count desc,create_date desc")->limit($offset, $limit)->select();
        return $data;
    }

    public function getAllCard($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 添加批次方法
     */
    static public function addBatch($data)
    {
        set_time_limit(0);
        //进行拼装数据添加批次
        $data["create_date"] = date("Y-m-d H:i:s");
        $data["belonger"] = "未分配";
        $data["belonger_name"] = "未分配";
        $num = ceil( $data["card_count"]/1000);
        $card_count =$data["card_count"];
        $card_head =$data["card_head"];
        //检查卡批次是否存在
         $validate = BatchModel::validateCardHeads($card_head,$card_head+$num-1);
         $validate = json_decode($validate,1);
         if (!empty($validate)) return msg(0, $validate, "卡标头已存在");
         
        for($i=0;$i<=$num-1;$i++){
            $data["card_head"] = $card_head +$i;
            //自动填充 批次号为卡号标头
            if($i==$num-1){
                 $data["card_count"] =$card_count - $i*1000;
            }else{
                 $data["card_count"] = 1000;
            }
           
            $data["batch_name"] = $data["card_head"];
             $addData = self::insert($data);
            //判断是否添加成功
            if ($addData == true) {
                //调用添加卡号方法
                $card = new CardModel();
                $addCard = $card->addCard($data);
             
            }
        }
        if ($addData == true) {
            return msg(1, url('batch/index'), '生成会员卡成功');
        }
    }                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  

    /**
     * 查询卡标头是否存在
     */
    static public function validateCardHead($data)
    {
        $validate = BatchModel::where("card_head", $data)->find();
        return $validate;
    }
     /**
     * 区间查询卡标头是否存在
      * $start 开始
      * $end  结束
     */
    static public function validateCardHeads($start,$end)
    {
        $validate = BatchModel::where("card_head", 'between',[$start,$end])->find();
        return $validate;
    }
    /**
     * 删除批次
     * @param $id
     */
    public function delBatch($id)
    {
        try{
            $data["is_del"] = 1;
            $this->where('id', $id)->update($data);
            return msg(1, '', '删除此批次会员卡成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 根据id获取出UserId
     */
    public static function queryBatch($id)
    {
        $queryId = CardModel::where("id", $id)
            ->field("batch")
            ->find();
        $belonger = self::alias("b")
            ->where("batch_name", $queryId["batch"])
            ->join("card c","b.batch_name = c.batch")
            ->find();
        return $belonger["belonger"];
    }

    /**
     * 根据Id获取默认分配批次
     */
    static public function returnDefaultBatchName($id)
    {
        //根据ID获取分配批次
        $defaultData = self::where("id", $id)
            ->field("batch_name")
            ->find();
        return $defaultData;
    }
     public function getBatchByWhere($where, $offset, $limit)
    {
        $data = $this->where($where)->field('id,batch_name,denomination,card_count,create_date,belonger,belonger_name,is_del,batch_status')->order("create_date desc")->limit($offset, $limit)->select();
        return $data;
    }

    public function getAllBatch($where)
    {
        return $this->where($where)->count();
    }
    /**
     * 根据Id获取默认分配批次
     */
    static public function getBatchDetail($id)
    {
        //根据ID获取分配批次
        $defaultData = self::where("id", $id)
            ->find();
        return $defaultData;
    }
}