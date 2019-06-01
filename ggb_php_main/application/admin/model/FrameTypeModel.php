<?php
namespace app\admin\model;

use think\Model;

class FrameTypeModel extends Model
{

    protected $name = 'frame_type';

    public function getFrameTypeByWhere($where, $offset, $limit)
    {
        return $this
            ->where($where)
            ->limit($offset, $limit)
            ->order('id desc')
            ->select();
    }

    public function getAllFrameType($where)
    {
        return $this
            ->where($where)
            ->count();
    }

    public function addFrameType($param)
    {
        try{
            $result = $this->validate('FrameTypeValidate')->save($param);
            if(false === $result){
                return msg(-1, '',$this->error);
            }else{
                return msg(1,url('frameType/index'),'新建框架类型成功');
            }
        }catch (\Exception $e){
            return msg(-1, '',$e->getMessage());
        }
    }

    public function editFrameType($param)
    {
        try{
            $result = $this->validate('FrameTypeValidate')->save($param, ['id' => $param['id']]);
            if(false === $result){
                return msg(-1, '',$this->getError());
            }else{
                return msg(1, url('frameType/index'), '框架更新成功');
            }
        }catch (\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }

    public function getOneFrameType($id)
    {
        return $this->where('id', $id)->find();
    }

    public function delFrameType($id)
    {
        try{
            $this->where('id', $id)->delete();
            return msg(1, '', '删除框架成功');
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }




}
