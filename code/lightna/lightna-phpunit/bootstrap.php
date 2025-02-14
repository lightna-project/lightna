<?php

declare(strict_types=1);

define('LIGHTNA_ENTRY', realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/../..') . '/');

$modulesDir = __DIR__ . '/../';
$engineName = 'engine';
if (!is_dir($modulesDir . $engineName)) {
    $engineName = 'lightna-engine';
}

require_once "$modulesDir$engineName/App/boot.php";
