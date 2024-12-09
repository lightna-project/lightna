<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

class EntityRoute extends EntityA
{
    public const NAME = 'entity_route';
    public const STORAGE_PREFIX = 'URLs_';

    /** @AppConfig(entity/entity_route/storage) */
    protected string $storageName;
}
