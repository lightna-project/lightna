<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Plugin\App\Index\Changelog;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\App\Query\Product;

class Handler extends ObjectA
{
    protected Product $productQuery;

    /**
     * @see          \Lightna\Engine\App\Index\Changelog\Handler::addIndexBatchDependencies()
     * @noinspection PhpUnused
     */
    public function addIndexBatchDependenciesExtended(Closure $proceed, array &$indexBatch): void
    {
        if (!$productIds = ($indexBatch['product'] ?? [])) {
            $proceed();
            return;
        }

        $indexBatch['product'] = merge(
            $productIds,
            $this->productQuery->getParentsBatch($productIds),
            $this->productQuery->getRelatedParentsBatch($productIds),
        );

        $proceed();
    }
}
