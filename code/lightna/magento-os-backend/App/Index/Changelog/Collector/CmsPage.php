<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\Collect;
use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;

class CmsPage extends ObjectA implements CollectorInterface
{
    protected Collect $collect;

    public function collect(string $table, array $changelog): array
    {
        if (str_starts_with($table, 'cms_page')) {
            return [
                'cms' => $this->collect->entityIds($table, $changelog),
            ];
        }

        return [];
    }
}
