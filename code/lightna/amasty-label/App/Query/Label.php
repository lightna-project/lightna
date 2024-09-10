<?php

declare(strict_types=1);

namespace Lightna\AmastyLabel\App\Query;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\Project\Database;
use Lightna\Engine\App\ObjectA;

class Label extends ObjectA
{
    protected Database $db;
    protected Context $context;

    public function getList(): array
    {
        $labels = [];
        foreach ($this->db->fetch($this->getListSelect()) as $row) {
            $type = $row['type'] === 1 ? 'category' : 'product';
            $labels[$row['label_id']][$type] = $row;
        }

        return $labels;
    }

    protected function getListSelect(): Select
    {
        return $this->db->select('amasty_label_catalog_parts');
    }

    public function getProductsBatch(array $ids): array
    {
        return $this->db->fetchCol($this->getProductsBatchSelect($ids), 'label_id', 'product_id');
    }

    protected function getProductsBatchSelect(array $ids): Select
    {
        $select = $this->db->select()
            ->from('amasty_label_index')
            ->columns(['product_id', 'label_id'])
            ->where(['store_id = ?' => $this->context->scope]);
        $select->where->in('product_id', $ids);

        return $select;
    }
}
