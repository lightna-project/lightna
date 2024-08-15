<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog;

use Lightna\Engine\App\Index\Changelog\BatchHandlerAbstract;

class StockBatchHandler extends BatchHandlerAbstract
{
    public function handle(string $table, array $changelog): array
    {
        if ($table === 'cataloginventory_stock_item') {
            return [
                'product' => $this->collectIds($changelog, 'product_id'),
            ];
        }

        return [];
    }
}
