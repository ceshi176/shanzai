<?php
namespace app\admin\controller;
use think\Config;
use app\admin\model\AuditModel;
use app\admin\model\NormalModel;
use think\Db;
class Audit extends Base
{
    // 文章列表
    public function audit()
    {
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['store_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            if (!empty($param['searchTexts'])) {
                $where['audit'] = ['like', '%' . $param['searchTexts'] . '%'];
            }
//            print_r($where);exit;
            $producttitle = new AuditModel();
            $selectResult = $producttitle->getProducttitleByWhere($where, $offset, $limit);
       // print_r($selectResult);exit;
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['license'] = '<img src="' . $vo['license'] . '" width="40px" height="40px">';
                $selectResult[$key]['identity'] = '<img src="' . $vo['identity'] . '" width="40px" height="40px">';
                $selectResult[$key]['identity_s'] = '<img src="' . $vo['identity_s'] . '" width="40px" height="40px">';
                $selectResult[$key]['door'] = '<img src="' . $vo['door'] . '" width="40px" height="40px">';
                $selectResult[$key]['qualifi'] = '<img src="' . $vo['qualifi'] . '" width="40px" height="40px">';
                if($vo['audit']=='1'){
                    $selectResult[$key]['audit']='未审核';
                }elseif ($vo['audit']=='2'){
                    $selectResult[$key]['audit']='已审核';
                }else{
                    $selectResult[$key]['audit']='审核未通过';
                }
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $producttitle->getAllProducttitle($where);  // 总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        return $this->fetch();
    }

    // 添加文章
    public function producttitleAdd()
    {
        if(request()->isPost()){
            $param = input('post.');

            unset($param['file']);
            $param['add_time'] = date('Y-m-d H:i:s');

            $producttitle = new ProducttitleModel();
            $flag = $producttitle->addProducttitle($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        $this->assign([
            'normal'=> $this->getAllNormalInfo()
        ]);

        return $this->fetch();
    }
//审核
    public function adopt()
    {
        $id=input('id');
        $res = Db::name('other')->where('id',$id) ->update(['audit' => '2','store_stac'=>'']);
        return json(msg('1' ,$res, '审核通过了'));
    }
    public function producttitleEdit()
    {
        $producttitle = new AuditModel();
        if(request()->isPost()){

            $param = input('post.');
            unset($param['file']);
            $flag = $producttitle->editProducttitle($param);
            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'producttitle' => $producttitle->getOneProducttitle($id),
            'normal'       => $this->getAllNormalInfo()
        ]);


        return $this->fetch();
    }

    public function producttitleDel()
    {
        $id = input('param.id');
        $producttitle = new TextModel();
        $flag = $producttitle->delProducttitle($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }
//审核不通过
    public function product()
    {
        $product = new AuditModel();
        if(request()->isPost()){
            $param = input('post.');
            unset($param['file']);
            $flag = $product->editProduct($param);
            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        $id = input('param.id');
        $this->assign([
            'product' => $product->getOneProduct($id),
            'mall' => Config::get('mall'),
        ]);
        $this->assign([
            'normal'=> $this->getAllNormalInfo()
        ]);
        return $this->fetch();
    }
    // 上传缩略图
    public function uploadImg()
    {
        if(request()->isAjax()){

            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            if($info){
                $src =  '/upload' . '/' . date('Ymd') . '/' . $info->getFilename();
                return json(msg(0, ['src' => $src], ''));
            }else{
                // 上传失败获取错误信息
                return json(msg(-1, '', $file->getError()));
            }
        }
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
                '不通过' => [
                    'auth' => 'audit/product',
                    'href' => url('audit/product', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '通过' => [
                    'auth' => 'audit/adopt',
                    'href' => "javascript:adopt(" . $id . ")",
                    'btnStyle' => 'danger',
                    'icon' => 'fa fa-trash-o'
                ],
            ];
    }

    /**
     * 获取所有的页面列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function getAllNormalInfo()
    {
        $normal = new NormalModel();
        $where = [];
        $offset  = 0;
        $limit = 100;
        return $normal->getNormalByWhere($where, $offset, $limit);

    }
}
