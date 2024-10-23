<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;

class CmsBlock extends ObjectA implements CollectorInterface
{
    public function collect(string $table, array $changelog): array
    {
        if (str_starts_with($table, 'cms_block')) {
            return [
                'content_page' => [1], // Update CMS blocks on all pages
                'content_product' => [1], // Update CMS blocks on product pages
            ];
        }

        return [];
    }
}
