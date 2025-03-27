<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console;

use Lightna\Engine\App\Config as AppConfig;

class Config extends CommandA
{
    protected AppConfig $config;

    public function run(): void
    {
        $path = str_replace('.', '/', $this->getArgs()[0]);
        $value = $this->config->get($path);

        echo is_array($value) ? json_pretty($value) : $value;
    }
}
