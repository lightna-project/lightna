<?php

return [
    'modules' => [

        // List of directories where Lightna modules can be localed
        'pool' => ['vendor', 'module'],

        // List of enabled modules
        'enabled' => [
            'lightna/webpack',
            'lightna/tailwind',
            'lightna/phpunit',
            'lightna/session',
            'lightna/magento-backend',
            'lightna/magento-frontend',
            'lightna/magento-frontend-lane',
        ],
    ],

    // Relative path to the directory where Lightna will make its build
    // Example: 'generated/compiled', '../generated/compiled'
    'compiler_dir' => '****',

    // Relative path to the directory where Lightna will move built assets (CSS, JS, fonts, images, etc)
    // Example: '../pub/lightna', '../magento/pub/lightna'
    'asset_dir' => '****',

    // Relative path to the document root, required to build the correct URLs to the assets
    // Example: '../pub', '../magento/pub'
    'doc_dir' => '****',

    // Relative path to Magento directory, required for indexer to collect data from Magento modules
    // Example: '..', '../magento
    'project_dir' => '****',

    // Recommended: false
    'fpc_compatible' => false,
    // Check documentation
    'progressive_rendering' => false,
    'maintenance' => [
        'dir' => 'maintenance',
        'vary_name' => 'MAGE_RUN_CODE',
    ],
    'router' => [
        'bypass' => [

            // Example: '../pub/magento_index.php'
            'file' => '****',

            // When URL is unknown to Lightna it could render 404 or bypass request to Magento
            // Values: 404 | 'bypass'
            'no_route' => '****',
        ],
    ],
    'storage' => [
        'opcache' => [
            'options' => [
                // Example: 'generated/storage'
                'dir' => '****',
            ],
        ],
    ],
];
