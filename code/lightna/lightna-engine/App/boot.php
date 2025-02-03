<?php

declare(strict_types=1);

use Lightna\Engine\App\Bootstrap;

require_once __DIR__ . '/Bootstrap.php';

Bootstrap::declaration();
Bootstrap::autoload();
Bootstrap::maintenance();
Bootstrap::objectManager();
