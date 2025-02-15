<?php

return [
    'modules' => [

        // List of directories where Lightna modules can be localed
        'pool' => ['vendor', 'module'],

        // List of enabled modules
        // Use relative to the Lightna entry path to the module (not the name)
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

    // Use fpc_compatible = false unless:
    // 1) Lightna page is cached by FPC (Varnish, Fastly, Builtin) and it renders private blocks
    // 2) Lightna Lane page is cached by FPC, and it renders private blocks
    'fpc_compatible' => false,

    // Use false unless you want to enable progressive rendering intentionally, read more in the documentation
    'progressive_rendering' => false,

    'maintenance' => [
        // The directory where custom default.phtml maintenance page can be placed
        'dir' => 'maintenance',
        // The $_SERVER variable name which is used to define custom maintenance page per website, for example [vary_value].phtml
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
