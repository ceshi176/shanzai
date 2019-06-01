<?php
// ||=================================================================>>>>
// |            PHP IS THE BEST LANGUAGE IN THE WORLD 
// |------------------------------------------------------------------>>>>
// |    User: FrankLee
// |------------------------------------------------------------------>>>>
// |    Date: 2019-04-20 Time: 20:34
// |------------------------------------------------------------------>>>>
// |                                   Author: FrankLee <frank227@163.com>
// ||=================================================================>>>>


//
namespace app\index\model;

use  app\admin\model\NormalModel;
use app\admin\model\ProductModel;
use app\admin\controller\Normal;
use think\Log;
use think\Model;

class IndexModel extends Model
{
    /**
     * 查询所有符合 $where 条件的 product
     * @param $where
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * author : FrankLee  dateTime : 2019-04-23 14:58
     */
    public function checkProduct($where)
    {
        // TODO 封装方法
        $productM = new ProductModel();
        $productArr = $productM->where($where)->select();
        if (!$productArr){
            return msg(0,'','数据库中没有符合要求的数据');
        }

        // 组装拼多多需要的 post 数据，
        $postArr = [
            'type' => config('pdd_api_goods_detail'),
            'client_id' => config('pdd_client_id'),
        ];

//        $result = getProductInfoFromPDD($postArr);
        $delArr = [];
        foreach ($productArr as $key=>$val){
            $postArr['goods_id_list'] = '['.$val['mall_goods_id'].']';
            $result = getProductInfoFromPDD($postArr);

            // 如果根据 ID 查到的信息是个数组 说明商品还在活动中 此时需要判断此商品的优惠券的剩余数量，如果为0 则删除商品
            // 如果
            if (is_array($result)){
                $result = $result['goods_detail_response']['goods_details'][0];
                if (0 == $result['coupon_remain_quantity']){
                    $delArr[] = $val['id'];
                }
            }
        }

        // 执行删除操作  删除所有不符合条件的商品$delArr中的 ID
        if (!empty($delArr) && is_null($delArr) && 0 != count($delArr) ){
            $delres = ProductModel::destroy($delArr);
            Log::record('本次删除了：'. $delres .' 条商品信息', 'log');
        }else{
            Log::record('本次查询所有商品均符合上架条件，未删除商品', 'log');
        }

        // 重新生成静态页面。
        // 通过 url 存在与否判断页面是否已经生成，如已生成，重新生成，如未生成 不做处理。
        $normalM = new NormalModel();
        // 查询所有已经生成了 url 的页面信息。
        $where = ['url'=>['<>','null']];
        $normalArr = $normalM->where($where)->select();


        return $normalArr;
        // 准备日志需要的数据
        // 任务执行前 ** 个商品 优惠券数量为零删除 ** 个商品 剩余 ** 个商品


    }


}