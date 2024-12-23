<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Content;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Data\Content\Product\VisibleOnFrontAttribute;

/**
 * @property-read VisibleOnFrontAttribute[] visibleOnFrontAttributes
 */
class Product extends DataA
{
    public string $uspHtml = '';
    public array $visibleOnFrontAttributes;

    /** @AppConfig(entity/content_product/entity) */
    protected string $contentProductEntity;

    protected function init(array $data = []): void
    {
        parent::init($this->getEntityData());
    }

    protected function getEntityData(): array
    {
        return getobj($this->contentProductEntity)->get(1);
    }
}
