<?php

declare(strict_types=1);

namespace Lightna\Magento\Data;

use Lightna\Engine\App\NotFoundException;
use Lightna\Engine\Data\Context;
use Lightna\Engine\Data\EntityA;

/**
 * @property-read string contentHeading
 * @property-read string content
 * @method string contentHeading
 * @method string content
 */
class Cms extends EntityA
{
    /** @AppConfig(entity/cms/entity) */
    protected string $cmsEntity;
    protected Context $context;

    protected function init($data = [])
    {
        parent::init($this->getEntityData());
    }

    protected function getEntityData(): array
    {
        if ($this->context->entity->type !== 'cms') {
            throw new \Exception(
                'Attempt to load cms entity when rendering ' . $this->context->entity->type
            );
        }

        $entity = getobj($this->cmsEntity);

        if (
            !$this->context->entity->id
            || !($entityData = $entity->get($this->context->entity->id))
        ) {
            throw new NotFoundException(
                'Entity "' . $this->context->entity->type . ':' . $this->context->entity->id . '" not found'
            );
        }

        return $entityData;
    }
}
