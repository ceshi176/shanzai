<?php
/**
 * Created by PhpStorm.
 * User: lidebiao
 * Date: 2018/4/16 0016
 * Time: 9:40
 */
namespace app\admin\model;
use think\Db;
use think\Model;
class TestModel extends Model
{

    protected $name = 'user';
    public function ff()
    {
//        $res = Db::name('user')->field('user_name,login_times')->find();
//        return $res;
//        return $this->field('user_name,real_name')->find();
        return $this->alias('user')->field( 'user.*,role_name')
                    ->join('role rol', 'user.role_id = ' . 'rol.id')
                    ->order('id desc')->select();
    }

    public function addd($arr)
    {
//        $res = Db::name('user')->insert($arr);
           return $this->save();
    }
    public function dele($id)
    {
//        $res = Db::table('user')->where('id', $id)->delete();
//        return $res;
          $this->where('id',$id)->delete();
          return msg('1','','删除成功');
    }
    public function fff($id)
    {
        $res = Db::table('user')->where('id', $id)->find();
        return $res;
    }
    public function updat($id,$data)
    {
        $res = Db::table('user')->where('id', $id)->update($data);
        return $res;
    }
    public function ss($data)
    {
        $res = Db::table('user')->where('str','like',"%$data%")->paginate(3,false,['query'=>request()->param()]);
        return $res;
    }
}