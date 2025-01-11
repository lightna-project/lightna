<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Content;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Data\Content\Product\FilterableAttribute;

/**
 * @property-read FilterableAttribute[] $filterableAttributes
 */
class Category extends DataA
{
    public array $filterableAttributes;

    /** @AppConfig(entity/content_category/entity) */
    protected string $contentCategoryEntity;

    protected function init(array $data = []): void
    {
        parent::init($this->getEntityData());
    }

    protected function getEntityData(): array
    {
        return getobj($this->contentCategoryEntity)->get(1);
    }
}
