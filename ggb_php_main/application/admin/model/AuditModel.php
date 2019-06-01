<?php
namespace app\admin\model;

use think\Model;

use think\Db;
class AuditModel extends Model
{
    // 确定链接表名
    protected $name = 'other';
    // 修改 Model字段 新加 normal_title 字段 并修改值
    public function getNormalTitleAttr($val, $data)
    {
        return getNormalTitLeById($data['normal_id']);
    }
    /**
     * 查询活动页图片标题
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getProducttitleByWhere($where)
    {
        $arr=Db::name('other')->alias('o')
            ->join('shop s','o.store_id = s.store_id')
            ->field('s.store_name,o.license,o.identity,o.identity_s,o.door,o.qualifi,o.audit,o.id,o.store_stac')
            ->where($where)
            ->order('o.id desc')
            ->select();
        return $arr;
        //where($where)->limit($offset, $limit)->order('id desc')->select();
    }
    public function editProduct($param)
    {
        try{
            $result = $this->validate('ProducttitleValidate')->save($param, ['id' => $param['id']]);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{
                $res = Db::name('other')->where('id',$param['id']) ->update(['audit' => '3']);
                return msg(1, url('audit/audit'), '审核不通过成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }
    /**
     * 根据搜索条件获取所有的活动页图片标题数量
     * @param $where
     */
    public function getAllProducttitle($where)
    {
        return  Db::name('other')->alias('o')
            ->join('shop s','o.store_id = s.store_id')
            ->field('s.store_name,o.license,o.identity,o.identity_s,o.door,o.qualifi,o.audit,o.id,o.store_stac')
            ->where($where)
            ->count();

    }

    public function getOneProduct($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 添加活动页图片标题
     * @param $param
     */
    public function addProducttitle($param)
    {
        try{
            $result = $this->validate('ProducttitleValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('producttitle/index'), '添加活动页图片标题成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑活动页图片标题信息
     * @param $param
     */
    public function editProducttitle($param)
    {
        try{

            $result = $this->validate('ProducttitleValidate')->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('producttitle/index'), '编辑活动页图片标题成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据活动页图片标题的id 获取活动页图片标题的信息
     * @param $id
     */
    public function getOneProducttitle($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除活动页图片标题
     * @param $id
     */
    public function delProducttitle($id)
    {
        try{
            $this->where('id', $id)->delete();
            return msg(1, '', '删除活动页图片标题成功');
        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}
