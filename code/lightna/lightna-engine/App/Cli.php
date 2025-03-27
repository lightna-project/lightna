<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Exception\CliInputException;

class Cli extends ObjectA
{
    /** @AppConfig(backend:cli/command) */
    protected array $commands;

    public function run(string $command): void
    {
        if (!isset($this->commands[$command])) {
            throw new CliInputException('Command "' . $command . '" not defined');
        }

        getobj($this->commands[$command])->run();
    }
}
