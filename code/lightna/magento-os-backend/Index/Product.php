<?php

declare(strict_types=1);

namespace Lightna\Magento\Index;

use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\Index\IndexAbstract;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Entity\Product as ProductEntity;
use Lightna\Magento\App\Query\Store;
use Lightna\Magento\App\Query\Url;
use Lightna\Magento\Index\Product\Batch as BatchDataProvider;

class Product extends IndexAbstract
{
    protected ProductEntity $entity;
    protected Database $db;
    protected Context $context;
    protected Store $store;
    protected Url $url;
    protected bool $hasRoutes = true;

    public function getDataBatch(array $ids): array
    {
        return $this->getDataProvider()->getData($ids);
    }

    public function getRoutesBatch(array $ids): array
    {
        return $this->url->getEntityRoutesBatch('product', $ids);
    }

    protected function getDataProvider(): BatchDataProvider
    {
        return newobj(BatchDataProvider::class);
    }

    public function scan(string|int $lastId = null): array
    {
        return $this->db->fetchCol($this->getScanSelect($lastId));
    }

    protected function getScanSelect(string|int $lastId = null): Select
    {
        $select = $this->getMainSelect()
            ->columns(['entity_id'])
            ->order('entity_id')
            ->limit(1000);

        $lastId && $select->where(['entity_id > ?' => $lastId]);

        return $select;
    }

    public function getBatchSelect(array $ids): Select
    {
        $select = $this->getMainSelect()
            ->columns(['entity_id', 'attribute_set_id', 'type_id', 'sku']);
        $select->where->in('e.entity_id', $ids);

        // Composite products must be at the end
        $select->order(new Expression('field(e.type_id, "simple", "virtual", "downloadable", "configurable")'));

        $select->join(
            ['i' => 'cataloginventory_stock_item'],
            'i.product_id = e.entity_id',
            ['backorders'],
            Select::JOIN_LEFT
        );

        return $select;
    }

    protected function getMainSelect(): Select
    {
        $websiteId = $this->store->get($this->context->scope)['website_id'];

        $select = $this->db
            ->select(['e' => 'catalog_product_entity'])
            ->join(
                ['pw' => 'catalog_product_website'],
                'pw.product_id = e.entity_id',
                []
            )
            ->where(['pw.website_id = ?' => $websiteId]);

        $select->where->in('e.type_id', ['simple', 'configurable', 'virtual', 'downloadable']);

        return $select;
    }
}
