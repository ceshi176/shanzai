<?php
// ||=================================================================>>>>
// |            PHP IS THE BEST LANGUAGE IN THE WORLD 
// |------------------------------------------------------------------>>>>
// |    User: FrankLee
// |------------------------------------------------------------------>>>>
// |    Date: 2019/3/31 Time: 2:14 AM
// |------------------------------------------------------------------>>>>
// |                                   Author: FrankLee <frank227@163.com>
// ||=================================================================>>>>


// 测试环境配置
return [
    //临时关闭日志写入
    'log'            => [
        'type' => 'test',
    ],
    // 显示错误信息
    'show_error_msg' => true,
    // 显示错误信息详情
    'app_debug'      => true,
    // 接口域名
    'api_domain' => 'http://cs.ssup.cn',
    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------
    'cache'          => [
        // 驱动方式
        'type'            => 'file',
        // 缓存保存目录
        'path'            => CACHE_PATH,
        // 缓存前缀
        'prefix'          => '',
        // 缓存有效期 0表示永久缓存
        'expire'          => 1,
    ],

    'database' => [
        // 数据库类型
        'type'            => 'mysql',
        // 测试服务器地址
        'hostname'        => 'ggb-dev-extranet.mysql.zhangbei.rds.aliyuncs.com',
        // 数据库名
        'database'        => 'phpdata',
        // 用户名
        'username'        => 'himall_test',
        // 密码
        'password'        => 'Himall_test',
        // 端口
        'hostport'        => '3306',
        // 连接dsn
        'dsn'             => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => 'h5_',
        // 数据库调试模式
        'debug'           => true,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy'          => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate'     => false,
        // 读写分离后 主服务器数量
        'master_num'      => 1,
        // 指定从服务器序号
        'slave_no'        => '',
        // 是否严格检查字段是否存在
        'fields_strict'   => false,
        // 数据集返回类型
        'resultset_type'  => 'array',
        // 自动写入时间戳字段
        'auto_timestamp'  => false,
        // 时间字段取出后的默认时间格式
        'datetime_format' => false,
        // 是否需要进行SQL性能分析
        'sql_explain'     => false,
    ],

];
