<?php
declare(strict_types=1);
// 菜单层级最多三级
//[
//    [
//        'parent_access_id' => 0,
//        'access_name' => '示例',
//        'uri' => 'demo/demo/none',
//        'params' => '',
//        'sort' => 100,
//        'is_menu' => 1,
//        'menu_icon' => 'el-icon-data-analysis',
//        'children' => []
//    ]
//]
return [
    [
        'parent_access_id' => 0,
        'access_name' => '钉钉宜搭',
        'uri' => 'aliwork/aliwork',
        'sort' => 100,
        'is_menu' => 1,
        'menu_icon' => 'line-icon-meishu',
        'children' => [
            [
                'access_name' => '配置',
                'uri' => 'aliwork/aliwork/setting',
                'sort' => 100,
                'is_menu' => 1,
                'children' => []
            ]
        ]
    ]
];