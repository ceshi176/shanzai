<?php
namespace app\admin\model;

use think\Model;
use app\admin\Model\NormalModel;

class TopimgModel extends Model
{
    // 确定链接表名
    protected $name = 'topimg';


    // public function getThumbnailAttr($val)
    // {
    //     return '<img src="' . $val . '" width="40px" height="40px">';
    // }

    public function getNormalTitleAttr($val, $data)
    {
        return getNormalTitLeById($data['normal_id']);
    }

    /**
     * 查询顶图
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getTopimgsByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的顶图数量
     * @param $where
     */
    public function getAllTopimg($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 添加顶图
     * @param $param
     */
    public function addTopimg($param)
    {
        try{
            $result = $this->validate('TopimgValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('topimg/index'), '添加顶图成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑顶图信息
     * @param $param
     */
    public function editTopimg($param)
    {
        try{

            $result = $this->validate('TopimgValidate')->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('topimg/index'), '编辑顶图成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据顶图的id 获取顶图的信息
     * @param $id
     */
    public function getOneTopimg($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除顶图
     * @param $id
     */
    public function delTopimg($id)
    {
        try{
            $this->where('id', $id)->delete();
            return msg(1, '', '删除顶图成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}
