<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog;

use Lightna\Engine\App\Database;
use Lightna\Engine\App\Index\Changelog\BatchHandlerAbstract;

class ProductBatchHandler extends BatchHandlerAbstract
{
    protected string $table;
    protected array $changelog;
    protected array $toQueue;
    protected Database $db;

    public function handle(string $table, array $changelog): array
    {
        $this->table = $table;
        $this->changelog = $changelog;
        $this->toQueue = ['product' => []];

        $this->collectDefault();
        $this->collectProductRelation();
        $this->collectSuperAttribute();
        $this->collectParents();

        return $this->toQueue;
    }

    protected function collectDefault(): void
    {
        if (str_starts_with($this->table, 'catalog_product_entity') || $this->table === 'catalog_product_index_price') {
            $this->toQueue['product'] = merge(
                $this->collectIds($this->changelog, 'entity_id'),
                $this->toQueue['product'],
            );
        }
    }

    protected function collectProductRelation(): void
    {
        if ($this->table === 'catalog_product_relation') {
            $this->toQueue['product'] = merge(
                $this->toQueue['product'],
                $this->collectIds($this->changelog, 'parent_id'),
            );
        }
    }

    protected function collectSuperAttribute(): void
    {
        if ($this->table === 'catalog_product_super_attribute') {
            $this->toQueue['product'] = merge(
                $this->toQueue['product'],
                $this->collectIds($this->changelog, 'product_id'),
            );
        }
    }

    protected function collectParents(): void
    {
        if (empty($this->toQueue['product'])) {
            return;
        }

        $select = $this->db->select('catalog_product_relation');
        $select->where->in('child_id', $this->toQueue['product']);

        $this->toQueue['product'] = merge(
            $this->toQueue['product'],
            $this->db->fetchCol($select, 'parent_id', 'parent_id'),
        );
    }
}
