<?php
namespace app\admin\model;

use think\Model;

class GoodsModel extends Model
{
    protected $name = 'goods';

    // 查询
    public function getGoodsByWhere($where, $offset=null, $limit=null)
    {

        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * @param $id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getGoodsByFrameId($id)
    {
        return $this->where('frame_id', $id)->select();
    }

    // 根据条件获取数量
    public function getAllGoods($where)
    {
        return $this->where($where)->count();
    }

    // 添加商品
    public function addGoods($param)
    {
        try{
            $result = $this->validate('GoodsValidate')->save($param);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('goods/index'), '添加商品成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    // 编辑商品

    public function editGoods($param)
    {
        try{
            $result = $this->validate('GoodsValidate')->save($param, ['id' => $param['id']]);
            if(false === $result){
                return msg(-1, '', $this->getError());
            }else{
                return msg(1, url('goods/index'), '编辑商品成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }
    //

    // 根据 ID 查找单条数据
    public function getOneGoods($id)
    {
        return $this->where('id', $id)->find();
    }

    // 删除
    public function delGoods($id)
    {
        try{
            $this->where('id', $id)->delete();
            return msg(1, '', '删除商品成功');
        }catch (\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }

}














