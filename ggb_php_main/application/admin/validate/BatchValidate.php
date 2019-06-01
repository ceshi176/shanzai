<?php
/**
 * Created by PhpStorm.
 * User: ALIENWARE
 * Date: 2019/3/28
 * Time: 17:19
 */

namespace app\admin\validate;


use think\Validate;

class BatchValidate extends Validate
{
    protected $rule = [
        //"batch_name" => "require",
        "card_head" => "require|number|max:5",
        "denomination" => "require|positiveInt|gt:0|number",
        "card_count" => "require|positiveIntc|gt:0|number"
    ];

    protected $message = [
        //"batch_name.require" => "批次不能为空",
        "card_head.require" => "卡号标头不能为空",
        "card_head.number" => "卡号标头只能5位数字",
        "card_head.max" => "卡号标头长度为5位数字",
        "denomination.require" => "面额必须大于0",
        "denomination.number" => "面额只能是数字",
        "card_count.require" => "数量必须大于0",
        "card_count.number" => "数量只能是数字"
    ];
     protected function positiveInt($value, $rule='', $data)
    {
        $search = '/^[0][0-9]+$/';
        if(preg_match($search,$value)) {
             return "开卡面额不能以0开头";
       }
      if (is_int(($value+0))&&($value+0)>0) {
               return true;
      }else{
              return '卡面额必须为正整数';
      }
    }
    protected function positiveIntc($value, $rule='', $data)
    {
        $search = '/^[0][0-9]+$/';
        if(preg_match($search,$value)) {
              return"开卡数量不能以0开头";
       }
      if (is_int(($value+0))&&($value+0)>0) {
                 return true;
      }else{
                return '开卡数量必须为正整数';
      }
    }

}