<?php
namespace app\admin\controller;

use app\admin\model\OrderModel;
use app\admin\model\NormalModel;
use think\Db;
class Cash extends Base
{
    // 文章列表
    public function cash()
    {
        $app_name = input('post.app_name');
        $where = [];
        if (!empty($app_name)) {
            $where['c.app_name'] = ['like', '%' .$app_name .'%'];
        }
        $date = Db::name('cashpay')->alias('c')
            ->join('order o','o.order_id = c.order_id','left')
            ->where($where)
            ->field('c.id,c.app_name,c.app_num,c.geway,c.third,c.payment,o.order_number,c.goods_price,c.transaction,c.refundflow,
            c.slipnumber,c.refundstatus,c.refundtime,c.transactiontime')
            ->select();
        return $this->fetch('cash',['data'=>$date]);
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
