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
namespace app\admin\model;

use think\Model;
use think\Db;
class CardModel extends Model
{
    // 确定链接表名
    protected $name = 'card';

    /**
     * 进行生成充值卡账号
     * return array
     */
    static public function createAccount($orderNumber = [], $count = [], $data = [])
    {
        //进行拼接
        $count -= 1;
        $lastData = $orderNumber . $count;
        $countLenth = strlen($lastData);
        if ($countLenth < 8) {
            //获取剩余长度
            $minusData = 8 - strlen($lastData);
            //填充0
            $str = str_repeat("0", $minusData);
            //重新拼装数组
            $lastData = substr_replace($lastData, $orderNumber . $str . $count, 0);
        }
        //开启事务
        Db::startTrans();
        try{
            $datas = [];
            //获取前三位
            $substrResult = substr($lastData, 0, 5);
            //进行生成充值卡账号和充值卡密码
            for ($i = $substrResult . "000";$i <= $lastData; $i++) {
                //随机数
                $secret = rand(1000, 9999);
                //拼接数组
                $datas[] = array(
                    "batch" => $data["batch_name"],
                    "card_number" => $i,
                    "card_secret" =>$secret,
                    "create_time" => date("Y-m-d H:i:s"),
                    "employ" => "未使用",
                    "employ_time" => "未使用",
                );
                //进行批量添加
            }
            $inserResult = self::insertAll($datas);
            // 提交事务
            Db::commit();
            //返回结果
            return $inserResult;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }

    }

    public function getCardByWhere($where, $offset, $limit)
    {
        $data = $this->where($where)->limit($offset, $limit)->select();
        foreach ($data as $dataKey => $dataVal) {
            switch ($dataVal["card_status"]) {
                case 0:
                    $data[$dataKey]["card_status"] = "未激活";
                    continue;
                case 1:
                    $data[$dataKey]["card_status"] = "已激活";
                    continue;
                case 2:
                    $data[$dataKey]["card_status"] = "已使用";
                    continue;
            }
        }
        return $data;
    }

