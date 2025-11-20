<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query\Product;

use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Lightna\Magento\Backend\App\Query\EavAbstract;

class Eav extends EavAbstract
{
    public const ENTITY_TYPE = 4;
    public const ENTITY_TABLE = 'catalog_product_entity';
    protected array $visibleOnFrontAttributes;
    protected array $filterableAttributes;

    public function getVisibleOnFrontAttributes(): array
    {
        return $this->visibleOnFrontAttributes;
    }

    public function getFilterableAttributes(): array
    {
        return $this->filterableAttributes;
    }

    /** @noinspection PhpUnused */
    protected function defineVisibleOnFrontAttributes(): void
    {
        $this->visibleOnFrontAttributes = $this->db->fetch($this->getVisibleOnFrontAttributesSelect());
    }

    /** @noinspection PhpUnused */
    protected function defineFilterableAttributes(): void
    {
        $this->filterableAttributes = $this->db->fetch($this->getFilterableAttributesSelect(), 'code');
    }

    protected function getVisibleOnFrontAttributesSelect(): Select
    {
        return $this->getProductAttributesSelect()
            ->where('ca.is_visible_on_front = 1');
    }

    protected function getFilterableAttributesSelect(): Select
    {
        return $this->getProductAttributesSelect()
            ->where('ca.is_filterable = 1');
    }

    protected function getProductAttributesSelect(): Select
    {
        return $this->db
            ->select(['ca' => 'catalog_eav_attribute'])
            ->columns([])
            ->join(
                ['a' => 'eav_attribute'],
                'a.attribute_id = ca.attribute_id',
                ['id' => 'attribute_id', 'code' => 'attribute_code', 'backend_type', 'frontend_input'],
            )
            ->join(
                ['l' => 'eav_attribute_label'],
                'l.attribute_id = ca.attribute_id',
                ['label' => new Expression('ifnull(l.value, a.frontend_label)')],
                Select::JOIN_LEFT
            )
            ->where(['(l.store_id is null or l.store_id = ?)' => $this->context->scope]);
    }
}
