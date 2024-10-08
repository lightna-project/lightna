<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity\Content;

use Lightna\Engine\App\Entity\EntityA;

class Page extends EntityA
{
    const STORAGE_PREFIX = 'CNT|G_';
    const MULTIPLE_VALUES_PER_SCOPE = false;

    /** @AppConfig(entity/content_page/storage) */
    protected string $storageName;
}
