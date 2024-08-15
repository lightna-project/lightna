<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog;

use Lightna\Engine\App\Index\Changelog\BatchHandlerAbstract;

class ProductWebsiteBatchHandler extends BatchHandlerAbstract
{
    public function handle(string $table, array $changelog): array
    {
        if ($table === 'catalog_product_website') {
            return [
                'product' => $this->collectIds($changelog, 'product_id'),
            ];
        }

        return [];
    }
}
