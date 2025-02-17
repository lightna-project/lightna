<?php

return [
    'mode' => 'dev',
    'router' => [
        'bypass' => [
            'rules' => [
                'url_starts_with' => [
                    // Admin URL prefix
                    'admin(/|$)',
                ],
            ],
        ],
    ],
    'session' => [
        // 'file' and 'redis' are supported out of the box
        // 'redis' requires session_redis storage to be configured in storage section
        'handler' => 'file',
    ],
    'storage' => [

        // Additional storages if needed
        //'redis' => [
        //    'options' => [
        //        'host' => 'localhost',
        //        'port' => 6379,
        //        'db' => 0,
        //        'prefix' => '',
        //    ],
        //],
        //'database' => [
        //    'options' => [
        //        'host' => 'localhost',
        //        'port' => '3306',
        //        'username' => '****',
        //        'password' => '****',
        //        'dbname' => '****',
        //        'shared' => 'SHARED_PDO_CONNECTION',
        //    ],
        //],
    ],
    'project' => [
        // Give Lightna access to Magento database
        'connection' => [
            'host' => 'localhost',
            'port' => '3306',
            'username' => '****',
            'password' => '****',
            'dbname' => '****',
        ],
    ],
    // Configure Elasticsearch if needed
    //'elasticsearch' => [
    //    'connection' => [
    //        'host' => 'localhost',
    //        'port' => 9200,
    //    ],
    //    'prefix' => '****',
    //],
];
