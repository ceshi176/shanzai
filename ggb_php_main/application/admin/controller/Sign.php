<?php
namespace app\index\controller;

use app\index\model\UserPraiseModel;
use think\Controller;
use think\Db;
use think\Request;
use app\index\model\Test;
use think\Verify;

class Sign extends Api
{
    /***
     * 李德标
     * 登录
     */
    public function log()
    {

        input('mobile_phone') ? $mobile_phone = input('mobile_phone') : $mobile_phone = 0;//手机号
        input('open_id') ? $open_id = input('open_id') : json('1', '请输入open_id');//open_id

        if ($mobile_phone == 0) {
            $token = setToken();
            $num = Db::name('user')->where("open_id", $open_id)->count();
            Db::name('user')->where("open_id", $open_id)->update(array('token' => $token));
            if ($num > 0) {
                json('0', '成功', $token);
            } else {
                json('1', '失败', '请输入手机号绑定账号');
            }

        } else {

            input('content') ? $verifiy = input('content') : json('1', '请填写内容');//内容
            $verify = new Verify();
            if (!$verify->check($verifiy, "admin_login")) {
                json(1005, '验证码错误');
            }
            if (isMobile($mobile_phone) == false) json('1', '请输入正确的手机号');

            $num = Db::name('user')->where("open_id", $open_id)->count();
            if ($num > 0) json('1', '您已经是用户,请不要重新绑定');
            $mobile_phone1 = Db::name('user')->where("mobile_phone=$mobile_phone")->find();
            if (!$mobile_phone1) {
                json('1', '手机号没有匹配的用户,无法绑定');
            } else {
                $token = setToken();
                if ($mobile_phone1['open_id']) json('1', '用户已经绑定');
                $user_res = Db::name('user')->where("mobile_phone=$mobile_phone")->update(array('open_id' => $open_id, 'token' => $token));
                if ($user_res) {
                    $openid_bd = Db::name('user_integral')->insert(array('w_open_id'=>$open_id));
                    if ($openid_bd){
                        json('0', '绑定成功', $token);
                    }else{
                        json("1","用户钱包创建失败");
                    }

                } else {
                    json('1', '绑定失败');
                }
            }
        }
    }

    /***
     * 李德标
     * 管理员
     */
    public function admin()
    {
        $token = input('token');
        $user = Db::name('user')->where((['token' => $token]))->field('permission')->find();
        if ($user['permission'] == 1) {
            $date = Db::name('region')->select();
            json('0', '管理员', $date);
        } else {
            json('2', '普通用户');
        }
    }

    /***
     * 李德标
     * 首页-轮播图
     */
    public function home()
    {

        $user = $this->loginAdmin(input('token'));
//      判断今天是否签到
        $today_ymd = date("Y-m-d", time());
        $todaydata = Db::name('sign_in')->where("from_unixtime(sign_time,'%Y-%m-%d')='{$today_ymd}' and user_id={$user['user_id']}")->find();

        $data = Db::name('top_image')->where("region={$user['region_id']}")->field('img_url')->limit(3)->select();
        if ($data){
            foreach ($data as $key=>$value){
                if ($todaydata) {
                    $data[$key]['sign'] = 1;//已签到
                }else{
                    $data[$key]['sign'] = 2;//未签到
                }
            }
            json('0', '查询成功', $data);
        }else{
            json('1', '暂无数据', '');
        }
    }

    /*
     * 精品课程
     *
     * 地址:/index.php/index/Sign/cour
     *
     * 参数:
     *
     * token -- 如果是管理员传入 region_id
     *
     * */

    public function cour()
    {
        $token = input('token','');
        if (empty($token)){
            json("1","参数token错误");
        }
        $user = $this->loginAdmin($token);
        $data = Db::name('course')->where("region_id={$user['region_id']} and type=1")->field('id,title,title_image,region_id')->limit(12)->select();
        if (!empty($data)) {
            json('0', '查询成功', $data);
        } else {
            json('1', '暂无数据', '');
        }
    }

    /*
     * 权威专家
     *
     * 地址: /index.php/index/Sign/user
     *
     * token -- token
     *
     * region_id -- 当前登录如果是管理员传入
     *
     *
     * */
    public function user()
    {
        $token = input('token','');
        if (empty($token)){
            json("1","token 参数错误");
        }
        $user = $this->loginAdmin($token);
        $data = Db::name('user')->where("permission=1 and status=1 and (job_desc=1 or job_desc=2) and region_id={$user['region_id']}")->field('user_id,user_icon,user_nick_name,introduction')->limit(12)->select();
        if (!empty($data)) {
            json('0', '查询成功', $data);
        } else {
            json('1', '暂无数据');
        }
    }
    /***
     * 李德标
     * 接口-open_id
     */
    public function token()
    {
        $w_open_id = 123;
        $data['w_open_id'] = $w_open_id;
        $str = Db::name('user_integral')->insert($data);
    }

    /*
     * 热门问答
     *
     * 地址: /index.php/index/Sign/hot
     *
     * 参数:
     *
     * token -- token
     *
     * region_id -- 当前登录如果是管理员传入
     * */
    public function hot()
    {
        $token = input('token','');
        if (empty($token)){
            json("1","token参数错误");
        }
        $user = $this->loginAdmin($token);
//        1.查出热门问题 2.查出相对应的回答 3.查出专家信息
        $data = Db::name('my_question')
            ->where("region_id={$user['region_id']} and is_answer=1")
            ->order("praise_num desc")
            ->field("id,user_id,title,content,user_icon,user_name,comment_num,praise_num")
            ->limit(2)
            ->select();
        if ($data){
            $userpraise = new UserPraiseModel();
            foreach($data as $key=>$value){
                $zan = $userpraise->inquire(1,$value['id'],$user['user_id']);
                if ($zan>0){
                    $data[$key]['zan'] = 1;//已经点赞

                }else{
                    $data[$key]['zan'] = 2;//未点赞
                }
            }

        }else{
            json('1', '暂无数据', '');
        }

            json('0', '查询成功', $data);
        
    }

    /*
     * 热门分享
     *
     * 地址:  /index.php/index/Sign/share
     *
     * 参数:
     *
     * token -- token
     *
     * region_id -- 当前登录如果是管理员传入
     * */
    public function share()
    {
//         热门分享（user_skill表），总个数3个，根据评论热度（top3)（根据权限推荐）

        $token = input('token','');
        if (empty($token)){
            json("1","token参数错误");
        }
        $user = $this->loginAdmin($token);
        $data = Db::name('user_skill')->where("region_id={$user['region_id']}")->field("id,user_id,user_nick_name,user_icon,desc_type,title,description,desc_url,comment_num,praise_num")->order("comment_num desc")->limit(3)->select();
        if ($data){
            $userpraise = new UserPraiseModel();
            foreach ($data as $key=>$value){
                $zan = $userpraise->inquire(3,$value['id'],$user['user_id']);
                if ($zan>0){
                    $data[$key]['zan'] = 1;//已经点赞

                }else{
                    $data[$key]['zan'] = 2;//未点赞
                }

                $desc = explode(',',$value['desc_url']);
                if($desc){
                    $data[$key]['desc_url'] = $desc;
                }else{
                    $data[$key]['desc_url'] = $value['desc_url'];
                }
//                是否收藏状态判断
                $shoucang = $this->Collection($value['id'],3,$token);
                if ($shoucang){
                    $data[$key]['shoucang'] = 1;//已收藏
                }else{
                    $data[$key]['shoucang'] = 2;//未收藏
                }
            }
        }else{
            json('1', '暂无数据', '');
        }

            json('0', '查询成功', $data);
    }
}