<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {
    protected function json($code="200",$message="",$info="") {
        $data['code'] = $code;
        $data['message'] = $message;
        if($info || is_array($info) ){
            $data['data'] = $info;
        }else{
            $data['data'] = $info;
        }
        $this->ajaxReturn($data);
    }
	//系统首页
    public function index(){
        $prize  = M("prize");
        $data  = $prize->select();
        foreach($data as $k=>$v){
            $str=M("prize_category")->where(array('category_level'=>$data[$k]['level']))->find();
            $data[$k]['prize']=$str['category_name'];
        }
        if(!empty($data)) {
            $this->json(0, '查询成功', $data);
        }else{
            $this->json('1','暂无数据','');
        }
    }
    //中奖页面
       public function number(){
        $prize  = M("prize_number");
        $data  = $prize->select();
        if(!empty($data)) {
            $this->json('0', '查询成功', $data);
        }else{
            $this->json('1','暂无数据','');
        }
    }
   //中奖记录
    public function record(){
        parent::__construct();
        $prize  = M("prize_record");
        $where['status']=array('neq','0');
        $data  = $prize->where($where)->select();
        if(!empty($data)) {
            $this->json('0', '查询成功', $data);
        }else{
            $this->json('1','暂无数据','');
        }
    }
   //中奖规则
    public function rule(){
        parent::__construct();
        $prize  = M("prize_rule");
        $data  = $prize->select();
        if(!empty($data)) {
            $this->json('0', '查询成功', $data);
        }else{
            $this->json('1','暂无数据','');
        }
    }
   //奖品分类页面
    public function category(){
        parent::__construct();
        $prize  = M("prize_category");
        $data  = $prize->select();
        if(!empty($data)) {
            $this->json('0', '查询成功', $data);
        }else{
            $this->json('1','暂无数据','');
        }
    }
    public function getRand($priarr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($priarr);
        //概率数组循环
        foreach ($priarr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($priarr);
        return  $result;
    }
    public function luckDraw(){
        $result = '';
        $list_where['left_num']=array('neq','0');
        $list=M("prize")->where($list_where)->field('level')->select();
        if(empty($list)) {
            $prize_category=M("prize_category")->where(array('category_name'=>"谢谢惠顾"))->find();
            $ss_where['level']=array('eq',$prize_category['category_level']);
        }else{
            $ids = array_column($list, 'level');
            $ss_where['level'] = array('in', $ids);
        }
        $probability = M('prize_probability');
        $proArr = $probability->where($ss_where)->field('level,num')->select();
        $arr = array();
        foreach($proArr as $k => $v){
            $arr[$v['level']] = $v['num'];
        }
        //概率数组的总概率精度
        $proSum = array_sum($arr); //计算数组中元素的和
        //概率数组循环
        foreach ($arr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) { //如果这个随机数小于等于数组中的一个元素，则返回数组的下标
                $number = $key;   //中奖等级 123...
                //此时生成中奖码 规则是 当前时间戳 + 随机四位
                unset ($arr);
                $rand = rand(1000,9999);  //
                $red = time();
                $redd = $rand .$red;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        $level = getlevel($number);
        $prizeRecord = M('prize_record');
        $data['user_id'] = 1;
        $data['user_name'] = '小明';
        $data['number'] = $redd;
        $data['type'] = $number;
        if($level == '谢谢参与' || $level == '谢谢惠顾'){
            $data['status'] = 0;
        }else{
            $data['status'] = 1;
        }
//        $data['draw'] = 1;  //setInc
        $data['open_time'] = date('Y-m-d H:i:s',time());;
        $data['create_time'] = date('Y-m-d H:i:s',time());;
        $data['prize_name'] = $level;
        $result = $prizeRecord ->add($data);
        if($result == false){
//            die(json_encode(array('code'=>500,'msg'=>"抽奖失败！",'result'=>null)));
            dump(json_encode(array('code'=>500,'msg'=>"抽奖失败！")));
        }
        else{
           if($prizeRecord->where('user_id=1')->setInc('draw',1) === false){
               dump(json_encode(array('code'=>500,'msg'=>"抽奖失败！")));
           }else{
                $shop=M("prize")->where(array('level'=>$number))->find();
                if(!empty($shop)){
                    if(!empty($shop['left_num'])) {
                        $shop_data['left_num'] = $shop['left_num'] - 1;
                        M("prize")->where(array('level' => $number))->save($shop_data);
                    }
                }else{
                    dump(json_encode(array('code'=>500,'msg'=>"抽奖失败！")));
                }
               $prize = M('prize_number');
               unset($data);
               $data['number'] = $redd;
               $data['type'] = $number;
               if($level == '谢谢参与' || $level == '谢谢惠顾'){
                   $data['status'] = 0;
               }else{
                   $data['status'] = 1;
               }
               $data['open_time'] = date('Y-m-d H:i:s',time());;
               $data['create_time'] = date('Y-m-d H:i:s',time());;
               $result = $prize ->add($data);
               if($level == '谢谢参与' || $level == '谢谢惠顾'){
                   die(json_encode(array('code' => 200,'msg' => '抽奖成功','result'=> array('type' => $number,'level' => $level,'number' => $redd))));
               }else{
                   die(json_encode(array('code' => 0,'msg' => '抽奖成功','result'=> array('type' => $number,'level' => $level,'number' => $redd))));
               }
           }
       }
    }
}