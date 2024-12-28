<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity\Content;

class Category extends Page
{
    public const string NAME = 'content_category';
    public const string STORAGE_PREFIX = 'CNT.C_';

    /** @AppConfig(entity/content_category/storage) */
    protected string $storageName;
}
