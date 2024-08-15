<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog;

use Lightna\Engine\App\Index\Changelog\BatchHandlerAbstract;

class CmsPageBatchHandler extends BatchHandlerAbstract
{
    public function handle(string $table, array $changelog): array
    {
        if (str_starts_with($table, 'cms_page')) {
            return [
                'cms' => $this->collectIds($changelog, 'page_id'),
            ];
        }

        return [];
    }
}
