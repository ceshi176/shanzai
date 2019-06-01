<?php
/**
 * Created by PhpStorm.
 * User: ALIENWARE
 * Date: 2019/3/28
 * Time: 16:35
 */
namespace app\api\validate;

use think\Validate;

class CardDataValidate extends Validate
{
    protected $rule = [
        ['cardNumber', 'require', '卡账号不能为空'],
        ['cardSecret', 'require', '卡密码不能为空'],
        ['UnionId', 'require', 'UnionId不能为空'],
        ['NickName', 'require', 'NickName不能为空'],
        ['HeadImgurl', 'require', 'HeadImgurl不能为空'],
        ['Openid', 'require', 'Openid不能为空'],
    ];

}