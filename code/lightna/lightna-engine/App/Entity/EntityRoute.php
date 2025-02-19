<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

class EntityRoute extends EntityA
{
    public const NAME = 'entity_route';
    public const STORAGE_PREFIX = 'URLs_';

    /**
     * @AppConfig(entity/route/storage)
     * (Not entity/entity_route/storage to have URL and Entity URLs in the same storage)
     */
    protected string $storageName;
}
