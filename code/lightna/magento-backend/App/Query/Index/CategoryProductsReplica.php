<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query\Index;

use Lightna\Engine\App\Schema\Index\Triggers;
use Lightna\Engine\App\Scope;

class CategoryProductsReplica extends ReplicaAbstract
{
    protected Scope $scope;
    protected Triggers $triggers;
    protected string $tablePattern = 'catalog_category_product_index_store<scope_id>';
    protected string $offsetField = 'product_id';

    public function sync(): void
    {
        foreach ($this->triggers->getTablesByPattern($this->tablePattern) as $table) {
            $this->table = $table;
            parent::sync();
        }
    }

    /** @noinspection PhpUnused */
    protected function defineSyncBatchSize(): void
    {
        // 3 - Average number of categories per product
        $this->syncBatchSize = (int)($this->rowsBatchSize / 3);
    }
}
