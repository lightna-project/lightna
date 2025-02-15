<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Product extends ObjectA
{
    protected Store $store;
    protected Database $db;
    protected array $availableTypes = [
        // Composite products must be at the end
        'simple', 'virtual', 'downloadable', 'configurable'
    ];
    protected array $compositeTypes = ['configurable'];

    public function getParentsBatch(array $ids): array
    {
        return $this->db->fetchCol(
            $this->getParentsBatchSelect($ids),
            'parent_id',
            'parent_id',
        );
    }

    protected function getParentsBatchSelect(array $ids): Select
    {
        $select = $this->db->select('catalog_product_relation');
        $select->where->in('child_id', $ids);

        return $select;
    }

    public function getProductIdsBySkus(array $skus): array
    {
        return $this->db->fetchCol(
            $this->getProductIdsBySkusSelect($skus),
            'entity_id',
        );
    }

    protected function getProductIdsBySkusSelect(array $skus): Select
    {
        $select = $this->db->select(['p' => 'catalog_product_entity']);
        $select->where->in('sku', $skus);

        return $select;
    }

    public function getAvailableIdsBatch(int $limit, int $afterId = null): array
    {
        return $this->db->fetchCol($this->getAvailableIdsBatchSelect($limit, $afterId));
    }

    protected function getAvailableIdsBatchSelect(int $limit, int $afterId = null): Select
    {
        $select = $this->getAvailableIdsSelectTemplate()
            ->order('e.entity_id')
            ->limit($limit);

        $afterId && $select->where(['e.entity_id > ?' => $afterId]);

        return $select;
    }

    protected function getAvailableIdsSelectTemplate(): Select
    {
        return $this->getBatchSelectTemplate()
            ->columns(['entity_id'])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->join(
                ['price' => 'catalog_product_index_price'],
                'price.entity_id = e.entity_id AND price.website_id = pw.website_id',
                []
            );
    }

    protected function getBatchSelectTemplate(): Select
    {
        $select = $this->db
            ->select(['e' => 'catalog_product_entity'])
            ->join(
                ['pw' => 'catalog_product_website'],
                'pw.product_id = e.entity_id',
                []
            )
            ->where(['pw.website_id = ?' => $this->store->getWebsiteId()]);

        $this->applyTypesFilter($select);

        return $select;
    }

    protected function applyTypesFilter(Select $select): void
    {
        $select->where->in('e.type_id', $this->availableTypes);
    }

    public function getAvailableIds(array $ids): array
    {
        return $this->db->fetchCol($this->getAvailableIdsSelect($ids));
    }

    protected function getAvailableIdsSelect(array $ids): Select
    {
        $select = $this->getAvailableIdsSelectTemplate();
        $select->where->in('e.entity_id', $ids);

        return $select;
    }

    public function getBatch(array $ids): array
    {
        return $this->db->fetch($this->getBatchSelect($ids), 'entity_id');
    }

    protected function getBatchSelect(array $ids): Select
    {
        $select = $this->getBatchSelectTemplate()
            ->columns(['entity_id', 'attribute_set_id', 'type_id', 'sku'])
            ->order(new Expression('field(e.type_id, ' . $this->getAllowedTypesListExpr() . ')'));

        $select->where->in('e.entity_id', $ids);

        return $select;
    }

    protected function getAllowedTypesListExpr(): string
    {
        return implode(', ', array_map([$this->db, 'quote'], $this->availableTypes));
    }

    public function getAvailableTypes(): array
    {
        return $this->availableTypes;
    }

    public function getChildrenRelations(array $ids): array
    {
        return $this->db->fetch($this->getChildrenRelationsSelect($ids));
    }

    protected function getChildrenRelationsSelect(array $ids): Select
    {
        $select = $this->db->select()
            ->from(['rel' => 'catalog_product_relation'])
            ->join(
                ['p' => 'catalog_product_entity'],
                'rel.parent_id = p.entity_id',
                [],
            );

        $select->where
            ->in('rel.parent_id', $ids)
            // Filter by composite types to fix issues when a bit messy in database
            ->in('p.type_id', $this->compositeTypes);

        return $select;
    }

    public function getPrices(array $ids): array
    {
        return $this->db->fetch($this->getPricesSelect($ids));
    }

    protected function getPricesSelect(array $ids): Select
    {
        $select = $this->db
            ->select('catalog_product_index_price')
            ->where(['website_id = ?' => $this->store->getWebsiteId()])
            ->order(['entity_id', 'customer_group_id']);
        $select->where->in('entity_id', $ids);

        return $select;
    }

    public function getConfigurableOptions(array $ids): array
    {
        return $this->db->fetch($this->getConfigurableOptionsSelect($ids));
    }

    protected function getConfigurableOptionsSelect(array $ids): Select
    {
        $select = $this->db
            ->select(['o' => 'catalog_product_super_attribute'])
            ->columns(['product_id', 'attribute_id'])
            ->join(
                ['a' => 'eav_attribute'],
                'o.attribute_id = a.attribute_id',
                ['code' => 'attribute_code', 'label' => 'frontend_label']
            )
            ->order(['o.product_id', 'o.position']);

        $select->where->in('o.product_id', $ids);

        return $select;
    }
}
