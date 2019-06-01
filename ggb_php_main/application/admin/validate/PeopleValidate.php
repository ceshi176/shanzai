<?php
/**
 * Created by PhpStorm.
 * User: ALIENWARE
 * Date: 2019/3/22
 * Time: 17:50
 */

namespace app\admin\validate;


use think\Validate;

class PeopleValidate extends Validate
{
    protected $rule = [
        ['business_name', 'require', '业务员姓名不能为空'],
        ['business_phone', 'require', '业务员手机号不能为空'],
    ];
}