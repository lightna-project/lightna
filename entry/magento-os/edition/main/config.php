<?php

return [
    'modules' => [
        'pool' => ['module', '../../code', 'vendor'],
        'enabled' => [
            'lightna/lightna-redis',
            'lightna/lightna-session',
            'lightna/lightna-elasticsearch',
            'lightna/lightna-webpack',
            'lightna/lightna-tailwind',
            'lightna/lightna-phpunit',
            'lightna/lightna-newrelic',
            'lightna/magento-newrelic',
            'lightna/magento-backend',
            'lightna/magento-frontend',
            'lightna/magento-frontend-lane',
            'lightna/magento-demo',
        ],
    ],
    'compiler_dir' => '../../generated/magento-os/compiled',
    'asset_dir' => '../../project/magento-os/pub/static/lightna',
    'doc_dir' => '../../project/magento-os/pub',
    'project_dir' => '../../project/magento-os',
    'entity' => [
//        'category' => [
//            'index' => null,
//        ],
//        'content_category' => [
//            'index' => null,
//        ],
//        'cms' => [
//            'index' => null,
//        ],
//        'custom_redirect' => [
//            'index' => null,
//        ],
    ],
    'storage' => [
        'opcache' => [
            'options' => [
                'dir' => '../../generated/magento-os/storage',
//                'is_read_only' => true,
            ],
        ],
    ],
];
