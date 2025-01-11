<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Plugin\App\Index\Changelog;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\App\Query\Product;

class Handler extends ObjectA
{
    protected Product $productQuery;

    /** @noinspection PhpUnused */
    public function addIndexBatchDependenciesExtended(Closure $proceed, array &$indexBatch): void
    {
        $productIds = $indexBatch['product'] ?? [];
        $parentIds = $productIds ? $this->productQuery->getParentsBatch($productIds) : [];
        $indexBatch['product'] = merge($productIds, $parentIds);

        $proceed();
    }
}
