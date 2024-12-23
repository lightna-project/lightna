<?php

declare(strict_types=1);

use Lightna\Engine\App\ObjectManager as LightnaObjectManager;
use Magento\Framework\App\ObjectManager as MagentoObjectManager;

LightnaObjectManager::setProducer(
    'Magento2',
    function ($class) {
        return MagentoObjectManager::getInstance()->get($class);
    },
);
