<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\Collect;
use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\App\Query\Product as ProductQuery;

class Inventory extends ObjectA implements CollectorInterface
{
    protected Collect $collect;
    protected ProductQuery $productQuery;

    public function collect(string $table, array $changelog): array
    {
        if ($this->isTableRelevant($table)) {
            return [
                'product' => $this->collectProductIds($changelog),
            ];
        }

        return [];
    }

    protected function isTableRelevant(string $table): bool
    {
        return in_array($table, ['inventory_source_item', 'inventory_reservation']);
    }

    protected function collectProductIds(array $changelog): array
    {
        $skus = $this->collect->ids($changelog, 'sku', 'string');

        return $this->productQuery->getProductIdsBySkus($skus);
    }
}
