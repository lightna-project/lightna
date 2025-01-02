<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console;

use Lightna\Engine\App\Config as AppConfig;
use Lightna\Engine\App\UserException;

class Config extends CommandA
{
    protected AppConfig $config;

    public function run(): void
    {
        if (!$this->getArgs()) {
            throw new UserException('Specify config patch');
        }

        $path = str_replace('.', '/', $this->getArgs()[0]);
        $value = $this->config->get($path);

        echo is_array($value) ? json_pretty($value) : $value;
    }
}
