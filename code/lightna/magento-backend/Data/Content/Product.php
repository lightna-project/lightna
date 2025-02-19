<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Content;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Backend\Data\Content\Product\VisibleOnFrontAttribute;

/**
 * @property VisibleOnFrontAttribute[] visibleOnFrontAttributes
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
