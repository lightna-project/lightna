<?php

return [
    /**
     * Path to Lightna package folder
     * ! (Can be defined in config.php only)
     */
    'src_dir' => '../../code/lightna/lightna-engine',
    /**
     * List of modules (namespace => folder)
     * ! (Can be defined in config.php only)
     */
    'modules' => [
        'Lightna\Redis' => '../../code/lightna/lightna-redis',
        'Lightna\Session' => '../../code/lightna/lightna-session',
        'Lightna\Elasticsearch' => '../../code/lightna/lightna-elasticsearch',
        'Lightna\Magento' => '../../code/lightna/magento-os-backend',
        'Lightna\Magento\Frontend' => '../../code/lightna/magento-os-frontend',
        'Lightna\Magento\Demo' => '../../code/lightna/magento-os-demo',
        'Lightna\Magento\FirstExtend' => '../../code/lightna/extend-js-here',
        'Lightna\Magento\SecondExtend' => '../../code/lightna/extend-js-here-and-here',
    ],
    'libs' => [
        'Laminas\Db' => 'vendor/laminas/laminas-db/src',
        'Laminas\Stdlib' => 'vendor/laminas/laminas-stdlib/src',
    ],
    'compiler' => [
        'dir' => '../../opcache/magento-os/compiled',
    ],
    'opcache' => [
        'dir' => '../../opcache/magento-os/storage',
    ]
];
