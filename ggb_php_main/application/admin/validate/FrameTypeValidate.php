<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class FrameTypeValidate extends Validate
{
    protected $rule = [
        ['title', 'require', '框架名称不能为空'],
        ['row', 'require', '行数不能为空'],
        ['cloumn', 'require', '列数不能为空'],
        ['thumbnail', 'require', '文件不能为空']
    ];

}