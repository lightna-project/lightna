<?php

return [
    'modules' => [

        // List of directories where Lightna modules can be localed
        'pool' => ['vendor', 'module'],

        // List of enabled modules
        // Relative to the Lightna entry path to the module (not the name)
        'enabled' => [
            'lightna/webpack',
            'lightna/tailwind',
            'lightna/phpunit',
        ],
    ],

    // Relative path to the directory where Lightna will make its build
    'compiler_dir' => 'generated/compiled',

    // Relative path to the directory where Lightna will move built assets (CSS, JS, fonts, images, etc.)
    'asset_dir' => 'pub/lightna',

    // Relative path to the document root, required to build the correct URLs to the assets
    'doc_dir' => 'pub',

    // If used together with another platform:
    // Relative path to app directory, can be used by indexer to collect data from the app
    'project_dir' => '.',

    // Use page_cache.type = null unless:
    //   - Lightna page is cached by FPC (Varnish, Fastly, Builtin) and it renders private blocks
    //   - Lightna Lane page is cached by FPC, and it renders private blocks
    // Possible values: custom, edge
    'page_cache' => [
        'type' => null,
    ],

    // Use false unless you want to enable progressive rendering intentionally, read more in the documentation
    'progressive_rendering' => false,

    'maintenance' => [

        // The directory where custom default.phtml maintenance page can be placed
        'dir' => 'maintenance',

        // The $_SERVER variable name which is used to define custom maintenance page per website, for example [vary_value].phtml
        'vary_name' => null,
    ],
    'router' => [
        'bypass' => [

            // Relative path to the fallback file, if you have an app under hood to handle some of the requests
            'file' => null,
            'rule' => [

                // If URL is unknown to Lightna and has no matching bypass rule, it could render 404 page or bypass request to the app
                // Values: 404 | 'bypass'
                'no_route' => '****',
            ],
        ],
    ],
    'storage' => [
        'opcache' => [
            'options' => [

                // Relative path to indexer opcache files
                'dir' => 'generated/storage',
            ],
        ],
    ],
];
