<?php

declare(strict_types=1);

namespace Lightna\Magento\Data;

use Lightna\Engine\App\NotFoundException;
use Lightna\Engine\Data\Context;
use Lightna\Engine\Data\EntityA;

/**
 * @method string name(string $escapeMethod = null)
 * @method string entityId(string $escapeMethod = null)
 */
class Category extends EntityA
{
    public string $name;
    public string|int $entityId;
    public ?string $image;
    public ?string $description;

    /** @AppConfig(entity/category/entity) */
    protected string $categoryEntity;
    protected Context $context;

    protected function init($data = [])
    {
        if (!$data) {
            parent::init($this->getEntityData());
            $this->title = $this->name;
        } else {
            parent::init($data);
        }
    }

    protected function getEntityData(): array
    {
        if ($this->context->entity->type !== 'category') {
            throw new \Exception(
                'Attempt to load category entity when rendering ' . $this->context->entity->type
            );
        }

        $entity = getobj($this->categoryEntity);

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
