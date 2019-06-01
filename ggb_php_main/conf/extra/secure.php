<?php
return [
    // 获取用户的信息:手机号、用户名、用户 ID  ，通过用户名或者手机号
    "UrlNameLocation" => "http://xiaoyao.gangubang.cn/api/UserBind/GetUserIdByUserNameOrCellPhone?UserName=",

    // 通过 ID 获取 用户信息
    "UrlIdLocation" => "http://xiaoyao.gangubang.cn/api/UserBind/GetUserNameByUserId?UserId=",

    // 最后进行的充值（赠送米券）
    "UrlPost" => "http://xiaoyao.gangubang.cn/api/UserBind/PostGiveJfqByMemberCard",

    // 注册接口（老用户的话 返回注册信息， 新用户）
    "UrlSendCreateData" => "http://xiaoyao.gangubang.cn/api/UserBind/MemberCardRegister",

    // 通过用户 ID  绑定手机号
    "UrlBind" => "http://xiaoyao.gangubang.cn/api/UserBind/BindPhoneByUserId",

    //  获取用户是否已经领取过米券的接口
    "UrlGetJfq" => "http://xiaoyao.gangubang.cn/api/UserBind/GetUserIsGetJfq?UserId="
];