<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Entity\Content;

class Category extends Page
{
    public const NAME = 'content_category';
    public const STORAGE_PREFIX = 'CNT.C_';

    /** @AppConfig(entity/content_category/storage) */
    protected string $storageName;
}
