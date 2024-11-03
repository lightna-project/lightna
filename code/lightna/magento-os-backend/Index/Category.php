<?php

declare(strict_types=1);

namespace Lightna\Magento\Index;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Index\IndexAbstract;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Entity\Category as CategoryEntity;
use Lightna\Magento\App\Query\Categories;
use Lightna\Magento\App\Query\Url;

class Category extends IndexAbstract
{
    protected CategoryEntity $entity;
    protected Database $db;
    protected Categories $categories;
    protected Url $url;
    protected bool $hasRoutes = true;

    public function getDataBatch(array $ids): array
    {
        return $this->db->fetch($this->getBatchSelect($ids), 'entity_id');
    }

    public function getRoutesBatch(array $ids): array
    {
        return $this->url->getEntityRoutesBatch('category', $ids);
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

    public function getBatchSelect(array $ids): Select
    {
        return $this->categories->getListSelect(['image', 'description']);
    }
}
