<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Index;

use Lightna\Engine\App\ObjectA;

class EntityLink extends ObjectA
{
    public function getColumn(string $table): string
    {
        // Extension point
        return match (true) {
            str_starts_with($table, 'cms_page') => 'page_id',
            str_starts_with($table, 'cms_block') => 'block_id',
            default => 'entity_id',
        };
    }

    public function getIds(string $table, array $ids): array
    {
        // Extension point
        return $ids;
    }
}
