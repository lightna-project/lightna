<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Index\Provider\Content;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\App\Index\DataProvider\Cms\Block as CmsBlockProvider;
use Lightna\Magento\Backend\App\Query\Product\Eav;

class Product extends ObjectA
{
    protected CmsBlockProvider $cmsBlockProvider;
    protected Eav $eav;
    /** @AppConfig(backend:magento/product/blocks) */
    protected array $blocks;

    public function getData(): array
    {
        return merge(
            $this->cmsBlockProvider->getData($this->blocks),
            ['visibleOnFrontAttributes' => $this->getVisibleOnFrontAttributes()],
        );
    }

    protected function getVisibleOnFrontAttributes(): array
    {
        $attributes = $this->eav->getVisibleOnFrontAttributes();
        foreach ($attributes as &$attribute) {
            $attribute['code'] = camel($attribute['code']);
        }

        return $attributes;
    }
}
