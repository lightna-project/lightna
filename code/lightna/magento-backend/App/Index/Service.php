<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Update\Schema\Index\Triggers;
use Lightna\Magento\App\Query\Index\Price;

class Service extends ObjectA
{
    protected Triggers $triggers;
    protected Price $price;

    public function unwatchPriceReplica(): void
    {
        $this->unwatchBoth();
        $this->triggers->watchTable('catalog_product_index_price');
    }

    public function watchPrice(): void
    {
        $this->unwatchBoth();
        $this->triggers->watchTable('catalog_product_index_price');
        $this->triggers->watchTable('catalog_product_index_price_replica');
    }

    /**
     * Because of table renames triggers are moving but names aren't changed,
     * unwatch both is more reliable way to make sure triggers are restored correctly
     */
    protected function unwatchBoth(): void
    {
        $this->triggers->unwatchTable('catalog_product_index_price');
        $this->triggers->unwatchTable('catalog_product_index_price_replica');
    }

    public function syncPriceReplica(): void
    {
        $this->price->syncReplica();
    }
}
