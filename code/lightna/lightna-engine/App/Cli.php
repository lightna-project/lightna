<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class Cli extends ObjectA
{
    /** @AppConfig(cli/command) */
    protected array $cliCommands;
    protected array $commands;

    /** @noinspection PhpUnused */
    protected function defineCommands(): void
    {
        $this->commands = array_flat($this->cliCommands);
    }

    public function run(string $command): void
    {
        if (!isset($this->commands[$command])) {
            throw new \Exception('Command "' . $command . '" not defined');
        }

        getobj($this->commands[$command])->run();
    }
}
