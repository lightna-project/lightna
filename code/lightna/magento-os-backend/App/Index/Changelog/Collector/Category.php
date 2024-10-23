<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\Collect;
use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;

class Category extends ObjectA implements CollectorInterface
{
    protected Collect $collect;

    public function collect(string $table, array $changelog): array
    {
        if (str_starts_with($table, 'catalog_category_entity')) {
            return [
                'category' => $this->collect->entityIds($table, $changelog),
                'content_page' => [1], // Update Top Menu
            ];
        }

        return [];
    }
}
