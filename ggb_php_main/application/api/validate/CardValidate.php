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
namespace app\api\validate;

use think\Validate;

class CardValidate extends Validate
{
    protected $rule = [
        ['UserId', 'require', '用户ID不能为空'],
        ['Code', 'require', '短信验证码不能为空'],
        ["Phone", "require", '手机号不能为空']
    ];

}