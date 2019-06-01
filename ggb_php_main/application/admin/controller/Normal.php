<?php
namespace app\admin\controller;

use app\admin\model\NormalModel;
use app\admin\model\ProducttitleModel;
use app\admin\model\TopimgModel;
use app\admin\model\ProductModel;

class Normal extends Base {

    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
//            $limit = 1000;
//            $offset = 0;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $normal = new NormalModel();
            $selectResult = $normal->getNormalByWhere($where, $offset, $limit);

            // 查询所有的顶图信息
            $topImg = new TopimgModel();
            $topImgArr = $topImg->getTopimgsByWhere([],0,1000);
            $topImgNormalIdArr = [];
            foreach ($topImgArr as $vo){
                $topImgNormalIdArr[] = $vo['normal_id'];
            }
//            $productNums = $normal->productNumInNormal();

            foreach($selectResult as $key=>$vo){
                // 获取获取器修改的值
                $selectResult[$key]->append(['productnum']);
                $vo['find'] = $vo['find'] == 1 ? '是发现页' : '不是发现页';
                $vo['sort_type'] = $vo['sort_type'] == 1 ? '两列每行' : '单列每行';
                $selectResult[$key]['topimg'] = in_array($selectResult[$key]['id'], $topImgNormalIdArr) ? '有' : '<span style="color: red">无</span>';
                // 添加按钮信息
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] = $normal->getAllNormal($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    public function normalAdd()
    {
        if(request()->isPost()){
            $param = input('post.');

            unset($param['file']);
             $param['add_time'] = date('Y-m-d H:i:s');

            $normal = new NormalModel();
            $flag = $normal->addNormal($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        return $this->fetch();
    }

    public function normalEdit()
    {
        $normal = new NormalModel();
        if(request()->isPost()){
//        dump(input('param.'));
            $param = input('param.');
//            unset($param['file']);
            $flag = $normal->editNormal($param);

            return msg($flag['code'], $flag['data'], $flag['msg']);
        }

        $id = input('param.id');
        $this->assign([
            'normal' => $normal->getOneNormal($id)
        ]);

        return $this->fetch();
    }

    public function normalForApi()
    {
        $normalModel = new NormalModel();
        $normalInfo = $normalModel->getNormalForApi();
        dump($normalInfo);
    }


    /**
     * @return \think\response\Json
     */
    public function normalDel()
    {
        $id = input('param.id');

        $normal = new NormalModel();
        $flag = $normal->delNormal($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /** 根据页面的 ID 查找相关信息:
     *  1. 归属于当前页面的顶图
     *  2. 归属于当前页面的标题
     *  3. 归属于当前页面的商品
     */
    public function normalToStatic()
    {
        $id = input('param.id');


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
            return json(msg(0,[],'请绑定顶图之后再生成'));
        }elseif (empty($productInfo) || is_null($productInfo)){
            return json(msg(0,[],'请绑定商品之后再生成'));
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
                return json(msg(1,'', $e->getMessage()));
            }


            $normalInfo['url'] = domain().'/normal/'.$id.'.html';
            $param['url'] = domain().'/normal/'.$id.'.html';
            $flag =  $normal->save($param,['id' => $id]);


            $this->assign([
                'normal'=>$normalInfo,
            ]);

            return json(msg(1, [],'生成成功'));
        }


    }


    public function upLoadImg()
    {
        if(request()->isAjax()){

            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            if($info){
                $src =  '/upload' . '/' . date('Ymd') . '/' . $info->getFilename();
                return json(msg(0, ['src' => $src], ''));
            }else{
                // 上传失败获取错误信息
                return json(msg(-1, '', $file->getError()));
            }
        }
    }

    private function makeButton($id)
    {
        return [
            '生成静态文件' => [
                'auth' => 'normal/normaltostatic',
                'href' => "javascript:normalToStatic(" . $id . ")",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-link'
            ],
            '编辑' => [
                'auth' => 'normal/normaledit',
                'href' => url('normal/normaledit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'normal/normaldel',
                'href' => "javascript:normalDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]

        ];
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