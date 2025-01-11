<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query\Cms;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Page extends ObjectA
{
    protected Database $db;
    protected Context $context;

    public function getByIdentifier(string $identifier): ?array
    {
        $rows = $this->db->fetch($this->getByIdentifierSelect($identifier));

        return end($rows);
    }

    protected function getByIdentifierSelect(string $identifier): Select
    {
        $select = $this->db
            ->select(['c' => 'cms_page'])
            ->join(
                ['s' => 'cms_page_store'],
                's.page_id = c.page_id',
                [],
            )
            ->where(['c.is_active = 1 and identifier = ?' => $identifier])
            // 0 (default) first
            ->order('s.store_id');

        $select->where->in('s.store_id', [0, $this->context->scope]);

        return $select;
    }
}
