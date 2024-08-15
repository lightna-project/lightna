<?php

return [
    'mode' => 'dev',
    'doc_dir' => '../../project/magento-os/pub',
    'asset_dir' => '../../project/magento-os/pub/static/lightna',
    'storage' => [
        'redis' => [
            'options' => [
                'host' => 'localhost',
                'port' => '6379',
                'db' => 0,
            ],
        ],
    ],
    'router' => [
        'bypass' => [
            'rules' => [
                'url_starts_with' => [
                    'admin_1kw0j5(/|$)',
                ],
            ],
            'process_after_routing' => false,
            'cookie' => [
                'enabled' => true,
                'name' => '___BYPASS',
            ],
            'file' => '../../project/magento-os/pub/magento_index.php',
        ],
    ],
    'session' => [
        'handler' => 'Lightna\Session\App\Handler\File',
        'options' => [
            'cookie' => [
                'name' => 'PHPSESSID',
                'lifetime' => 3600,
                'secure' => false,
            ],
            'path' => '/var/lib/php/sessions',
            'prefix' => 'sess_',
        ]
    ],
    'project' => [
        'src_dir' => '../../project/magento-os',
        'connection' => [
            'username' => 'root',
            'password' => 'abcABC123',
            'dbname' => 'lightna_dev2',
        ],
    ],
    'elasticsearch' => [
        'connection' => [
            'host' => 'localhost',
            'port' => 9200,
        ],
        'prefix' => 'lightna-dev2',
    ],
];
