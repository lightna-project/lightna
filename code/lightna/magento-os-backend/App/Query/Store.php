<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Store extends ObjectA
{
    protected Database $db;
    protected array $stores;

    /** @noinspection PhpUnused */
    protected function defineStores(): void
    {
        $this->stores = $this->db->fetch(
            $this->getListSelect(),
            'store_id',
        );
    }

    protected function getListSelect(): Select
    {
        return $this->db->select()
            ->from('store')
            ->where('store_id > 0');
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
