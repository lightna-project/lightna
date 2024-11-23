<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console;

use Lightna\Engine\App\ObjectA;

class CommandA extends ObjectA
{
    protected array $args;
    protected array $opts;

    /** @noinspection PhpUnused */
    protected function defineArgs(): void
    {
        $this->args = $this->opts = [];
        $args = array_slice($GLOBALS['argv'], 2);
        foreach ($args as $arg) {
            if (str_starts_with($arg, '-')) {
                $parts = explode('=', ltrim($arg, '-'));
                $this->opts[$parts[0]] = $parts[1] ?? true;
            } else {
                $this->args[] = $arg;
            }
        }
    }

    /** @noinspection PhpUnused */
    protected function defineOpts(): void
    {
        $this->defineArgs();
    }

    protected function getArg(string|int $nameOrNumber): string|bool|null
    {
        if (is_string($nameOrNumber)) {
            return in_array($nameOrNumber, $this->args);
        } else {
            return $this->args[$nameOrNumber - 1] ?? null;
        }
    }

    protected function getArgs(): array
    {
        return $this->args;
    }

    protected function getOpt(string|array $name): mixed
    {
        foreach ((array)$name as $name) {
            if (isset($this->opts[$name])) {
                return $this->opts[$name];
            }
        }

        return null;
    }

    protected function printStart($text): void
    {
        echo str_pad($text, 30, '.');
    }

    protected function printEnd(string $additional = ''): void
    {
        echo ' ok';
        $additional !== '' && print(" ($additional)");
        echo "\n";
    }
}
