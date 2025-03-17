<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Index;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Schema\Index\Triggers;
use Lightna\Magento\Backend\App\Query\Index\CategoryProductsReplica;
use Lightna\Magento\Backend\App\Query\Index\PriceReplica;

class Service extends ObjectA
{
    protected Triggers $triggers;
    protected PriceReplica $priceReplica;
    protected CategoryProductsReplica $categoryProductsReplica;

    public function unwatchReplica(string $table): void
    {
        $this->unwatchBoth($table);
        $this->triggers->watchTable($table);
    }

    public function watch(string $table): void
    {
        $this->unwatchBoth($table);
        $this->triggers->watchTable($table);
        $this->triggers->watchTable($table . '_replica');
    }

    /**
     * Because of table renames triggers are moving but names aren't changed,
     * unwatch both is more reliable way to make sure triggers are restored correctly
     */
    protected function unwatchBoth(string $table): void
    {
        $this->triggers->unwatchTable($table);
        $this->triggers->unwatchTable($table . '_replica');
    }

    public function sync(string $table): void
    {
        if ($table === 'catalog_product_index_price') {
            $this->priceReplica->sync();
        } elseif ($table === 'catalog_category_product_index_store<scope_id>') {
            $this->categoryProductsReplica->sync();
        }
    }
}
