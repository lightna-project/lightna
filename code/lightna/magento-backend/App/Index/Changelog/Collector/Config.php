<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;

class Config extends ObjectA implements CollectorInterface
{
    public function collect(string $table, array $changelog): array
    {
        if ($table === 'core_config_data') {
            return ['config' => [1]];
        }

        return [];
    }
}
