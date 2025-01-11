<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Entity;

use Lightna\Engine\App\Entity\EntityA;

class RunCode extends EntityA
{
    public const NAME = 'run_code';
    public const SCOPED = false;
    public const STORAGE_PREFIX = 'RUN.CODE_';
    /** @AppConfig(entity/run_code/storage) */
    protected string $storageName;
}
