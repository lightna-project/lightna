<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

use Exception;

class State extends EntityA
{
    public const string NAME = 'state';
    public const bool SCOPED = false;
    public const string STORAGE_PREFIX = 'STATE_';

    /** @AppConfig(entity/state/storage) */
    protected string $storageName;

    protected function init(array $data = []): void
    {
        $this->validateStorage();
    }

    protected function validateStorage(): void
    {
        // Extension point

        if ($this->storageName === 'opcache') {
            /**
             * State storage must be realtime including multinode setup
             * If you have shared writable opcache folder and implemented revalidated load for state entity only
             * then you can remove this validation
             */
            throw new Exception('opcache storage for state isn\'t supported at the moment');
        }
    }
}
