<?php
namespace app\admin\Model;


use think\Model;


class ActivityModel extends Model
{
    // 存放表名
    protected $name = 'activity';

    // 查询活动页面列表
    public function getActivityByWhere($where, $offset, $limit)
    {
        return $this
            ->where($where)
            ->limit($offset, $limit)
            ->order('id desc')
            ->select();
    }

    // 获取数据的数量
    public function getAllActivity($where)
    {
        return $this->where($where)->count();
    }

    public function  getOneActivity($id)
    {
        return $this->where('id', $id)->find();
    }

    // 添加框架
    /**
     * @param $param
     * @return array
     */
    public function addActivity($param)
    {
        try{
            $result = $this->validate('ActivityValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{
                return msg(1, url('activity/index'), '新建活动成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    public function editActivity($param)
    {
        try{

            $result = $this->validate('ActivityValidate')->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('activity/index'), '编辑活动页面成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }
    public function bindActivityFrame()
    {

    }


    public function delActivity($id)
    {
        try{

            $this->where('id', $id)->delete();
            return msg(1, '', '删除活动页面成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }

    /** 根据活动页面的 ID 查找相关信息:
     *  1. 归属于当前页面的 框架
     *  2. 框架的排序类型
     *  3. 属于当前框架的商品
     */


}