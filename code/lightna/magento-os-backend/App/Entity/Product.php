<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity;

use Lightna\Engine\App\Entity\EntityA;

class Product extends EntityA
{
    const STORAGE_PREFIX = 'PRD_';

    /** @AppConfig(entity/product/storage) */
    protected string $storageName;
}
