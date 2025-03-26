<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Wishlist extends ObjectA
{
    protected Database $db;
    protected Store $store;

    public function getItems(string|int $customerId): array
    {
        return $this->db->fetch($this->getItemsSelect($customerId), 'product_id');
    }

    protected function getItemsSelect(string|int $customerId): Select
    {
        return $this->db->select()
            ->from(['i' => 'wishlist_item'])
            ->columns(['id' => 'wishlist_item_id', 'product_id'])
            ->join(
                ['w' => 'wishlist'],
                'w.wishlist_id = i.wishlist_id',
                [],
            )
            ->where([
                'w.customer_id = ?' => $customerId,
                'i.store_id = ?' => $this->store->getId(),
            ]);
    }
}
