<?php

declare(strict_types=1);

namespace Lightna\Frontend\Plugin;

use Closure;
use Lightna\Magento\App\Index\Service;
use Magento\Catalog\Model\Indexer\Product\Price\Action\Full;

class PriceFullReindex
{
    protected Service $service;

    public function __construct()
    {
        $this->service = getobj(Service::class);
    }

    public function aroundExecute(Full $subject, Closure $proceed, $ids = null): void
    {
        // Remove triggers from replica to avoid spam in changelog and retarded reindex
        $this->service->unwatchPriceReplica();

        $proceed($ids);

        // Replica was renamed back to index_price, restore triggers
        $this->service->watchPrice();

        // Sync replica and price index to generate elegant changelog
        $this->service->syncPriceReplica();
    }
}
