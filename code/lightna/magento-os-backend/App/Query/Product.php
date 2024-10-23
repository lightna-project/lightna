<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Product extends ObjectA
{
    protected Database $db;

    public function getParentsBatch(array $ids): array
    {
        return $this->db->fetchCol(
            $this->getParentsBatchSelect($ids),
            'parent_id',
            'parent_id',
        );
    }

    protected function getParentsBatchSelect(array $ids): Select
    {
        $select = $this->db->select('catalog_product_relation');
        $select->where->in('child_id', $ids);

        return $select;
    }

    public function getProductIdsBySkus(array $skus): array
    {
        return $this->db->fetchCol(
            $this->getProductIdsBySkusSelect($skus),
            'entity_id',
        );
    }

    protected function getProductIdsBySkusSelect(array $skus): Select
    {
        $select = $this->db->select(['p' => 'catalog_product_entity']);
        $select->where->in('sku', $skus);

        return $select;
    }
}
