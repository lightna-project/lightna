<?php

declare(strict_types=1);

$env = require BP . '/app/etc/env.php';

$error = match (true) {
    !isset($env['lightna_entry']) => 'Please specify "lightna_entry" in app/etc/env.php',
    !is_dir($env['lightna_entry']) => '"lightna_entry" directory does not exist',
    !is_file($env['lightna_entry'] . '/index.php') => 'Invalid directory "lightna_entry"',
    default => null,
};

if ($error) {
    throw new Exception($error);
}

define('LIGHTNA_ENTRY', realpath($env['lightna_entry']) . '/');
$edition = $_SERVER['LIGHTNA_EDITION'] ?? 'main';

if (!is_file($configFile = LIGHTNA_ENTRY . "edition/$edition/applied/backend.php")) {
    throw new Exception("Lightna build doesn't exist, have you run make build?");
}

require LIGHTNA_ENTRY . (require $configFile)['lightna_dir'] . '/App/boot.php';
