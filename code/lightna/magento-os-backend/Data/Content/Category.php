<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Content;

use Lightna\Engine\App\Context;
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
    protected Context $context;

    protected function init($data = []): void
    {
        parent::init($this->getEntityData());
    }

    protected function getEntityData(): array
    {
        return getobj($this->contentCategoryEntity)->get($this->context->scope);
    }
}
