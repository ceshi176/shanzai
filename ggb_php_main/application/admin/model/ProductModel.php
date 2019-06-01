<?php
namespace app\admin\model;

use think\Model;
use app\admin\model\NormalModel;
use app\admin\validate\ProductValidate;

class ProductModel extends Model
{
    // 确定链接表名
    protected $name = 'product';

    // 修改 Model字段 新加 normal_title 字段 并修改值
    public function getNormalTitleAttr($val, $data)
    {
        return getNormalTitLeById($data['normal_id']);
    }


    /**
     * 查询产品
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getProductByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的产品数量
     * @param $where
     */
    public function getAllProduct($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 添加产品
     * @param $param
     */
    public function addProduct($param)
    {
        // 如果 check = 1  说明已经确认过  可以直接添加数据
        // 如果 check ！= 1  说明没有确认过  需要确认 数据库内是否存在字段。返回相应信息。
        // TODO  功能实现但尚未优化代码
        try{
            $validate = new ProductValidate();
//            $check = $this->validate('ProductValidate');
            if (!$validate->check($param)){
                return msg(-1, '', $validate->getError());
            }else{
                if(array_key_exists('check',$param) && $param['check'] == 1){
//                $result = $this->validate('ProductValidate')->save($param);
                    $result = $this->validate('ProductValidate')->save($param);
                    if(false === $result){
                        // 验证失败 输出错误信息
                        return msg(-1, '', $this->getError());
                    }else {
                        return msg(1, url('product/index'), '添加产品成功');
                    }
                }else{
                    $checkInfo = $this->where(['mall_goods_id'=>$param['mall_goods_id']])->find();
                    if (is_null($checkInfo)){
                        $param['check'] = 1;
                        return $this->addProduct($param);
                    }else{
                        $normal = $this->getNormalTitleByProductMGID($param['mall_goods_id']);
                        return msg(2, '', '此商品已存在于  <span style="color: red">'. $normal['title'] .'</span> 页面，是否继续添加？');
                    }
                }
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑产品信息
     * @param $param
     */
    public function editProduct($param)
    {
        try{
            $result = $this->validate('ProductValidate')->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('product/index'), '编辑产品成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据产品的id 获取产品的信息
     * @param $id
     */
    public function getOneProduct($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除产品
     * @param $id
     */
    public function delProduct($id)
    {
        try{

            $this->where('id', $id)->delete();
            return msg(1, '', '删除产品成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * tips : 批量删除产品
     * @param $data
     * @return mixed
     *
     * author : FrankLee  dateTime : 2019-04-12 15:51
     */
    public function batchDelProduct($data)
    {
        return ProductModel::destory($data);
    }

    /**
     * tips : 通过商品在其它商城的 ID 来查询商品所在的页面的标题
     * @param $mall_goods_id
     * @return array|\Exception|false|\PDOStatement|string|Model
     *
     * author : FrankLee  dateTime : 2019-04-12 16:02
     */
    protected function getNormalTitleByProductMGID($mall_goods_id)
    {
        try{
            $productInfo = $this->where('mall_goods_id',$mall_goods_id)->find();
            $normal = new NormalModel();
            return $normal->where('id', $productInfo['normal_id'])->find();
        }catch (\Exception $e){
            return $e;
        }
    }

    /**
     * 从用户粘贴的数据中获取正确的推广链接
     * @param $str
     * @return bool|string
     *
     * author : FrankLee  dateTime : 2019-04-17 10:39
     */
    public function getProductUrlPDD($str)
    {
        if (!strpos($str, 'http')){
            return '无法从您输入的信息中获取有效信息，请核实输入的内容';
        }
            return substr($str, strpos($str, 'https:'));
    }

    public function getProductInfoByCopyStr($str)
    {
        // 判断输入的字符串是否有url
        if (false === strpos($str, 'http')){
            return msg(0,'','无法从您输入的信息中获取有效的 URL，请核实输入的内容');
        }
        $url = substr($str, strpos($str, 'https:'));
        parse_str(parse_url($url,PHP_URL_QUERY), $paramInUrl);

        // 判断输入的字符串是否有相关的参数
        if (!isset($paramInUrl['goods_id'])){
            return msg(0,'','无法送您输入的信息中获取有效参数，请核实输入内容');
        }
        // 声明用来存储商品信息的数组
        $productInfo = [];
        $productInfo['url'] = $url;
        $productInfo['mall_goods_id'] = $paramInUrl['goods_id'];

        // 组装拼多多需要的 post 数据，
        $postArr = [
            'type' => config('pdd_api_goods_detail'),
            'client_id' => config('pdd_client_id'),
            'goods_id_list' => '[' . $productInfo['mall_goods_id'] . ']',
        ];

        // 如果是数组说明数据查询成功  将相应的值保存到数组中
        $result = getProductInfoFromPDD($postArr);
        if (is_array($result)){
            $result = $result['goods_detail_response']['goods_details'][0];
            // 预处理数据 将数据单位换算为元
            $result['min_group_price'] = round($result['min_group_price'] / 100, 2);
            $result['coupon_discount'] = round($result['coupon_discount'] / 100,2);
            $result['promotion_rate'] = $result['promotion_rate'] / 1000;

            $productInfo['title'] = $result['goods_name'];
            $productInfo['price'] = $result['min_group_price'];
            $productInfo['prefer_num'] = $result['coupon_discount'];
            $productInfo['prefer_price'] = round($result['min_group_price'] - $result['coupon_discount'],2);
            $productInfo['total_reward'] = round($productInfo['prefer_price'] * $result['promotion_rate'],2);
            $productInfo['reward'] = round($productInfo['prefer_price'] * $result['promotion_rate'] * 0.9, 2) ;
            $productInfo['thumbnail'] = $result['goods_thumbnail_url'];
            $productInfo['my_reward'] =  round($productInfo['total_reward'] - $productInfo['reward'],2);
            $productInfo['promotion_rate'] = $result['promotion_rate'];

            return msg(200, $productInfo,'查询成功');
        }else{
            return msg(0,'',$result);
        }

    }

    /**
     * 获取 URL 中的参数。
     * @param $url
     * @return \Exception
     *
     * author : FrankLee  dateTime : 2019-04-19 10:48
     */
    public function getProductInfoByUrl($url)
    {
        try{
            parse_str(parse_url($url,PHP_URL_QUERY), $param);
            return $param;
        }catch (\Exception $e){
            return $e;
        }
    }
}
