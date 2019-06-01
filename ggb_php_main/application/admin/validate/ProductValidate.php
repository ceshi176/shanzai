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
namespace app\admin\validate;

use think\Validate;

class ProductValidate extends Validate
{
    protected $rule = [
        ['title', 'require', '产品标题不能为空'],
        ['price', 'require', '原价不能为空'],
        ['prefer_price', 'require', '特惠价不能为空'],
        ['prefer_num', 'require', '优惠量不能为空'],

        ['url', 'require', '产品购买链接不能空'],
        ['mall_id', 'require', '所属商城不能空'],
        ['mall_goods_id', 'require', '所属商城的 ID 不能空'],
        ['normal_id', 'require', '所属页面不能空'],
        ['thumbnail', 'require', '缩略图不能空'],
    ];

}