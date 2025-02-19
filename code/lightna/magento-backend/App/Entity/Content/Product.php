<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Entity\Content;

class Product extends Page
{
    public const NAME = 'content_product';
    public const STORAGE_PREFIX = 'CNT.P_';

    /** @AppConfig(entity/content_product/storage) */
    protected string $storageName;
}
