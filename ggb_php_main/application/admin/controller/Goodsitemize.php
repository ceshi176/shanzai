<?php
namespace app\admin\controller;

use app\admin\model\GoodsitemizeModel;
use think\Db;
class Goodsitemize extends Base
{

    //商品分类添加
    public function additemize()
    {
        $itemize=new GoodsitemizeModel();
        if(request()->isPost()){
            $arr=input('post.');
            $item=$itemize->additem($arr);
            return json(msg('1' ,$item, '添加成功'));
        }

        $show=$itemize->typeshow();
        $list=getree($show);
        $this->assign('list',$list);
        return $this->fetch();
    }

     //商品展示
    public function listitemize()
    {
        $itemize=new GoodsitemizeModel();
        $item=$itemize->listitem();
        $lis=getree($item);
        $this->assign('item',$lis);
        return $this->fetch();
    }
}