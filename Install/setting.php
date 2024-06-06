<?php
declare(strict_types=1);
//[
//    'demo' => [
//        [
//            'setting_key' => 'demo_string',
//            'setting_description' => '字符串类型配置示例',
//            'setting_value' => '示例字符串',
//            'type' => 'string',
//        ]
//    ]
//];
return [
    'aliwork' => [
        [
            'setting_key' => 'appkey',
            'setting_description' => 'appKey',
            'setting_value' => '',
            'type' => 'string',
        ],
        [
            'setting_key' => 'appsecret',
            'setting_description' => 'appSecret',
            'setting_value' => '',
            'type' => 'string',
        ],
        [
            'setting_key' => 'app_type',
            'setting_description' => '宜搭应用编码',
            'setting_value' => '',
            'type' => 'string',
        ],
        [
            'setting_key' => 'system_token',
            'setting_description' => '宜搭应用密钥',
            'setting_value' => '',
            'type' => 'string',
        ],
        [
            'setting_key' => 'user_id',
            'setting_description' => '宜搭操作员ID（建议是管理员）',
            'setting_value' => '',
            'type' => 'string',
        ]
    ]
];