<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog;

use Lightna\Engine\App\Index\Changelog\BatchHandlerAbstract;

class CategoryBatchHandler extends BatchHandlerAbstract
{
    public function handle(string $table, array $changelog): array
    {
        if (str_starts_with($table, 'catalog_category_entity')) {
            return [
                'category' => $this->collectIds($changelog, 'entity_id'),
                'content_page' => [1], // Update Top Menu
            ];
        }

        return [];
    }
}
