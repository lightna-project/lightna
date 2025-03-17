<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query\Index;

use Lightna\Magento\Backend\App\Query\Customer\Group;
use Lightna\Magento\Backend\App\Query\Website;

class PriceReplica extends ReplicaAbstract
{
    protected Website $website;
    protected Group $group;
    protected string $table = 'catalog_product_index_price';
    protected string $offsetField = 'entity_id';

    /** @noinspection PhpUnused */
    protected function defineSyncBatchSize(): void
    {
        $this->syncBatchSize = (int)(
            $this->rowsBatchSize
            / count($this->website->getList())
            / count($this->group->getList())
        );
    }
}
