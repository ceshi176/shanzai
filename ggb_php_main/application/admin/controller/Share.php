<?php
namespace app\admin\controller;

use app\admin\model\ShareModel;
use app\admin\model\NormalModel;

class Share extends Base
{
    // 文章列表
    public function index()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $share = new ShareModel();
            $selectResult = $share->getShareByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['normal_title'] = getNormalTitLeById($selectResult[$key]['normal_id']);
                $selectResult[$key]['thumbnail'] = '<img src="' . $vo['thumbnail'] . '" width="40px" height="40px">';
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] = $share->getAllShare($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    // 添加文章
    public function shareAdd()
    {
        if (request()->isPost()) {
            $param = input('post.');

            unset($param['file']);
            $param['add_time'] = date('Y-m-d H:i:s');
            $param['thumbnail'] = domain().$param['thumbnail'];

            $share = new ShareModel();
            $flag = $share->addShare($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        // 将 nomal 信息注入到页面变量
        $this->assign([
            'normal'=> $this->getAllNormalInfo(),
        ]);

        return $this->fetch();
    }

    public function shareEdit()
    {
        $share = new ShareModel();
        if (request()->isPost()) {

            $param = input('post.');
            unset($param['file']);
            // 判断文件链接是否带有域名 如果有域名 直接赋值，如果没有域名则需要加上域名。
            $param['thumbnail'] = strpos($param['thumbnail'], domain()) === false ? domain().$param['thumbnail'] : $param['thumbnail'];
            $flag = $share->editShare($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'share' => $share->getOneShare($id),
            'normal'=> $this->getAllNormalInfo(),
        ]);
        return $this->fetch();
    }

    public function shareDel()
    {
        $id = input('param.id');

        $share = new ShareModel();
        $flag = $share->delShare($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    // 上传缩略图
    public function uploadImg()
    {
        if (request()->isAjax()) {

            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            if ($info) {
                $src = '/upload' . '/' . date('Ymd') . '/' . $info->getFilename();
                return json(msg(0, ['src' => $src], ''));
            } else {
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
            '编辑' => [
                'auth' => 'share/shareedit',
                'href' => url('share/shareedit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'share/sharedel',
                'href' => "javascript:shareDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
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
        $offset = 0;
        $limit = 100;
        return $normal->getNormalByWhere($where, $offset, $limit);


    }
}
