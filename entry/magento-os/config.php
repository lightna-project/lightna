<?php

return [
    /**
     * Modules can be specified only in config.php
     */
    'modules' => [
        'Lightna\Redis' => '../../code/lightna/lightna-redis',
        'Lightna\Session' => '../../code/lightna/lightna-session',
        'Lightna\Elasticsearch' => '../../code/lightna/lightna-elasticsearch',
        'Lightna\Webpack' => '../../code/lightna/lightna-webpack',
        'Lightna\Tailwind' => '../../code/lightna/lightna-tailwind',
        'Lightna\Magento' => '../../code/lightna/magento-os-backend',
        'Lightna\Magento\Frontend' => '../../code/lightna/magento-os-frontend',
        'Lightna\Magento\Frontend\Lane' => '../../code/lightna/magento-os-frontend-lane',
        'Lightna\Magento\Demo' => '../../code/lightna/magento-os-demo',
    ],
    'compiler' => [
        'dir' => '../../generated/magento-os/compiled',
    ],
];
