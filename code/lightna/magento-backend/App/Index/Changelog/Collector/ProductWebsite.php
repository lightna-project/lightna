<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\Collect;
use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;

class ProductWebsite extends ObjectA implements CollectorInterface
{
    protected Collect $collect;

    public function collect(string $table, array $changelog): array
    {
        if ($table === 'catalog_product_website') {
            return [
                'product' => $this->collect->ids($changelog, 'product_id'),
            ];
        }

        return [];
    }
}
