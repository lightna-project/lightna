<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

use Lightna\Engine\App\Storage\Opcache as OpcacheStorage;

class State extends EntityA
{
    public const NAME = 'state';
    public const SCOPED = false;
    public const STORAGE_PREFIX = 'STATE_';

    /** @AppConfig(entity/state/storage) */
    protected string $storageName;

    #[\Override]
    protected function defineStorage(): void
    {
        parent::defineStorage();

        if ($this->storageName === 'opcache') {
            $this->initOpcacheStorage();
        }
    }

    protected function initOpcacheStorage(): void
    {
        /**
         * Create new instance with custom settings
         * @var OpcacheStorage $storage
         */
        $storage = $this->storage = newobj($this->storage::class);

        $storage->setIniOptions([
            'validate_timestamps' => 1,
            'revalidate_freq' => 0,
        ]);

        $storage->disableSlap();
    }
}
