<?php
namespace app\admin\controller;

use app\admin\model\BatchModel;
use app\admin\model\BatchLogModel;
use app\admin\model\CardModel;
use app\admin\model\PeopleModel;
use think\Session;
/**
 * Description of Batch
 *
 * @author ljl
 * @descrition 批次建卡
 */
class Batch extends Base{
    //put your code here
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
                $where['batch_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $batch = new BatchModel();
            $selectResult = $batch->getBatchByWhere($where, $offset, $limit);
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]["batch_status"] = $vo["batch_status"] == 0 ?"已分配":"未分配";
                $selectResult[$key]["card_count"] = $vo["card_count"] . "张";
 //               $selectResult[$key]["mincardno"] = CardModel::getBatchMinId($vo["batch_name"]);
//                $selectResult[$key]["maxcardno"] =  $selectResult[$key]["mincardno"] + $selectResult[$key]["card_count"]-1;
                if( $vo["card_count"]>0){
                      $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                } else {
                      $selectResult[$key]['operate'] = showOperate($this->makeButton_0($vo['id']));
                }
            }
            $return['total'] = $batch->getAllBatch($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        $this->assign("card_info", CardModel::getCardanalysis());
        return $this->fetch("batch");
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
                return json(msg(0, "", $result));
            }
            //去掉批次号验证，只限制卡标头验证
           // $result = BatchModel::batchBatch($param);
           // if ($result["code"] == 0) return json(msg(1, '', "批次名已存在"));
            $validate = BatchModel::validateCardHead($param["card_head"]);
            if (!empty($validate)) return json(msg(0, $validate, "卡标头已存在!"));
           
            //验证批次是否重复
            if ($getStrlen > 5 || $getStrlen < 5) return json(msg(0, '', "卡标头只能是5位纯数字"));
            if(substr($param["card_head"], -5,1) == 0) return json(msg(0, '', "卡标头不能以0开头"));
            if ($param["card_count"] < 1) return json(msg(0, '', "数量最小限额为1"));
            //if ($param["card_count"] > 1000) return json(msg(1, '', "数量最大限额为1000"));
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
             return json(msg('0', '', '此手机号不是注册用户'));
        }
    }

    /**
     * 进行分配
     */
    public function batchAllocation()
    {
        if (request()->isPost()) {
            $allotted = input("post.");
            //验证批次是否被分配并且账号存在
            //var_dump($allotted);exit;
            $allotted = CardModel::allottedCard($allotted);
            //var_dump($allotted);exit;
            return json(msg($allotted['code'], $allotted['data'], $allotted['msg']));
        }
        $id = input();
        $data = BatchModel::returnDefaultBatchName($id["id"]);
        $this->assign("data", $data["batch_name"]);
        $mincard = CardModel::getBatchMinId($data["batch_name"]);
        $this->assign("id", $id["id"]);
        $this->assign("card_number", $mincard);
        return $this->fetch("batch_allocation");
    }
     /**
      * *************废弃
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
         $where = [];
        $where["is_del"] = 0 ;
        $batch = new BatchModel();
        $list = $batch->getCardByWhere($where, '0', '1000');
        $this->assign("list",$list);
        return $this->fetch("derive");
    }
     /**
     * 批量数据导出
     * @param $id
     * @return array
     */
    public function downLoadCardInfos()
    {
        set_time_limit(0);
        $data = input("batch");
        if (isset($data)) {
            //定义文件名
            $whereData = [
                "id" => array("in",$data),
                "is_del" => 0
            ];
            $query = db("batch")
                ->where($whereData)
                ->column("batch_name");
            
            $fileName = "会员卡" . "-" . date("Ymd")."(". implode("&", $query).")";
            //获取数据
            $queryData = [
                "batch" => array("in",$query),
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
    public function showCardAnalysis(){
        $this->assign("card_info", CardModel::getCardanalysis());
        return $this->fetch("analysis");
    }
    /**
     * 
     */
    public function reparemysql(){
        $sql = "CREATE TABLE IF NOT EXISTS `h5_batchlog` (
            `id`  int(8) NOT NULL AUTO_INCREMENT ,
            `batch_name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '批次名' ,
            `denomination`  int(8) NOT NULL COMMENT '面额' ,
            `card_head`  int(8) NOT NULL COMMENT '卡号标头' ,
            `card_count`  int(8) NOT NULL COMMENT '卡数量' ,
            `create_date`  datetime NOT NULL COMMENT '激活时间' ,
            `belonger`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '归属人ID' ,
            `belonger_name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '归属人名称' ,
            `is_del`  enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '0未删除 1已删除' ,
            `batch_id`  int(8) NOT NULL COMMENT 'batch批次id' ,
            PRIMARY KEY (`id`)
            )
            ENGINE=MyISAM
            DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
            COMMENT='批次发卡记录'
            AUTO_INCREMENT=52
            CHECKSUM=0
            ROW_FORMAT=DYNAMIC
            DELAY_KEY_WRITE=0;
            alter table h5_batch add COLUMN `batch_status`  enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1' COMMENT '1未分配 0已分配' ; 
            alter table h5_card add COLUMN `batch_id`  int(8) NULL DEFAULT 0 COMMENT '分配批次log id' ; 
            alter table h5_card add COLUMN `belonger`  int(8) NULL DEFAULT 0 COMMENT '所属者id' ; 
            ";
    }
    /**
     * 版本更新数据修复函数
     * 1、card表 batch_id belonger更新
     * 2、batchlog 记录增加  导入 转移 从batch 导入
     * 3、batch batch_status=0 card_count =0更新
     */
    public function repaire(){
        //echo BatchModel::getLastSql();
        //batchlog 写入日志
         $batch = new BatchModel();
          $where['belonger'] = [ 'neq' , '未分配'];
         $list = $batch->getCardByWhere($where,0,1000);
         foreach ($list as $key => $value) {
             $value = json_decode($value,1);
             $value['batch_id']=$value['id'];
             BatchLogModel::addBatchLog($value);
             echo BatchLogModel::getLastSql()."运行成功<br>";
         }
          //更新batch表
//         $uptData["batch_status"] = 0;
//         $uptData["card_count"] = 0;
//         $modify = BatchModel::where("belonger", "neq","未分配")->update($uptData);
//         echo BatchModel::getLastSql()."运行成功<br>";
        // card表 batch_id belonger更新
         $batchlog = new BatchLogModel();
         $where['belonger'] = [ 'neq' , '未分配'];
         $list = $batchlog->getCardByWhere($where,0,1000);
         foreach ($list as $key => $value) {
               $value = json_decode($value,1);
                $uptData= array(
                    'batch_id'=>$value['id'],
                    'belonger'=>$value['belonger'],
                );
                CardModel::where("batch", $value['batch_name'])->update($uptData);
                echo CardModel::getLastSql()."运行成功<br>";
         }
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
     * 版本更新数据修复函数
     * 1、card表 batch_id belonger更新
     * 2、batchlog 记录增加  导入 转移 从batch 导入
     * 3、batch batch_status=0 card_count =0更新
     */
    public function repaire1(){
          //更新batch表
         $uptData["batch_status"] = 0;
         $uptData["card_count"] = 0;
         $modify = BatchModel::where("belonger", "neq","未分配")->update($uptData);
         echo BatchModel::getLastSql()."运行成功<br>";
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
                'auth' => 'batch/batchallocation',
                'href' => url('batch/batchallocation', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'batch/peopledel',
                'href' => "javascript:peopleDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
        ];
    }
    /**
     * 拼装操作按钮 无分配按钮
     * 当可分配数量为0是不显示分配按钮
     * @param $id
     * @return array
     */
    private function makeButton_0($id)
    {
        return [
            '删除' => [
                'auth' => 'batch/peopledel',
                'href' => "javascript:peopleDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
        ];
    }
}
