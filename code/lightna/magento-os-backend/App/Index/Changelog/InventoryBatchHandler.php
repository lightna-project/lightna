<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Database;
use Lightna\Engine\App\Index\Changelog\BatchHandlerAbstract;

class InventoryBatchHandler extends BatchHandlerAbstract
{
    protected Database $db;

    public function handle(string $table, array $changelog): array
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
        $skus = $this->collectIds($changelog, 'sku', 'string');

        return $this->db->fetchCol($this->getProductIdsBySkusSelect($skus), 'entity_id');
    }

    protected function getProductIdsBySkusSelect(array $skus): Select
    {
        $select = $this->db->select(['p' => 'catalog_product_entity']);
        $select->where->in('sku', $skus);

        return $select;
    }
}
