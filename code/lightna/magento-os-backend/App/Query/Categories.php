<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Query\Category\Eav;

class Categories extends ObjectA
{
    protected Database $db;
    protected Eav $eav;
    protected Context $context;
    protected Store $store;
    protected int $rootId;

    protected function init(): void
    {
        $this->initRootId();
    }

    protected function initRootId(): void
    {
        $select = $this->db
            ->select('store_group')
            ->columns(['root_category_id'])
            ->where(['group_id = ?' => $this->store->get($this->context->scope)['group_id']]);

        $this->rootId = $this->db->fetchOneCol($select);
    }

    public function getRootId(): int
    {
        return $this->rootId;
    }

    public function getList(): array
    {
        $list = $this->db->fetch($this->getListSelect(), 'entity_id');
        foreach ($list as &$item) {
            $isRelativeUrl = !preg_match('~^(https?://|/)~', $item['url']);
            $item['url'] = $isRelativeUrl ? '/' . $item['url'] : $item['url'];
        }

        return $list;
    }

    public function getListSelect(array $additionalAttributeCodes = []): Select
    {
        $select = $this->db
            ->select(['e' => 'catalog_category_entity'])
            ->columns(['entity_id', 'parent_id'])
            ->where(['path like ?' => '1/' . $this->rootId . '/%'])
            ->order('e.position');

        $attrs = ['name', ...$additionalAttributeCodes];
        foreach ($attrs as $code) {
            $this->eav->joinAttribute($code, $select);
        }

        $isActive = $this->eav->joinAttribute('is_active', $select);
        $includeInMenu = $this->eav->joinAttribute('include_in_menu', $select);
        $select->where("$isActive = 1 and $includeInMenu = 1");

        $select->join(
            ['u' => 'url_rewrite'],
            new Expression(
                'u.entity_id = e.entity_id and u.entity_type = "category" and u.store_id = ?',
                $this->context->scope,
            ),
            ['url' => 'request_path'],
        );

        return $select;
    }
}
