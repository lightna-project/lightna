<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Plugin\App;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Scope extends ObjectA
{
    protected Database $db;
    protected array $list;

    /** @noinspection PhpUnused */
    protected function defineList(): void
    {
        $this->list = $this->db->fetchCol($this->getListSelect());
    }

    /** @noinspection PhpUnused */
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
