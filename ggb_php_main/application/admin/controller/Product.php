<?php
namespace app\admin\controller;

use app\admin\model\ProductModel;
use app\admin\model\NormalModel;
use think\Config;

class Product extends Base
{
    // 商品列表
    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $product = new ProductModel();
            $selectResult = $product->getProductByWhere($where, $offset, $limit);

            foreach($selectResult as $key=>$vo){
                $vo['check'] = '<input id="del_id" name="del_id" type="checkbox" value="'.$vo['id'].'" />';
                $vo['title'] =  $this->setTitleHref($vo);
                $selectResult[$key]['mall_title'] = getMallNameById($selectResult[$key]['mall_id']);
                // $selectResult[$key]['normal_title'] = getNormalTitLeById($selectResult[$key]['normal_id']);
                $selectResult[$key]['thumbnail'] = '<img src="' . $vo['thumbnail'] . '" width="40px" height="40px">';
                $selectResult[$key]->append(['normal_title']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] = $product->getAllProduct($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    // 添加商品
    public function productAdd()
    {
        if(request()->isPost()){
            $param = input('post.');

            unset($param['file']);
            $param['add_time'] = date('Y-m-d H:i:s');

            $product = new ProductModel();
            $flag = $product->addProduct($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $this->assign([
            'normal'=> $this->getAllNormalInfo(),
            'mall' => Config::get('mall'),
        ]);

        return $this->fetch();
    }

    /**
     * tips : 编辑产品
     * @return mixed|\think\response\Json
     *
     * author : FrankLee  dateTime : 2019-04-11 14:04
     */
    public function productEdit()
    {
        $product = new ProductModel();
        if(request()->isPost()){

            $param = input('post.');
            unset($param['file']);
            $flag = $product->editProduct($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'product' => $product->getOneProduct($id),
            'mall' => Config::get('mall'),
        ]);

        $this->assign([
            'normal'=> $this->getAllNormalInfo()
        ]);
        return $this->fetch();
    }

    /**
     * tips : 删除单个产品
     * @return \think\response\Json
     *
     * author : FrankLee  dateTime : 2019-04-11 14:05
     */
    public function productDel()
    {
        $id = input('param.id');

        $product = new ProductModel();
        $flag = $product->delProduct($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /**
     * 批量删除商品
     * @return \think\response\Json
     *
     * author : FrankLee  dateTime : 2019-04-17 10:41
     */
    public function productBatchDel()
    {
        if (request()->isPost()){
            // 前端 post 一个数组 或字符串  里面是要删除的产品的 ID 根据 ID  执行删除操作
//            request();
//            $param = input('param.post');
//            dump(file_get_contents('php://input','r'));
            $param = json_decode(file_get_contents('php://input','r'));

            $result =  ProductModel::destroy($param);
            if ($result == 0){
                return json(msg(0,'','删除失败'));
            }else{
                return json(msg(1,'','删除成功'));
            }
        }
        return json(msg(0,'','不支持的请求类型'));
    }

    /**
     * 上传图片
     * @return \think\response\Json
     *
     * author : FrankLee  dateTime : 2019-04-17 10:41
     */
    public function uploadImg()
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

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'product/productedit',
                'href' => url('product/productedit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'product/productdel',
                'href' => "javascript:productDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

    /**
     * 获取所有的页面列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function getAllNormalInfo()
    {
        $normal = new NormalModel();
        $where = [];
        $offset  = 0;
        $limit = 100;
        return $normal->getNormalByWhere($where, $offset, $limit);

    }

    /**
     * tips : 给商品的标题添加超链接
     * @param $product
     * @return string
     *
     * author : FrankLee  dateTime : 2019-04-10 15:52
     */
    private function setTitleHref($product)
    {
        return '<a target="_blank" href="'.$product['url'].'">'. $product['title'] .'</a>';
    }

    /**
     *
     * @return \think\response\Json
     *
     * author : FrankLee  dateTime : 2019-04-20 11:58
     */
    public function checkProductInfoByCopyStr()
    {
        if (request()->isPost()){
            $param = input('post.');
            $product = new ProductModel();
            $flag = $product->getProductInfoByCopyStr($param['copy_str']);
            return json($flag);
        }
    }

    /**
     *
     * @param $param
     * @return array
     *
     * author : FrankLee  dateTime : 2019-04-20 11:58
     */
    public function pddProductAdd($param)
    {

            $param['add_time'] = date('Y-m-d H:i:s');

            $product = new ProductModel();
            $flag = $product->addProduct($param);

            return msg($flag['code'], $flag['data'], $flag['msg']);


    }
}
