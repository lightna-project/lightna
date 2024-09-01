<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Store extends ObjectA
{
    protected Database $db;
    protected array $stores;

    protected function defineStores(): void
    {
        $this->stores = $this->db->fetch(
            $this->db->select('store'),
            'store_id',
        );
    }

    public function getList(): array
    {
        return $this->stores;
    }

    public function get(string|int $id): array
    {
        return $this->stores[$id];
    }
}
