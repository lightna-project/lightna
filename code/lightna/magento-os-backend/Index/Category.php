<?php

declare(strict_types=1);

namespace Lightna\Magento\Index;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Database;
use Lightna\Engine\App\Index\IndexAbstract;
use Lightna\Magento\App\Entity\Category as CategoryEntity;
use Lightna\Magento\App\Query\Categories;

class Category extends IndexAbstract
{
    protected CategoryEntity $entity;
    protected Database $db;
    protected Categories $categories;

    public function getBatchSelect(array $ids): Select
    {
        return $this->categories->getListSelect(['image', 'description']);
    }

    public function getBatchData(array $ids): array
    {
        return $this->db->fetch($this->getBatchSelect($ids), 'entity_id');
    }

    public function scan(string|int $lastId = null): array
    {
        if ($lastId) {
            // Scan is done in a single batch
            return [];
        }

        return $this->db->fetchCol($this->getScanSelect());
    }

    protected function getScanSelect(): Select
    {
        return $this->categories->getListSelect()->columns(['entity_id']);
    }
}
