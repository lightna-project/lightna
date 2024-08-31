<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Plugin\App;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Database;
use Lightna\Engine\App\ObjectA;

class Scope extends ObjectA
{
    protected Database $db;
    protected array $list;

    protected function defineList(): void
    {
        $this->list = $this->db->fetchCol($this->getListSelect());
    }

    public function getListExtended(): array
    {
        return $this->list;
    }

    protected function getListSelect(): Select
    {
        return $this->db
            ->select('store')
            ->columns(['store_id'])
            ->where('store_id > 0');
    }
}
