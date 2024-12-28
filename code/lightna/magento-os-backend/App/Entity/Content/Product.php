<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity\Content;

class Product extends Page
{
    public const string NAME = 'content_product';
    public const string STORAGE_PREFIX = 'CNT.P_';

    /** @AppConfig(entity/content_product/storage) */
    protected string $storageName;
}
