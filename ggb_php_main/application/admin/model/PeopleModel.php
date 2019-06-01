<?php
/**
 * Created by PhpStorm.
 * User: ALIENWARE
 * Date: 2019/3/22
 * Time: 14:16
 */

namespace app\admin\model;


use think\Model;

class PeopleModel extends Model
{
    protected $name = "personnel";
    public function getCardByWhere($where, $offset, $limit)
    {
        $data = $this->where($where)->limit($offset, $limit)->select();
        return $data;
    }

    public function getAllCard($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 查询人员
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getArticlesByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的人员数量
     * @param $where
     */
    public function getAllArticles($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 添加人员
     * @param $param
     */
    public function addCard($param)
    {
        try{
            $result = $this->validate('CardValidate')->createAccount($param["prefix"], $param["count"]);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{
                return msg(1, url('card/index'), '生成充值卡成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑人员信息
     * @param $param
     */
    public function editArticle($param)
    {
        try{

            $result = $this->validate('ArticleValidate')->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('articles/index'), '编辑文章成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据文章的id 获取人员的信息
     * @param $id
     */
    public function getOneArticle($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 进行分配
     */
    static public function allottedPeople($data)
    {
        $result = CardModel::allottedCard($data);
        return $result;
    }

    /**
     * 添加业务员
     * @param @param
     * return array
     */
    public function addPeople($param)
    {
        try{
            $param["add_time"] = date("Y-m-d H:i:s");
            $result = $this->validate('PeopleValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('people/index'), '添加业务人员成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 返回编辑页面数据
     */
    public function getOnPeople($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 编辑人员信息
     * @param $param
     */
    public function uptPeople($param)
    {
        //查询传来的数据
        $validate = $this->where("id", $param["id"])->field("id, business_name, business_phone")->find();
        //验证数据是否更改
        $diff = array_diff((array) json_decode($validate), $param);
        //如果为空返回信息
        if (empty($diff)) return msg(1, url('people/peopleUpt'), '数据未进行修改');
        try{
            //验证数据是否正确
            $result = $this->validate('PeopleValidate')->save($param, ['id' => $param['id']]);
            //根据受影响行数判断是否失败
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('people/index'), '修改人员成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据UID查询归属人
     */
    public function queryPeople($data)
    {
        $query = [];
        foreach ($data as $queryKey => $queryVal)
        {
            $query = $this->where("user_id", $queryVal["uid"])->find();
            if ($query["business_name"] == "") $query["business_name"] = "未分配";
            $data[$queryKey]["business_name"] = $query["business_name"];
        }
        return $data;
    }
}