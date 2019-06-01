<?php
namespace app\admin\model;

use think\Model;
use think\Db;
class GoodsitemizeModel extends Model
{
    // 确定链接表名
   protected $name = 'goods_itemize';

      public function additem($data)
      {
          $data['pid']=$data['newid'];

          $data['itemdate']=time();
          $ar=$this->save($data);
          return $ar;
      }
      public function listitem()
      {
          $show= Db::name('goods_itemize')->select();
          return $show;
      }

      public function typeshow()
      {
          $show= Db::name('goods_itemize')
              ->field('id,pid,name')
              ->select();
          return $show;
      }



}
