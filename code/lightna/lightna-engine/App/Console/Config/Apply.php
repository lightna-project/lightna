<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Config;

use Lightna\Engine\App\Build\Config;
use Lightna\Engine\App\Console\CommandA;

class Apply extends CommandA
{
    protected Config $config;

    public function run(): void
    {
        $this->config->apply();
    }
}
