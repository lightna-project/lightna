<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity;

use Lightna\Engine\App\Entity\EntityA;

class Category extends EntityA
{
    const STORAGE_PREFIX = 'CAT_';

    /** @AppConfig(entity/category/storage) */
    protected string $storageName;
}
