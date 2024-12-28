<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity\Content;

use Lightna\Engine\App\Entity\EntityA;

class Page extends EntityA
{
    public const string NAME = 'content_page';
    public const string STORAGE_PREFIX = 'CNT.PG_';

    /** @AppConfig(entity/content_page/storage) */
    protected string $storageName;
}
