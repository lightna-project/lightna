<?php
/** @var array $config */

declare(strict_types=1);

use Lightna\Engine\App\Bootstrap;

require __DIR__ . '/Bootstrap.php';

Bootstrap::declaration($config);
Bootstrap::autoload();
Bootstrap::objectManager();
