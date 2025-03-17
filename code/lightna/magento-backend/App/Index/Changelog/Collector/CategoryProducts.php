<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\App\Index\Changelog\Collect;

class CategoryProducts extends ObjectA implements CollectorInterface
{
    protected Collect $collect;

    public function collect(string $table, array $changelog): array
    {
        if (str_starts_with($table, 'catalog_category_product_index_store')) {
            return [
                'product' => $this->collect->ids($changelog, 'product_id'),
            ];
        }

        return [];
    }
}
