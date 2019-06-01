<?php
/**
 * Created by PhpStorm.
 * User: ALIENWARE
 * Date: 2019/3/23
 * Time: 17:24
 */
namespace app\api\model;

use think\Model;
use app\api\model\BatchModel as BatchModel;
use think\Log;

class CardModel extends Model
{
    public static $cardNumberData;

    protected $name = "card";

//    static public function

    /**
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function validateCard($param)
    {
        //检查卡号或密码为空情况
         if(empty($param['CardSecret']) || empty($param['CardSecret'])){
               return reJson(0,'点击页面错误，请重新扫描卡片地址重新输入卡号和密码','');
        }
        $CarModel = new CardModel();
        //判断这个卡号密码是否正确
        $corrtWhere["card_number"] = $param['cardNumber'];
        $corrtWhere["card_secret"] = $param['cardSecret'];
        //增加未分配数据限制
        //$corrtWhere["belonger"] = array(">","0");
        $cardInfo = $CarModel::where($corrtWhere)->field('id,card_number,card_status,belonger')->find();
        if ($cardInfo == null){
            return reJson(0,'卡号或密码错误，请核实','');
        }else{
            //增加未分配数据限制
            if($cardInfo['belonger']=='0'){
                    return reJson(0,'此卡未激活，请联系客服','');
            }
            $queryBelonger = self::alias("c")
                    ->where("card_number", $param['cardNumber'])
                    ->join("batch b", "c.batch = b.batch_name")
                    ->field("c.belonger")
                    ->find();
            $cardInfo['userId'] = $queryBelonger['belonger'];
            $flag = $cardInfo['card_status'] == 0 ? reJson(1,'卡正常',$cardInfo) : reJson(2, '卡已使用',[]);
            return $flag;
        }
    }

    /**
     *
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * author : FrankLee  dateTime : 2019-04-18 15:51
     */
    static public function registerPerson($param)
    {
        $cardNumber = $param['CardNumber'];
        // 查询卡对应的面额
        $queryMoney = self::alias("c")
                    ->where("card_number", $cardNumber)
                    ->join("batch b", "c.batch = b.batch_name")
                    ->field("denomination, c.belonger")
                    ->find();

        // 组装 需要发送的信息
        // {"Phone":"",
        //  "Code":"",
        //  "GiveQuota":"",
        //  "TuiJianId":""
        //}
        $sendData =[
            'Phone'=>$param['Phone'],
            'Code' => $param['Code'],
            'UnionId' => $param['UnionId'],
            'OpenId' => $param['openId'],
            'NickName' => !empty($param['nickname']) ?$param['nickname']:"赶谷榜会员",
            'Headimg' => $param['headimgurl'],
            'GiveQuota'=>$queryMoney['denomination'],
            'TuiJianId' => $queryMoney['belonger']
        ];
         Log::init([
            'type' => 'File',
            'path' => APP_PATH.'../runtime/logs/',
        ]);
         Log::record('给逍遥发送data'.date("y-m-d h:i:s").var_export($sendData,true));
        // 测试接口
//       $returnMsg = curlPost(" http://xiaoyao.gangubang.cn/api/UserBind/MemberCardRegisterByPhone");
 //      $returnMsg = curlPost("http://xiaoyao.gangubang.cn/api/UserBind/MemberCardRegisterByPhoneAndUnionId",$sendData);
         //$returnMsg = curlPost(config('api_domain').'/api/UserBind/MemberCardRegisterByPhone',$sendData);
          //$returnMsg = curlPost(config('api_domain').'/api/UserBind/MemberCardRegisterByUnionId',$sendData);
          $returnMsg = curlPost(config('api').'/api/UserBind/MemberCardRegisterByPhoneAndUnionId',$sendData);
          Log::record('接收逍遥data'.date("y-m-d h:i:s").var_export($returnMsg,true));
        $return = (array)json_decode($returnMsg,true);
        if (is_null($return) || empty($return)){
            return reJson('10',$returnMsg,[]);
        }
        if ($return['Success'] == 'true'){
            // 成功 修改卡状态
            if($return['IsGive'] == true){
                $card = self::where(['card_number'=>$cardNumber])
                ->update([
                    'card_status'=> 1,
                    'cell_phone' => $param['Phone'],
                    'user_unionid' => $param['UnionId'],
                    'employ' => $return['Data']['UserId'],
                    'employ_time' => date("Y-m-d H:i:s"),
                ]);
                  // 数据修改是否成功
                    if ($card == 1){
                        return reJson(1,'成功',[]);
                    }else{
                        return reJson(0, '失败，请重新尝试一次',[]);
                    }
            }else{
                return reJson(1004, '您已领取过现金券',[]);
            }
          

        }else{
            return reJson($return['Code'], $return['Mess'],$return);
        }

    }


    /**
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function registerValidateCard($param)
    {
        $CarModel = new CardModel();
        //判断这个卡号密码是否正确
        $corrtWhere = [];
        $corrtWhere["card_number"] = $param['CardNumber'];
        $corrtWhere["card_secret"] = $param['CardSecret'];
        if(empty($param['CardNumber']) || empty($param['CardSecret'])){
               return reJson(0,'点击页面错误，请重新扫描卡片地址重新输入卡号和密码','');
        }
        if(empty($param['UnionId']) || empty($param['openId'] )|| empty($param['headimgurl']) || empty($param['nickname'])){
               return reJson(0,'微信授权信息错误，请关闭当前页面重新扫描卡片二维码进行操作','');
        }
        //增加未分配数据限制
        //$corrtWhere["belonger"] = array(">","0");
        $cardInfo = $CarModel::where($corrtWhere)->field('id,card_number,card_status,belonger')->find();
        if ($cardInfo == null){
            return reJson(0,'卡号或密码错误，请核实','');
        }else{
            //增加未分配数据限制
            if($cardInfo['belonger']=='0'){
                    return reJson(0,'此卡未激活，请联系客服','');
            }
            //验证手机号是否在会员卡时是否注册过
             //增加未分配数据限制
           $corrtWhere = [];
           $corrtWhere["cell_phone"] = $param['Phone'];
            $cardInfom = $CarModel::where($corrtWhere)->field('id,card_number,card_status')->find();
            if ($cardInfom != null){
                return reJson(1004,'您已参加过此次活动','');
            }
            $corrtWhere = [];
            //验证UnionId是否注册过
          $corrtWhere["user_unionid"] = $param['UnionId'];
          $cardInfou = $CarModel::where($corrtWhere)->field('id')->find();
          if ($cardInfou != null){
              return reJson(1004,'您已参加过此次活动','');
          }
//            $queryBelonger = self::alias("c")
//                ->where("card_number", $param['CardNumber'])
//                ->join("batch b", "c.batch = b.batch_name")
//                ->field("belonger")
//                ->find();
//            $cardInfo['userId'] = $queryBelonger['belonger'];
            $flag = $cardInfo['card_status'] == 0 ? reJson(1,'卡正常',$cardInfo) : reJson(2, '卡已使用',[]);
            return $flag;
        }
    }


}