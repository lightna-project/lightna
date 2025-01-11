<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query\Inventory;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Ssi extends ObjectA
{
    protected Database $db;

    public function getBatch(array $productIds): array
    {
        return $this->db->fetch($this->getBatchSelect($productIds), 'product_id');
    }

    protected function getBatchSelect(array $productIds): Select
    {
        $select = $this->db->select()
            ->from(['i' => 'cataloginventory_stock_item'])
            ->columns(['product_id', 'qty', 'status' => 'is_in_stock', 'backorders']);
        $select->where->in('i.product_id', $productIds);

        return $select;
    }
}
