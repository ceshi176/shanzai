<?php
namespace app\admin\model;

use think\Model;

class FrameModel extends Model
{

    protected $name = 'frame';

    public function getFrameByWhere($where, $offset, $limit)
    {
        return $this
            ->where($where)
            ->limit($offset, $limit)
            ->order('id desc')
            ->select();
    }

    public  function getAllFrameByWhere($where)
    {
        return $this
            ->where($where)
            ->select();
    }

    public function getAllFrame($where)
    {
        return $this
            ->where($where)
            ->count();
    }

    public function addFrame($param)
    {
        try{
            $result = $this->validate('FrameValidate')->save($param);
            if(false === $result){
                return msg(-1, '',$this->error);
            }else{
                return msg(1,url('frame/index'),'新建框架成功');
            }
        }catch (\Exception $e){
            return msg(-1, '',$e->getMessage());
        }
    }

    public function editFrame($param)
    {
        try{
            $result = $this->validate('FrameValidate')->save($param, ['id' => $param['id']]);
            if(false === $result){
                return msg(-1, '',$this->getError());
            }else{
                return msg(1, url('frame/index'), '框架更新成功');
            }
        }catch (\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }

    public function getOneFrame($id)
    {
        return $this->where('id', $id)->find();
    }

    public function delFrame($id)
    {
        try{
            $this->where('id', $id)->delete();
            return msg(1, '', '删除框架成功');
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    public function getFrameByActivityId($id)
    {
        return $this
            ->where('activity_id', $id)
            ->order('id')
            ->select();
    }


}
