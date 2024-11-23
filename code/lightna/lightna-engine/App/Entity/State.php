<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

class State extends EntityA
{
    public const IS_GLOBAL = true;
    public const STORAGE_PREFIX = 'STATE_';

    /** @AppConfig(entity/state/storage) */
    protected string $storageName;
}
