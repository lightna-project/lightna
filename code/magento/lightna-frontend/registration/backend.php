<?php

declare(strict_types=1);

$etcConfig = require BP . '/app/etc/lightna.php';

$error = match (true) {
    !isset($etcConfig['lightna_entry']) => 'Please specify "lightna_entry" in app/etc/lightna.php',
    !is_dir($etcConfig['lightna_entry']) => '"lightna_entry" directory does not exist',
    !is_file($etcConfig['lightna_entry'] . '/index.php') => 'Invalid directory "lightna_entry"',
    default => null,
};

if ($error) {
    throw new LogicException($error);
}

define('LIGHTNA_ENTRY', realpath($etcConfig['lightna_entry']) . '/');
$edition = $_SERVER['LIGHTNA_EDITION'] ?? 'main';

if (!is_file($configFile = LIGHTNA_ENTRY . "edition/$edition/applied/backend.php")) {
    throw new LogicException("Lightna build doesn't exist, have you run make build?");
}

require LIGHTNA_ENTRY . (require $configFile)['lightna_dir'] . '/App/boot.php';
