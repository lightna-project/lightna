<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

use Exception;

class State extends EntityA
{
    public const IS_GLOBAL = true;
    public const STORAGE_PREFIX = 'STATE_';

    /** @AppConfig(entity/state/storage) */
    protected string $storageName;

    protected function init(array $data = []): void
    {
        if ($this->storageName === 'opcache') {
            /**
             * State storage must be realtime including multinode setup
             */
            throw new Exception('opcache storage for state isn\'t supported at the moment');
        }
    }
}
