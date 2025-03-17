<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;

class Attributes extends ObjectA implements CollectorInterface
{
    protected array $watchedTables = ['eav_attribute', 'catalog_eav_attribute'];

    public function collect(string $table, array $changelog): array
    {
        if (in_array($table, $this->watchedTables)) {
            return [
                'content_product' => [1], // Update visible attributes
                'content_category' => [1], // Update filterable attributes
            ];
        }

        return [];
    }
}
