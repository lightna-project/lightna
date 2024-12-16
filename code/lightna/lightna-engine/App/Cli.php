<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;

class Cli extends ObjectA
{
    /** @AppConfig(backend:cli/command) */
    protected array $commands;

    public function run(string $command): void
    {
        if (!isset($this->commands[$command])) {
            throw new Exception('Command "' . $command . '" not defined');
        }

        getobj($this->commands[$command])->run();
    }
}
