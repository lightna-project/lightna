<?php

declare(strict_types=1);

$env = require BP . '/app/etc/env.php';

if (!isset($env['lightna_entry'])) {
    throw new \Exception('Please specify "lightna_entry" in app/etc/env.php');
}
if (!is_dir($env['lightna_entry'])) {
    throw new \Exception('"lightna_entry" directory does not exist');
}

define('LIGHTNA_ENTRY', realpath($env['lightna_entry']) . '/');

$config = require LIGHTNA_ENTRY . 'config.php';

require LIGHTNA_ENTRY . $config['src_dir'] . '/App/boot.php';
