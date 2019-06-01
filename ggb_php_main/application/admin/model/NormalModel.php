<?php
namespace app\admin\Model;


use think\Model;
use think\Db;
use app\admin\model\ProductModel;
//use traits\model\SoftDelete;


class NormalModel extends Model
{
    // 存放表名
    protected $name = 'normal';


    /**
     * 获取当前页面的产品数量
     * @param $val
     * @param $data
     * @return int|string
     *
     * author : FrankLee  dateTime : 2019-04-23 12:27
     */
    public function getProductnumAttr($val,$data)
    {
        $product = new ProductModel();
        return $product->getAllProduct(['normal_id'=>$data['id']]) == 0 ? '<span style="color:red">0</span>' : $product->getAllProduct(['normal_id'=>$data['id']]);
    }


    /**
     * 获取所有页面以及页面绑定的商品的信息
     * @return array
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     *
     * author : FrankLee  dateTime : 2019-04-23 12:28
     */
    public function productNumInNormal()
    {
        $arr = $this->query('SELECT n.id , SUM(n.id = p.normal_id) AS times FROM h5_normal AS n, h5_product AS p
GROUP BY n.id');
        $data = array_column($arr,'times','id');
        return $data;
    }

    /**
     * 查询活动页面列表
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * author : FrankLee  dateTime : 2019-04-23 12:28
     */
    public function getNormalByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 给 API 查询发现页常规活动接口
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * author : FrankLee  dateTime : 2019-04-23 13:22
     */
    public function getNormalForApi()
    {
        // 联表查询 查询 normal 表中标识了 find = 1 且 url != null 的数据
        return Db::table('h5_normal')
            ->alias('a')
            ->join('h5_share f','a.id = f.normal_id')
            ->field('a.id,a.title,a.url,f.thumbnail,f.description')
            ->where('a.find',1)
            ->select();
    }

    /**
     *  获取数据的数量
     * @param $where
     * @return int|string
     *
     * author : FrankLee  dateTime : 2019-04-23 13:22
     */
    public function getAllNormal($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 查询单条数据
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * author : FrankLee  dateTime : 2019-04-23 13:22
     */
    public function  getOneNormal($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 添加页面
     * @param $param
     * @return array
     */
    public function addNormal($param)
    {
        try{
            $result = $this->validate('NormalValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{
                return msg(1, url('normal/index'), '新建活动成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑页面
     * @param $param
     * @return array
     *
     * author : FrankLee  dateTime : 2019-04-23 13:23
     */
    public function editNormal($param)
    {
        try{
            $result = $this->validate('NormalValidate')->save($param, ['id' => $param['id']]);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('normal/index'), '编辑活动页面成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 删除页面信息
     * @param $id
     * @return array
     *
     * author : FrankLee  dateTime : 2019-04-23 14:49
     */
    public function delNormal($id)
    {
        try{

            $this->where('id', $id)->delete();
            return msg(1, '', '删除活动页面成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 根据 sort_id 排序
     * @param $arr
     * @return mixed
     *
     * author : FrankLee  dateTime : 2019-04-20 15:58
     */
    private function maopao($arr)
    {
        if(empty($arr) || is_null($arr)){
            return $arr;
        }else{
            $len = count($arr);
            for($i=1; $i<$len; $i++)//最多做n-1趟排序
            {
                $flag = false;    //本趟排序开始前，交换标志应为假
                for($j=$len-1;$j>=$i;$j--)
                {
                    if($arr[$j]['sort_id']<$arr[$j-1]['sort_id'])//交换记录
                    {//如果是从大到小的话，只要在这里的判断改成if($arr[$j]>$arr[$j-1])就可以了
                        $x=$arr[$j];
                        $arr[$j]=$arr[$j-1];
                        $arr[$j-1]=$x;
                        $flag = true;//发生了交换，故将交换标志置为真
                    }
                }
                if(! $flag)//本趟排序未发生交换，提前终止算法
                    return $arr;
            }
            return $arr;
        }
    }


}