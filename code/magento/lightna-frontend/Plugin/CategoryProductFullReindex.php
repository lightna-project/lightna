<?php

declare(strict_types=1);

namespace Lightna\Frontend\Plugin;

use Closure;
use Lightna\Magento\Backend\App\Index\Service;
use Magento\Catalog\Model\Indexer\Category\Product\Action\Full;

/** @noinspection PhpUnused */

class CategoryProductFullReindex
{
    protected const TABLE = 'catalog_category_product_index_store<scope_id>';
    protected Service $service;

    public function __construct()
    {
        $this->service = getobj(Service::class);
    }

    public function aroundExecute(Full $subject, Closure $proceed, $ids = null): Full
    {
        // Remove triggers from replica to avoid spam in changelog and retarded reindex
        $this->service->unwatchReplica(static::TABLE);

        $proceed($ids);

        // Replica was renamed back to source table, restore triggers
        $this->service->watch(static::TABLE);

        // Sync replica and price index to generate elegant changelog
        $this->service->sync(static::TABLE);

        return $subject;
    }
}
