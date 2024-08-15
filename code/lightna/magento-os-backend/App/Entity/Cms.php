<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity;

use Lightna\Engine\App\Entity\EntityA;

class Cms extends EntityA
{
    const STORAGE_PREFIX = 'CMS_';
    const MULTIPLE_VALUES_PER_SCOPE = true;

    /** @AppConfig(entity/cms/storage) */
    protected string $storageName;
}
