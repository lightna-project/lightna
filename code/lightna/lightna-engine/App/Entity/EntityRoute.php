<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

class EntityRoute extends EntityA
{
    const STORAGE_PREFIX = 'URLs_';
    const MULTIPLE_VALUES_PER_SCOPE = true;

    /** @AppConfig(entity/entity_route/storage) */
    protected string $storageName;
}
