<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Quote extends ObjectA
{
    protected Database $db;

    public function get(int|string $id): ?array
    {
        return $this->db->fetchOne($this->getQuoteSelect($id));
    }

    protected function getQuoteSelect(string|int $id): Select
    {
        return $this->db
            ->select('quote')
            ->where(['entity_id = ?' => $id]);
    }

    public function getItems(string|int $id): array
    {
        return $this->db->fetch($this->getItemsSelect($id), 'item_id');
    }

    protected function getItemsSelect(string|int $id): Select
    {
        $select = $this->db
            ->select('quote_item')
            ->order('item_id');

        $select->where(['quote_id = ?' => $id])
            ->where->isNull('parent_item_id');

        return $select;
    }
}
