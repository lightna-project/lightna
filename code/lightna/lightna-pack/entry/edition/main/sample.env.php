<?php

return [

    // Environment mode: 'dev' | 'prod'
    'mode' => 'dev',
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
        // Give Lightna access to app database.
        // Lightna needs a database at least as a lock provider.
        // If your project is very simple, and you don't use database,
        // then use custom plugin to change the implementation
        // for methods "get" and "release" in Lightna\Engine\App\Lock
        'connection' => [
            'host' => 'localhost',
            'port' => '3306',
            'username' => '****',
            'password' => '****',
            // Use null if database is used only as lock provider
            'dbname' => null,
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
