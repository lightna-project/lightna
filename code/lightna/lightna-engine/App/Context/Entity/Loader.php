<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Context\Entity;

use Lightna\Engine\App\Context;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\Exception\NotFoundException;
use Lightna\Engine\App\ObjectA;

class Loader extends ObjectA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Context $context;
    protected static array $entityData;

    public function loadData(): array
    {
        if (isset(static::$entityData)) {
            throw new LightnaException("Attempt to load context entity a second time. This is likely a mistake. Review your implementation.");
        }

        return static::$entityData = $this->_loadData();
    }

    protected function _loadData(): array
    {
        if (!($type = $this->context->entity->type)
            || !($id = $this->context->entity->id)) {
            throw new LightnaException('Undefined entity context');
        }
        if (!$entityClass = ($this->entities[$type]['entity'] ?? null)) {
            throw new LightnaException('Unknown entity type "' . $type . '"');
        }
        if (!$entityData = getobj($entityClass)->get($id)) {
            throw new NotFoundException("Entity \"$type:$id\" not found");
        }

        return $entityData;
    }
}
