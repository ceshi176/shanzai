<?php
namespace app\admin\controller;

use app\admin\model\OrderModel;
use app\admin\model\NormalModel;
use think\Db;
class Order extends Base
{
    // 文章列表
    public function order()
    {
        $store_name = input('post.store_name');
        $consignee = input('post.consignee');
        $order_number = input('post.order_number');
        $order_status = input('post.order_status');//订单状态值
        $where['s.store_name'] = ['like', '%' .$store_name .'%'];
        $where['o.order_number'] = ['like', '%'  . $order_number .'%'];
        $where['o.order_status'] = ['like', '%' . $order_status .'%'];
        $where['o.consignee'] = ['like', '%' . $consignee .'%'];
        $date = Db::name('order')->alias('o')
            ->join('shop s','o.store_id = s.store_id','left')
            ->where($where)
            ->field('s.store_name,o.order_id,o.order_number,o.u_id,o.order_name,o.order_status,o.consignee,o.address,
            o.goods_price,o.shipping_price,o.integral,o.integral_money,o.shop_time,o.integral_money,o.order_num')
            ->select();
        return $this->fetch('exhibition',['data'=>$date]);
    }
    // 添加文章
    public function producttitleAdd()
    {
        if(request()->isPost()){
            $param = input('post.');

            unset($param['file']);
            $param['add_time'] = date('Y-m-d H:i:s');

            $producttitle = new ProducttitleModel();
            $flag = $producttitle->addProducttitle($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        $this->assign([
            'normal'=> $this->getAllNormalInfo()
        ]);

        return $this->fetch();
    }

    public function producttitleEdit()
    {
        $producttitle = new TextModel();
        if(request()->isPost()){

            $param = input('post.');
            unset($param['file']);
            $flag = $producttitle->editProducttitle($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'producttitle' => $producttitle->getOneProducttitle($id),
            'normal'       => $this->getAllNormalInfo()
        ]);


        return $this->fetch();
    }

    public function producttitleDel()
    {
        $order_id = input('param.order_id');

        $producttitle = new OrderModel();
        $flag = $producttitle->delProducttitle($order_id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    // 上传缩略图
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
    private function makeButton($order_id)
    {
        return [
            '编辑' => [
                'auth' => 'order/producttitleedit',
                'href' => url('order/producttitleedit', ['order_id' => $order_id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'order/producttitledel',
                'href' => "javascript:producttitleDel(" . $order_id . ")",
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
}
