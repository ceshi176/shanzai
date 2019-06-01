<?php
namespace app\admin\controller;

use app\admin\model\RoleModel;
use app\admin\model\UserModel;
use app\admin\model\TestModel;
use think\Controller;
use think\Request;
use org\Verify;
class Redd extends Controller
{
	public function bb(){
		$model= new TestModel();
        $arr=$model->ff();
        return json($arr);
	}
    public function ker(){
        $model= new TestModel();
        $arr=input('post.');
        $str=$model->addd($arr);
        return json($str);
    }
    public function zh(){
        $model= new TestModel();
        $arr=$model->ff();
        return $this->fetch('zh',['data'=>$arr]);
    }
    public function del(){
        $model= new TestModel();
        $id=Request::instance()->param('id');
//        var_dump($id);die;
        $str=$model->dele($id);
        return $str;
//        if($str){
//            $this->success('删除成功','redd/zh');
//        }else{
//            $this->success('删除失败','redd/zh');
//        }
    }
    public function upde(){
        $model= new TestModel();
        $id=Request::instance()->param('id');
        $arr=$model->fff($id);
        return $this->fetch('upd',['data'=>$arr]);
    }
    public function upl(){
        $model= new TestModel();
        $id=Request::instance()->param('id');
        $data=input('post.');
        $arr=$model->updat($id,$data);
        if($arr){
            $this->success('修改成功','redd/zh');
        }else{
            $this->success('修改失败','redd/zh');
        }
    }
    public function souu(){
        $model= new TestModel();
        $data=Request::instance()->param("sou");
        $arr=$model->ss($data);
        return $this->fetch('zh',['data'=>$arr]);
    }
}