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
];
