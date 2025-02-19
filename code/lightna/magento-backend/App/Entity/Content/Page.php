<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Entity\Content;

use Lightna\Engine\App\Entity\EntityA;

class Page extends EntityA
{
    public const NAME = 'content_page';
    public const STORAGE_PREFIX = 'CNT.PG_';

    /** @AppConfig(entity/content_page/storage) */
    protected string $storageName;
}
