<?php
namespace app\admin\model;

use think\Model;

class ShareModel extends Model
{
    // 确定链接表名
    protected $name = 'share';

    public function getNormalTitleAttr($val, $data)
    {
        return getNormalTitLeById($data['normal_id']);
    }

    /**
     * 查询分享图
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getShareByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的分享图数量
     * @param $where
     */
    public function getAllShare($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 添加分享图
     * @param $param
     */
    public function addShare($param)
    {
        try{
            $result = $this->validate('ShareValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('share/index'), '添加分享图成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑分享图信息
     * @param $param
     */
    public function editShare($param)
    {
        try{

            $result = $this->validate('ShareValidate')->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('share/index'), '编辑分享图成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据分享图的id 获取分享图的信息
     * @param $id
     */
    public function getOneShare($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除分享图
     * @param $id
     */
    public function delShare($id)
    {
        try{

            $this->where('id', $id)->delete();
            return msg(1, '', '删除分享图成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}