    public function getAllCard($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getArticlesByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllArticles($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 添加卡
     * @param $param
     */
    public function addCard($param)
    {
        try{
            $result = $this->createAccount($param["card_head"], $param["card_count"], $param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('card/index'), '生成会员卡成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑充值卡信息
     * @param $param
     */
    public function editArticle($param)
    {
        try{

            $result = $this->validate('ArticleValidate')->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('articles/index'), '编辑文章成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据充值卡的id 获取充值卡的信息
     * @param $id
     */
    public function getOneArticle($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除充值卡
     * @param $id
     */
    public function delCard($id)
    {
        try{
            $uptData["is_del"] = 1;
            $this->where('id', $id)->update($uptData);
            return msg(1, '', '删除会员卡成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 提供给人员分配PeopleModel模型方法
     */
    static public function allottedCard($data)
    {
        $newCard = new CardModel();
        //验证批次是否存在并且是否分配 
        //剩余数量是否大于分配数量
        $validateBatch = BatchModel::batchCard($data); 
        if ($validateBatch["code"] == 0) {
            return $validateBatch;
        }
        $batchinfo = json_decode($validateBatch['data'],1);
        $returnData = $data["alloction_number"];
         $where = [
            'batch'=>$data['batch_name'],
            'belonger'=>'0'
        ];
 
        //验证合伙人在himall存在
        $resultData = config("secure.UrlNameLocation") . $returnData;
        $getResultData = send_curl($resultData);
        $validatePeople = ((array) json_decode($getResultData, true));
        if (!$validatePeople["Data"]["UserId"]) return ["code" => 0, "data" => "", "msg" => "请输入正确的会员账号!"];
        //修改批次归属人为返回的UserId
        //更新数据库减少更新数量，如已经已经没有剩余，直接状态为已分配完成
        $uptContent = [
            'card_count'=>$batchinfo['card_count'] -$data["batch_nums"] ,
        ];
        if($data["batch_nums"] == $batchinfo['card_count']){
            $uptContent['belonger']=$validatePeople["Data"]["UserId"];
            $uptContent['belonger_name']=$validatePeople["Data"]["UserName"];
            $uptContent['batch_status'] = 0;
        }
        $upBelonger = BatchModel::where("id" , $data["batch_id"])->update($uptContent);
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
    /**
     * 根据提供的用户手机号查询甘谷帮用户信息
     */
     public static function checkAlloctionNumber($alloction_number){
        $resultData = config("secure.UrlNameLocation") . $alloction_number;
        $getResultData = send_curl($resultData);
        $validatePeople = ((array) json_decode($getResultData, true));
        if ($validatePeople["Data"]["UserId"] == false){
            return -1;
        } else{
             return $validatePeople;
        }
    }
    /**
     * 单次激活
     */
    public function activateCard($activate_id)
    {
        //查询这张卡是否被使用或者已激活
        $queryData = $this->where("id", $activate_id)
            ->field("is_status")
            ->find();
        //判断这张卡时候使用或者激活
        if ($queryData["is_status"] == 1)  return msg(1, url('card/index'), '请勿重复激活');
        if ($queryData["is_status"] == 2)  return msg(1, url('card/index'), '此卡已使用');
        $is_status["is_status"] = "1";
        $upt = $this->where("id", $activate_id)->update($is_status);
        if(false === $upt){
            // 验证失败 输出错误信息
            return msg(-1, '', $this->getError());
        }else{
            return msg(1, url('card/index'), '激活成功');
        }
    }

    /**
     * 根据批次激活数据
     */
    public function batchCard($data, $batch_id)
    {
        //查询是这些批次的数据
        $where["batch_id"] = $batch_id;
        $where["is_status"] = 0;
        $selData = $this->where($where)->select();
        $uptData = [];
        foreach ($selData as $selkey => $selval)
        {
            $selData[$selkey]["is_status"] = $data["is_status"];
            $uptData[] = $selval->toArray();
        }
        $cardModel = new CardModel();
        $uptStatus = $cardModel->saveAll($uptData);
        if ($uptStatus === false) {
            return ["code" => 0, "data" => "", "msg" => "激活失败"];
        }
        return ["code" => 1, "data" => "", "msg" => "激活成功"];
    }

    /**
     * 根据UID重新拼装数组获得归属人
     */
    public function affiliationCard($affiliationData)
    {
        $people = new PeopleModel();
        $data = $people->queryPeople($affiliationData);
        return $data;
    }
    /**
     * $batchName 批次号
     * $order  排序
     * 获取当前批次未分配的最小card_number
     */
    public static function getBatchMinId($batchName,$order="id asc"){
        $info =  CardModel::where(array('batch'=> $batchName,'belonger'=>0))->order($order)->find();
        $mincard = json_decode($info,1);
        return  $mincard['card_number'];
    }
    /**
     * $batchName 批次号
     * $order  排序
     * 获取当前批次未分配的最小card_number
     */
    public static function getBatchMinIdById($batchid,$order="id asc"){
        $info =  CardModel::where(array('batch_id'=> $batchid))->order($order)->find();
        $mincard = json_decode($info,1);
        return  $mincard['card_number'];
    }
    /**
     * @return 
     * 获取建卡总数 totalCard
     * 剩余未分配卡数 totalLeftCard
     * 已分配总数   belogerCard
     * 用户已激活数量  userCardCount
     * 已分配未激活    useCardLeftCount
     * 剩余不为空的批次总数   batchLeftCount
     * 
     */
    public static function getCardanalysis(){
        //获取建卡总数
        $info["totalCard"] = CardModel::count();
        $info['totalLeftCard'] =  CardModel::where(array('belonger'=>0))->count();
        $info["belogerCard"] = CardModel::where("belonger",">","0")->count();
        $info["userCardCount"] = CardModel::where("card_status","1")->count();
        $info["userCardMoneyTotal"] = BatchLogModel::sum("card_count*denomination");
        $info["useCardLeftCount"] = $info["belogerCard"] -  $info["userCardCount"];
        $info["batchTotalCount"] = BatchModel::where("id",">","0")->count();
        $info["batchLeftCount"] = BatchModel::where("card_count",">","0")->where("is_del","0")->count();
        $info["batchUseCount"] = BatchModel::where("card_count","0")->where("is_del","0")->count();
        $info["batchDeleteCount"] = BatchModel::where("is_del","1")->count();
        return $info;
    }
}
