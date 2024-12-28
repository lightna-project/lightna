<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity;

use Lightna\Engine\App\Entity\EntityA;

class Config extends EntityA
{
    public const string NAME = 'config';
    public const string STORAGE_PREFIX = 'CFG_';

    /** @AppConfig(entity/config/storage) */
    protected string $storageName;
}
