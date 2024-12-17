<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\Collect;
use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;

class Stock extends ObjectA implements CollectorInterface
{
    protected Collect $collect;
    /** @AppConfig(backend:indexer/inventory/ignore_qty_change) */
    protected bool $ignoreQtyChange;

    public function collect(string $table, array $changelog): array
    {
        if ($table === 'cataloginventory_stock_item') {
            $ignore = $this->ignoreQtyChange ? ['qty'] : [];

            return [
                'product' => $this->collect->idsWithIgnore($changelog, 'product_id', $ignore),
            ];
        }

        return [];
    }
}
