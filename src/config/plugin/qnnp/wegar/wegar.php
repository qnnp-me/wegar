<?php

use qnnp\wegar\Attribute\Helper\OpenAPI\info;
use qnnp\wegar\Attribute\Helper\OpenAPI\openapi;

return [
    openapi::info => [
        info::title   => 'Wegar',
        info::version => '0.0.1'
    ],
    // Res::json() 助手函数用到的模板
    'response'    => [
        'direct'      => false, // 直接输出 json 不使用模板
        'placeholder' => false, // 模板字段为空时是否出现在 Response 里
        'template'    => [
            'data'       => 'data',
            'message'    => 'msg',
            'error_code' => 'error',
            'trace'      => 'trace',
        ]
    ],
    // 未安装 webman/admin 时访问文档需要的密码，留空公开访问
    'password'=>'wegar'
];
