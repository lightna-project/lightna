<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity\Content;

class Category extends Page
{
    const STORAGE_PREFIX = 'CNT.C_';

    /** @AppConfig(entity/content_category/storage) */
    protected string $storageName;
}
