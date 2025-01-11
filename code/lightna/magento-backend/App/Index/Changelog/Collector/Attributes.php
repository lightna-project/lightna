<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;

class Attributes extends ObjectA implements CollectorInterface
{
    public function collect(string $table, array $changelog): array
    {
        if (str_contains($table, 'eav_attribute')) {
            return [
                'content_product' => [1], // Update visible attributes
                'content_category' => [1], // Update filterable attributes
            ];
        }

        return [];
    }
}
