<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Entity;

use Lightna\Engine\App\Entity\EntityA;

class Cms extends EntityA
{
    public const NAME = 'cms';
    public const STORAGE_PREFIX = 'CMS_';

    /** @AppConfig(entity/cms/storage) */
    protected string $storageName;
}
