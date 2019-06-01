<?php


/**
 * Description of BatchLogModel
 *
 * @author ljl
 * @time 2019-04-17
 * @mark 用户发卡记录
 */
namespace app\admin\model;
use think\Model;
class BatchLogModel extends Model{
    protected $name = 'batchlog';
    static public function addBatchLog($param) {
         $param['batch_id'] = $param['id'];
        unset($param['id']);
        $id = self::insert($param,1);
        return self::getLastInsID();
    }
    public function getCardByWhere($where, $offset, $limit)
    {
        $data = $this->where($where)->field('id,batch_name,denomination,card_count,create_date,belonger,belonger_name,is_del')->order("create_date desc")->limit($offset, $limit)->select();
        return $data;
    }
    public function getAllCard($where)
    {
        return $this->where($where)->count();
    }
        /**
     * 根据Id获取默认分配批次
     */
    static public function getBatchLogDetail($id)
    {
        //根据ID获取分配批次
        $defaultData = self::where("id", $id)
            ->find();
        return json_decode($defaultData,1);
    }
     /**
     * batchlog 把原有分配记录重新分配给另一个人
      * 1、更新card 表中blonger 推荐人id
      * 2、更新batchlog  表中blonger 推荐人id  blonger_name
     */
    static public function allottedCard($data)
    {
        //验证批分配记录是否存在
        $validateBatch = BatchLogModel::getBatchLogDetail($data['id']); 
        if ($validateBatch["id"] == 0) {
            return ["code" => 0, "data" => "", "msg" => "分配记录不存在，请重新选择再进行分配"];
        }
        //验证合伙人在himall存在
        $resultData = config("secure.UrlNameLocation") . $returnData;
        $getResultData = send_curl($resultData);
        $validatePeople = ((array) json_decode($getResultData, true));
        if (!$validatePeople["Data"]["UserId"]) return ["code" => 0, "data" => "", "msg" => "请输入新的正确会员账号"];
        //修改批次归属人为返回的UserId

        $batchinfo['belonger']=$validatePeople["Data"]["UserId"];
        $batchinfo['belonger_name']=$validatePeople["Data"]["UserName"];
        $upBelonger = BatchLogModel::where("id" , $data["id"])->update($batchinfo);
        //更新卡的批次id
        
        //增加分配记录日志
        $batchinfo['belonger']=$validatePeople["Data"]["UserId"];
        $batchinfo['belonger_name']=$validatePeople["Data"]["UserName"];
        $batchinfo['card_count']=$data["batch_nums"];
        $batchinfo['create_date']=date("Y-m-d H:i:s");
        $batchid = BatchLogModel::addBatchLog($batchinfo);
       
        //更新batchlog 分配的数据库id  
        //todo  数据量巨大，暂时不存 去card表中存
//        $cardlist =CardModel::where($where)->order("id asc")->limit($data["batch_nums"])->column("card_number");
//        $c = json_encode($cardlist);
//        var_dump($c);exit;
         //更新card表中belonger 所属人id
        $where = [
            'batch'=>$data['batch_name'],
            'belonger'=>'0'
        ];
        CardModel::where($where)->order("id asc")->limit($data["batch_nums"])->update(array("belonger"=>$validatePeople["Data"]["UserId"],'batch_id'=>$batchid));
        return msg(1, url('batch/index'), '分配成功');
    }
}
