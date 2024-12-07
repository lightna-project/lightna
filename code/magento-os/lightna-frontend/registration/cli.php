<?php

declare(strict_types=1);

$env = require BP . '/app/etc/env.php';

if (!isset($env['lightna_entry'])) {
    throw new Exception('Please specify "lightna_entry" in app/etc/env.php');
}
if (!is_dir($env['lightna_entry'])) {
    throw new Exception('"lightna_entry" directory does not exist');
}

if (!is_file($env['lightna_entry'] . '/env.php')) {
    throw new Exception('Invalid directory "lightna_entry"');
}

define('LIGHTNA_ENTRY', realpath($env['lightna_entry']) . '/');

// Prevent broken bin/magento if Lightna isn't compiled yet
if (is_file($configFile = LIGHTNA_ENTRY . 'config/backend.php')) {
    require LIGHTNA_ENTRY . (require $configFile)['value']['src_dir'] . '/App/boot.php';
}
