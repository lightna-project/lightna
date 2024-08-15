<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog;

use Lightna\Engine\App\Index\Changelog\BatchHandlerAbstract;

class ConfigBatchHandler extends BatchHandlerAbstract
{
    public function handle(string $table, array $changelog): array
    {
        if ($table === 'core_config_data') {
            return ['config' => [1]];
        }

        return [];
    }
}
