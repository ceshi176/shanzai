<?php
namespace app\admin\controller;

use app\admin\model\ClaimsModel;
use app\admin\model\LegalaffairsModel;
use think\Db;
class Creditor extends Base
{
    // 审核债权人信息
    public function examine()
    {
        if(request()->isAjax()){

            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['claims_state'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $article = new ClaimsModel();
            $selectResult = $article->getArticlesByWhere($where, $offset, $limit);

            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['claims_img'] = '<img src="' . $vo['claims_img'] . '" width="40px" height="40px">';
                $selectResult[$key]['claims_time'] =date("Y-m-d H:i", $vo['claims_time']);
                $selectResult[$key]['examine_time'] =date("Y-m-d H:i", $vo['examine_time']);
                if($vo['type_id']=='1'){
                    $selectResult[$key]['type_id']='合同';
                }elseif ($vo['type_id']=='2'){
                    $selectResult[$key]['type_id']='欠条';
                }
                if($vo['claims_state']=='1'){
                    $selectResult[$key]['claims_state']='未审核';
                }elseif ($vo['claims_state']=='2'){
                    $selectResult[$key]['claims_state']='审核通过';
                }else{
                    $selectResult[$key]['claims_state']='审核未通过';
                }
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['claims_id']));
            }

            $return['total'] = $article->getAllArticles($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

   public function notpass()
   {
       $article = new ClaimsModel();
       if(request()->isPost()){
           //echo 123;exit;
           $data['claims_id']=input('claims_id');
           $data['claims_name']=input('post.claims_name');
           $data['reason']=input('post.description');
           $data['claims_state']=3;
           $res = Db::name('claims')->update($data,['claims_id' =>$data['claims_id']]);
           return json(msg('1' ,$res, '审核未通过'));
       }
       $claims_id=input('claims_id');
       $arr=$article->not($claims_id);
       $this->assign('arr',$arr);
       return $this->fetch();
   }
    public function pass()
    {
        $claims_id=input('claims_id');
        $res = Db::name('claims')->where('claims_id',$claims_id) ->update(['claims_state' => '2']);;
        return json(msg('1' ,$res, '审核通过'));
    }
    public function legalaffairs()
    {
        if(request()->isPost()){
            $arr['title']=input('title');
            $arr['content']=input('content');
            $files = request()->file('imgs');
            foreach($files as $file){
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data[] = '/uploads/'.$info -> getSaveName();
                var_dump($data);'die';
            }
            $arr['img'] = implode(',',$data);
            $myfile=$_FILES["file"];
            $tmp=$myfile['tmp_name'];
            $a='video/'.time().'.mp4';
            $path=ROOT_PATH .'public/'.$a ;
            $arr['video']=$a;
            if(!move_uploaded_file($tmp,$path)) die('视频上传失败');
            $arr['publisher']=input('publisher');
            $arr['date']=time();
            $legal=new LegalaffairsModel();
            $suc=$legal->addlegal($arr);
            return json(msg('1' ,$suc, '发布成功'));
        }
        return $this->fetch();
    }
    //查询法务大讲堂
    public function legalalist()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['publisher'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $article = new LegalaffairsModel();
            $selectResult = $article->getArticlesByWhere($where, $offset, $limit);
            //var_dump($selectResult);die;

            foreach ($selectResult as $key => $vo) {
                $result[$key]=explode(",",$vo['img']);

                  foreach ($result[$key] as $k=>$v) {
                      //print_r($v);exit;
                      $selectResult[$key]['img'] = '<img src="' . $v . '" width="100px" height="100px">';
                      //print_r($selectResult[$key]['img']);
                  }

                $selectResult[$key]['video'] = '<video width="10px" height="10px" controls="controls" src="' . $vo['video'] . '"></video>';
                $selectResult[$key]['date'] = date("Y-m-d H:i", $vo['date']);

                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] = $article->getAllArticles($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
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
                'auth' => 'creditor/notpass',
                'href' => url('creditor/notpass', ['claims_id' => $id]),
                'btnStyle' => 'danger',
                'icon' => 'fa fa-paste'
            ],
            '通过' => [
                'auth' => 'creditor/pass',
                'href' => "javascript:pass(" . $id . ")",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
