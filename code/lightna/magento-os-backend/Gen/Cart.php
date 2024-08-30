<?php

declare(strict_types=1);

namespace Lightna\Magento\Gen;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Database;
use Lightna\Engine\App\ObjectA;
use function array_camel;

class Cart extends ObjectA
{
    protected Database $db;

    public function getData(string|int $id): array
    {
        if (
            (!$quoteRow = $this->getQuote($id))
            || $quoteRow['is_active'] !== 1
            || !$itemRows = $this->getQuoteItems($id)
        ) {
            return [];
        }

        $items = [];
        $itemFields = ['product_id', 'sku', 'name', 'qty', 'price'];
        foreach ($itemRows as $i => $row) {
            $items[$i] = array_intersect_key($row, array_flip($itemFields));
            $items[$i]['qty'] += 0;
        }

        return array_camel([
            'qty' => (int)$quoteRow['items_qty'],
            'grand_total' => (float)$quoteRow['grand_total'],
            'items' => $items,
        ]);
    }

    protected function getQuote(string|int $id): ?array
    {
        return $this->db->fetchOne($this->getQuoteSelect($id));
    }

    protected function getQuoteSelect(string|int $id): Select
    {
        return $this->db
            ->select('quote')
            ->where(['entity_id = ?' => $id]);
    }

    protected function getQuoteItems(string|int $id): array
    {
        return $this->db->fetch($this->getQuoteItemsSelect($id), 'item_id');
    }

    protected function getQuoteItemsSelect(string|int $id): Select
    {
        $select = $this->db
            ->select('quote_item')
            ->order('item_id');
        $select->where(['quote_id = ?' => $id])
            ->where->isNull('parent_item_id');

        return $select;
    }
}
