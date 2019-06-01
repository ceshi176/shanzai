<?php
// +------------------------------------------------------------------>>>>
// |            PHP IS THE BEST LANGUAGE IN THE WORLD 
// +------------------------------------------------------------------>>>>
// |    User: FrankLee
// +------------------------------------------------------------------>>>>
// |    Date: 2019/3/31 Time: 1:48 AM
// +------------------------------------------------------------------>>>>
// |                                   Author: FrankLee <frank227@163.com>
// +------------------------------------------------------------------>>>>


//
return [
    // 应用状态
    'app_status' => 'dev',
    //各模块公用配置
    'extra_config_list' => ['database', 'route', 'validate'],
    // 是否开启路由
    'url_route_on' => true,
    //加密串
    'salt' => 'wZPb~yxvA!ir38&Z',
    //备份数据地址
    'back_path' => APP_PATH .'../back/',

    // 拼多多的 client_id
    'pdd_client_id' =>'742d5f14ee05487db2901ee7b2290ee7',
    // 拼多多的 client_secret
    'pdd_client_secret' => 'c43da9fd7423caf0b6eee589196956b1c51b8517',
    // 拼多多总接口
    'pdd_api' => 'http://gw-api.pinduoduo.com/api/router',
    // 拼多多接口类型（查询商品详情仅支持单个查询）
    'pdd_api_goods_detail' => 'pdd.ddk.goods.detail',

];
