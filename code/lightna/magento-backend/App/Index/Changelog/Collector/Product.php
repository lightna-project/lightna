<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\App\Index\Changelog\Collect;

class Product extends ObjectA implements CollectorInterface
{
    protected Collect $collect;
    protected string $table;
    protected array $changelog;
    protected array $toQueue;

    public function collect(string $table, array $changelog): array
    {
        $this->table = $table;
        $this->changelog = $changelog;
        $this->toQueue = ['product' => []];

        $this->collectDefault();
        $this->collectProductRelation();
        $this->collectSuperAttribute();

        return $this->toQueue;
    }

    protected function collectDefault(): void
    {
        if (
            str_starts_with($this->table, 'catalog_product_entity')
            || $this->table === 'catalog_product_index_price'
        ) {
            $this->toQueue['product'] = merge(
                $this->collect->entityIds($this->table, $this->changelog),
                $this->toQueue['product'],
            );
        }
    }

    protected function collectProductRelation(): void
    {
        if ($this->table === 'catalog_product_relation') {
            $this->toQueue['product'] = merge(
                $this->toQueue['product'],
                $this->collect->ids($this->changelog, 'parent_id'),
            );
        }
    }

    protected function collectSuperAttribute(): void
    {
        if ($this->table === 'catalog_product_super_attribute') {
            $this->toQueue['product'] = merge(
                $this->toQueue['product'],
                $this->collect->ids($this->changelog, 'product_id'),
            );
        }
    }
}
