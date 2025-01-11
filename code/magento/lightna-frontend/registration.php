<?php

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Lightna_Frontend',
    __DIR__
);

/**
 *
 * INITIALIZE LIGHTNA LANE
 *
 * Check for BP to prevent boot on non-magento
 * Check for LIGHTNA_ENTRY to prevent Magento 2 ObjectManager on non-lightna-lane
 *
 **/
if (defined('BP')) {
    if (PHP_SAPI === 'cli') {
        require_once __DIR__ . '/registration/backend.php';
    }
    if (defined('LIGHTNA_ENTRY')) {
        require_once __DIR__ . '/registration/common.php';
    }
}
