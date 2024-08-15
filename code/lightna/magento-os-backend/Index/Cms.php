<?php

declare(strict_types=1);

namespace Lightna\Magento\Index;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Database;
use Lightna\Engine\App\Index\IndexAbstract;
use Lightna\Engine\Data\Context;
use Lightna\Magento\App\Entity\Cms as CmsEntity;

class Cms extends IndexAbstract
{
    protected CmsEntity $entity;
    protected Database $db;
    protected Context $context;

    public function getBatchData(array $ids): array
    {
        return $this->db->fetch($this->getBatchSelect($ids), 'page_id');
    }

    public function getBatchSelect(array $ids): Select
    {
        $select = $this->db
            ->select(['c' => 'cms_page'])
            ->where('c.is_active = 1')
            ->join(
                ['s' => 'cms_page_store'],
                's.page_id = c.page_id',
                [],
            )
            // 0 (default) first
            ->order('s.store_id');

        $select->where
            ->in('s.store_id', [0, $this->context->scope])
            ->in('c.page_id', $ids);

        return $select;
    }

    public function scan(string|int $lastId = null): array
    {
        return $this->db->fetchCol($this->getScanSelect($lastId));
    }

    protected function getScanSelect(string|int $lastId = null): Select
    {
        $select = $this->db
            ->select('cms_page')
            ->columns(['page_id'])
            ->where('is_active = 1')
            ->order('page_id')
            ->limit(1000);

        $lastId && $select->where(['page_id > ?' => $lastId]);

        return $select;
    }
}
