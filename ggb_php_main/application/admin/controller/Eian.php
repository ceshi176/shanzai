<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Validate;
use app\index\model\Test;
class Eian extends Controller
{
	public function data(){
		 $arr = Db::table('lian')->select();
         return $this->fetch('zh',['data'=>$arr]);

	}
	public function zhu(){
		return $this->fetch('zhuce');
	}
	public function deng(){
		return $this->fetch('den');
	}
	public function zhuce(){
		$arr=input('post.');
		$data = Db::table('lian')->insert($arr);
		if($data){
			 $this->success('注册成功','eian/deng');
		}else{
			 $this->success('注册失败','eian/zhu');
		}
	}
	public function den(){
	    $data = input('post.');
        $user=Db::name('lian')->where('name','=',$data['name'])->find();
        if ($user) {
        	 if($user['pwd'] == $data['pwd']){
               session('name',$user['name']);
               $this->success('登录成功','eian/data');         
    }else{
              $this->success('密码错误','eian/deng');
    }

   }else{
   	          $this->success('用户名不存在','eian/deng');
   }
 }
   public function delete(){
   	    $request = Request::instance();        
        $id = $request->get('id');//接收id
        $res= Db::name('lian')->where('id','=',$id)->delete();
        if($res){
         echo 1;
      }else{
         echo 2;
        }

  }
  //查询接口
   public function json()
   {
   	   $data = Db::name('lian')->select();
   	   if($data)
   	   {
   	   	 json('成功',$data);
   	   }
   	   else{
   	   	json('失败',0);
   	   }

   }
  //删除接口
  public function dele(){
  	 $id = 28;
  	 $data['id'] = $id;
  	 $res = Db::name('lian')->where('id', $id)->delete($data);
  	 if($res){
  	 	json('成功',1);
  	 }
  	 else{
  	 	json('失败',0);
  	 }
  }
  public function upde(){
      $id=Request::instance()->param('id');
      $res = Db::table('lian')->where('id', $id)->find();
      return $this->fetch('upd',['data'=>$res]);
  }
  public function upl(){
       $id=Request::instance()->param('id');
       $data=input('post.');
       $res = Db::table('lian')->where('id', $id)->update($data);
        if($res){
            $this->success('修改成功','eian/data');
        }else{
            $this->success('修改失败','eian/data');
        }
  }
  public function souu(){
        $data=Request::instance()->param("sou");
        $res = Db::table('lian')->where('name','like',"%$data%")->paginate(3,false,['query'=>request()->param()]);
        return $this->fetch('zh',['data'=>$res]);
    }
}
