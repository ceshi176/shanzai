<?php
/**
 * Created by PhpStorm.
 * User: ALIENWARE
 * Date: 2019/3/23
 * Time: 16:03
 */

namespace app\api\controller;

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Credentials:false');

use app\api\model\CardModel;
use think\Config;
use think\Controller;
use think\Log;
class Card extends Controller
{

    /**
     * @return array
     */
    public function cardValidate()
    {
        // 设置返回格式为 json
        Config::set('default_return_type','json');

        // 判断是否是 post 请求
        if (request()->isPost()){
//                dump(input('?post.'));
            $param = (array)json_decode(file_get_contents('php://input','r'));
//            dump($param);
            // 是否有 post 数据
            if (is_null($param) || empty($param)){
                $return = reJson(-1, '卡号或卡密不能为空',[]);
            }else{
                // 验证卡号、卡密的合法性
                $return = CardModel::validateCard($param);
            }
            return $return;
        }
    }

    public function registerMember()
    {
        // 设置返回信息的格式为 json
        Config::set('default_return_type','json');

        //
        if (request()->isPost()){
            $param = (array) json_decode(file_get_contents('php://input','r'));
            // 数据格式 Phone (手机号)，Code （验证码）, UserId（推荐人的 ID）, CardNumber(卡号)
//            $moke  = array(
//                'Phone' => '13611052971',
//                'Code' => '1231',
//                'UserId' => '8211',
//                'CardNumber' => '20001001',
//                'CardSecret' => '2012',
//            );
            Log::init([
                'type' => 'File',
                'path' => APP_PATH.'../runtime/logs/',
            ]);
              Log::record('接收数据1'.APP_PATH.date("y-m-d h:i:s").var_export($param,true));
            $checkData = CardModel::registerValidateCard($param);
            if ($checkData['code'] == 1){
                $return = CardModel::registerPerson($param);
                  Log::write('返回数据1code=1\n'.date("y-m-d h:i:s").var_export($return,true));
                return $return;
            }else{
                  Log::write('返回数据1code=0\n'.date("y-m-d h:i:s").var_export($checkData,true));
                return $checkData;
            }
        }
    }


    /**
     * 进行数据验证
     */
//    public function dataValidate()
//    {
//        $data = json_decode(file_get_contents("php://input","r"));
//        $data = (array) $data;
//        $result = $this->validate($data,'CardValidate');
//        if(true !== $result){
//            // 验证失败 输出错误信息
//            return json_encode(["msg" => $result, "code" => 400], JSON_UNESCAPED_UNICODE);
//        }
//        //开始验证数据
//        $validateData = CardModel::validateCard($data);
//        return $validateData;
//    }

    /**
     * 进行激活注册接口
     */
//    public function cardActivate()
//    {
//        echo "is this";
//        if (request()->isPost()) {
//            //接收数据
//            $data = json_decode(file_get_contents("php://input","r"));
//            $data = (array) $data;
//            $result = $this->validate($data,'CardDataValidate');
//            if(true !== $result){
//                // 验证失败 输出错误信息
//                return json_encode(["success" => false,"data" => ["code" => 400,"msg" => $result]], JSON_UNESCAPED_UNICODE);
//            }
//            //验证卡的正确性接口
//            $validateCard = CardModel::validateDataCard($data);
//            if ($validateCard["data"]["code"] != 200) return json_encode($validateCard);
//            //进行激活接口
//            $actiavate = CardModel::activateCard($data);
//            //返回结果
//            return $actiavate;
//        }
//    }



    /**
     * 进行绑定接口
     */
    public function cardBinding()
    {
        if (request()->isPost()) {
            //接收前端传过来的数据
            $getData = input("post.");
            $result = $this->validate($getData,'CardValidate');
            if(true !== $result){
                // 验证失败 输出错误信息
                return json_encode(["success" => false,"data" => ["code" => 400,"msg" => $result]], JSON_UNESCAPED_UNICODE);
            }
            //调用模型进行绑定
            $excuteBind = CardModel::userBindCard($getData);
            //返回信息
            return $excuteBind;
        }
    }
}