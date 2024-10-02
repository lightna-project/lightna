<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console;

use Lightna\Engine\App\ObjectA;

class CommandA extends ObjectA
{
    protected array $args;

    protected function defineArgs(): void
    {
        $this->args = [];
        $args = array_slice($GLOBALS['argv'], 2);
        foreach ($args as $arg) {
            $parts = explode('=', $arg);
            $this->args[$parts[0]] = $parts[1] ?? true;
        }
    }

    protected function getArg(string|array $name): mixed
    {
        $names = (array)$name;
        foreach ($names as $name) {
            if (isset($this->args[$name])) {
                return $this->args[$name];
            }
        }

        return null;
    }

    protected function hasCommands(): bool
    {
        foreach ($this->args as $name => $value) {
            if ($name[0] !== '-') {
                return true;
            }
        }

        return false;
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
