<?php
namespace app\index\controller;

use app\admin\Model\NormalModel;
use app\admin\model\ProductModel;
use app\admin\model\ProducttitleModel;
use app\admin\model\TopimgModel;
use think\Controller;
use app\index\model\IndexModel;
use think\Log;

class Index extends Controller
{
    /**
     * 默认跳向后台管理页面
     *
     * author : FrankLee  dateTime : 2019-04-20 20:30
     */
    public function index()
    {
        // 默认跳转至后台管理
        $this->redirect('/admin');
    }

    public function autoCheck()
    {
        // 初始化 log 类
        Log::init([
            'type' => 'File',
            'path' => APP_PATH.'../public/log_/autocheck/',
        ]);


        // 初始化 Index类
        $index = new IndexModel();
        // 查询条件 prefer_num不等于0 （）
        $where = ['prefer_num'=>['<>',0]];

//        Log::write('this is eroor_log by Log', 'log');
        try{
            $normalIdArr = $index->checkProduct($where);
        }catch (\Exception $e){
            Log::record('查询相关信息失败：'.$e, 'log');
            return ;
        }

        // 拿到了所有有 url 的页面的 id 根据这些 id 重新生成静态页面。
        foreach ($normalIdArr as $key=>$vo ){
//            dump($vo);
            try{
                $this->createStaticPageById($vo['id']);
            }catch (\Exception $e){
                Log::record('生成失败：'.$e, 'log');
            }

        }

    }

    /**
     *
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * author : FrankLee  dateTime : 2019-04-23 14:50
     */
    protected function createStaticPageById($id)
    {
        $normal = new NormalModel();
        $topimg = new TopimgModel();
        $producttitle = new ProducttitleModel();
        $product = new ProductModel();

        // 获取页面的相关信息
        $normalInfo = $normal->getOneNormal($id);

        // 获取页面的顶图信息
        $where = ['normal_id' => $id];
        $topimgInfo = $topimg->getTopimgsByWhere($where, 0, 1);

        // 获取页面的商品
        $productInfo = $product->getProductByWhere($where, 0, 100);

        if (empty($topimgInfo) || is_null($topimgInfo)){
//            return json(msg(0,[],'请绑定顶图之后再生成'));
            Log::record($normalInfo['title'].':页面没有绑定顶图','log');
        }elseif (empty($productInfo) || is_null($productInfo)){
            Log::record($normalInfo['title'].':页面没有绑定商品','log');
//            return json(msg(0,[],'请绑定商品之后再生成'));
        }else{
            // 处理 product 相关信息
            $productArr = [];
            foreach ($productInfo as $key=>$vo){
                $productArr[] = $vo->toArray();
            }
            $productInfo = $this->maopao($productArr);

            // 获取页面的标题
            $producttitleInfo = $producttitle->getProducttitleByWhere($where, 0, 1);

            // 根据 sort_type 匹配 排序类型
            switch($normalInfo['sort_type']){
                // 2为单商品独占一行排序(每行只有一列), 1 和默认为一行两个商品(每行两列)。
                case 2:
                    $normalInfo['sort_type'] = 't_list';
                    break;
                case 1:
                    $normalInfo['sort_type'] = '';
                    break;
                default:
                    $normalInfo['sort_type'] = '';
            }
            $normalInfo['topimg'] = empty($topimgInfo) ? '' : $topimgInfo[0]['thumbnail'];
            $normalInfo['producttitle'] = empty($producttitleInfo) ? '' : $producttitleInfo[0]['thumbnail'];

            foreach($productInfo as $key=>$val){
                $val['prefer_num'] *=100;
                $val['prefer_num'] /=100;
            }
            $normalInfo['url'] = domain().'/normal/'.$id.'.html';

            // 绑定页面变量
            $this->assign([
                'normal' => $normalInfo,
                'product' => $productInfo
            ]);


            $string = $this->fetch(APP_PATH.'admin/view/normal/1.html');

            // 文件写入是否成功
            $fileName = APP_PATH.'../public/normal/'.$id.'.html';
            try{
                if(file_exists($fileName)){
                    // 如果文件存在 直接写入
                    file_put_contents($fileName, $string);
                }else{
                    // 文件不存在创建文件并写入
                    $handle = fopen($fileName, 'w');
                    fwrite($handle, $string);
                    fclose($handle);
                }
            }catch (\Exception $e){
                // 捕获异常信息
                Log::record($normalInfo['title'].'出现异常：'.$e, 'log');
            }


            $normalInfo['url'] = domain().'/normal/'.$id.'.html';
            $param['url'] = domain().'/normal/'.$id.'.html';
            $flag =  $normal->save($param,['id' => $id]);


            $this->assign([
                'normal'=>$normalInfo,
            ]);
            Log::record($normalInfo['title'].' 生成成功', 'log');
        }

    }

    /**
     * 根据 sort_id 排序
     * @param $arr
     * @return mixed
     *
     * author : FrankLee  dateTime : 2019-04-20 15:58
     */
    private function maopao($arr)
    {
        if(empty($arr) || is_null($arr)){
            return $arr;
        }else{
            $len = count($arr);
            for($i=1; $i<$len; $i++)//最多做n-1趟排序
            {
                $flag = false;    //本趟排序开始前，交换标志应为假
                for($j=$len-1;$j>=$i;$j--)
                {
                    if($arr[$j]['sort_id']<$arr[$j-1]['sort_id'])//交换记录
                    {//如果是从大到小的话，只要在这里的判断改成if($arr[$j]>$arr[$j-1])就可以了
                        $x=$arr[$j];
                        $arr[$j]=$arr[$j-1];
                        $arr[$j-1]=$x;
                        $flag = true;//发生了交换，故将交换标志置为真
                    }
                }
                if(! $flag)//本趟排序未发生交换，提前终止算法
                    return $arr;
            }
            return $arr;
        }
    }


}
