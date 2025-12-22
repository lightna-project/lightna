<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query\Product;

use Closure;
use Lightna\Magento\Backend\App\Query\EavAbstract;

class Eav extends EavAbstract
{
    public const ENTITY_TYPE = 4;
    public const ENTITY_TABLE = 'catalog_product_entity';

    public function getVisibleOnFrontAttributes(): array
    {
        return $this->filterAttributes(
            fn($attr) => $attr['is_visible_on_front'],
            ['id', 'code', 'label'],
        );
    }

    public function getFilterableAttributes(): array
    {
        return $this->filterAttributes(
            fn($attr) => $attr['is_filterable'],
            ['id', 'code', 'label', 'backend_type', 'frontend_input'],
        );
    }

    protected function filterAttributes(Closure $filter, array $fields): array
    {
        $attributes = [];
        foreach ($this->getScopeAttributes() as $code => $attribute) {
            if (!$filter($attribute)) {
                continue;
            }

            $attributes[$code] = [];
            foreach ($fields as $field) {
                $attributes[$code][$field] = $attribute[$field];
            }
        }

        return $attributes;
    }
}
