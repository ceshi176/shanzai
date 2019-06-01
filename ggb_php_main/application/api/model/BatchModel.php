<?php
/**
 * Created by PhpStorm.
 * User: ALIENWARE
 * Date: 2019/3/25
 * Time: 10:25
 */

namespace app\api\model;


use think\Model;

class BatchModel extends Model
{
    protected $name = "batch";

    /**
     * 通过充值卡号查询归属人
     */
    static public function belongerPerson($belonger_id)
    {
        $query = self::where("user_id", $belonger_id)->find();
        return $query;
    }
}