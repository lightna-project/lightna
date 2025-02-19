<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query\Customer;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Group extends ObjectA
{
    protected Database $db;
    protected array $groups;

    /** @noinspection PhpUnused */
    protected function defineGroups(): void
    {
        $this->groups = $this->db->fetch(
            $this->getListSelect(),
            'customer_group_id',
        );
    }

    protected function getListSelect(): Select
    {
        return $this->db->select()
            ->from('customer_group');
    }

    public function getList(): array
    {
        return $this->groups;
    }
}
