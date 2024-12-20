<?php

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Lightna_Frontend',
    __DIR__
);

if (PHP_SAPI === 'cli') {
    require_once __DIR__ . '/registration/cli.php';
}
