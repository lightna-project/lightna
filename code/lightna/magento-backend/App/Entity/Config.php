<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Entity;

use Lightna\Engine\App\Entity\EntityA;

class Config extends EntityA
{
    public const NAME = 'config';
    public const STORAGE_PREFIX = 'CFG_';

    /** @AppConfig(entity/config/storage) */
    protected string $storageName;
}
