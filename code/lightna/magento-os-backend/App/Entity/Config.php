<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity;

use Lightna\Engine\App\Entity\EntityA;

class Config extends EntityA
{
    const STORAGE_PREFIX = 'CFG_';
    const MULTIPLE_VALUES_PER_SCOPE = false;

    /** @AppConfig(entity/config/storage) */
    protected string $storageName;
}
