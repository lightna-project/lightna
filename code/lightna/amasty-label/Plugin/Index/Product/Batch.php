<?php

declare(strict_types=1);

namespace Lightna\AmastyLabel\Plugin\Index\Product;

use Closure;
use Lightna\AmastyLabel\App\Query\Label;
use Lightna\Engine\App\Database;
use Lightna\Engine\App\ObjectA;

class Batch extends ObjectA
{
    protected Database $db;
    protected Label $labelQuery;
    protected array $labelMeta;
    protected array $label;

    protected function defineLabelMeta(): void
    {
        $this->labelMeta = $this->labelQuery->getList();
    }

    public function loadDataExtended(): Closure
    {
        $loadData = function (array $ids) {
            $this->label = $this->labelQuery->getProductsBatch($ids);
        };

        return function ($proceed) use ($loadData) {
            $proceed();
            $loadData($this->allIds);
        };
    }

    public function collectProductDataExtended(Closure $proceed, &$product, $id): void
    {
        $proceed();

        if (!$labelId = $this->label[$id] ?? null) {
            return;
        }

        $label = $this->labelMeta[$labelId];
        $product['amasty_label'] = [
            'product' => [
                'image' => $label['product']['image'],
                'position' => $label['product']['position'],
            ],
            'category' => [
                'image' => $label['category']['image'],
                'position' => $label['category']['position'],
            ],
        ];
    }
}
