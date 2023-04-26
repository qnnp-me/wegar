<?php

use qnnp\wegar\Attribute\Helper\OpenAPI\info;
use qnnp\wegar\Attribute\Helper\OpenAPI\openapi;

return [
    openapi::info => [
        info::title   => 'Wegar-0.0.2',
        info::version => '0.0.1'
    ],
    'response'    => [
        'direct'      => false,
        'placeholder' => false,
        'template'    => [
            'data'       => 'data',
            'message'    => 'msg',
            'error_code' => 'error',
            'trace'      => 'trace',
        ]
    ]
];
