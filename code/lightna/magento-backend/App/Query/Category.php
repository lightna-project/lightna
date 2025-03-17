<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query;

use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\Backend\App\Query\Category\Eav;

class Category extends ObjectA
{
    protected Database $db;
    protected Eav $eav;
    protected Context $context;
    protected Store $store;
    protected array $rootId;

    public function getRootId(): int
    {
        $scope = $this->context->scope;
        if (!isset($this->rootId[$scope])) {
            $this->loadRootId($scope);
        }

        return $this->rootId[$scope];
    }

    protected function loadRootId(int $scope): void
    {
        $this->rootId[$scope] = $this->db->fetchOneCol($this->getRootIdSelect());
    }

    protected function getRootIdSelect(): Select
    {
        return $this->db
            ->select('store_group')
            ->columns(['root_category_id'])
            ->where(['group_id = ?' => $this->store->getGroupId()]);
    }

    public function getNavigation(): array
    {
        $list = $this->db->fetch($this->getNavigationSelect(), 'entity_id');
        $ids = array_keys($list);

        return merge(
            $list,
            $this->eav->getAttributeValues($ids, []),
        );
    }

    protected function getNavigationSelect(): Select
    {
        $select = $this->getAllSelect()
            ->order('e.position');

        $isActive = $this->eav->joinAttribute('is_active', $select);
        $includeInMenu = $this->eav->joinAttribute('include_in_menu', $select);

        $select->where("$isActive = 1 and $includeInMenu = 1");
        $this->eav->joinUrl($select);

        return $select;
    }

    public function getAllAccessible(): array
    {
        return $this->db->fetch($this->getAllAccessibleSelect(), 'entity_id');
    }

    protected function getAllAccessibleSelect(): Select
    {
        $select = $this->getAllSelect();

        $isActive = $this->eav->joinAttribute('is_active', $select);
        $this->eav->joinAttribute('name', $select);
        $this->eav->joinUrl($select);
        $select->where("$isActive = 1");

        return $select;
    }

    public function getBatch(array $ids): array
    {
        $result = merge(
            $this->db->fetch($this->getBatchSelect($ids), 'entity_id'),
            $this->eav->getAttributeValues($ids, []),
        );

        unset($result[1]);
        unset($result[$this->getRootId()]);

        return $result;
    }

    protected function getBatchSelect(array $ids): Select
    {
        $select = $this->getAllSelect();
        $select->where->in('e.entity_id', $ids);

        return $select;
    }

    public function getAllSelect(): Select
    {
        return $this->db
            ->select(['e' => 'catalog_category_entity'])
            ->columns(['entity_id', 'parent_id'])
            ->where([
                'path like ?' => '1/' . $this->getRootId() . '/%',
                'e.entity_id != 1',
                'e.entity_id != ?' => $this->getRootId(),
            ]);
    }
}
