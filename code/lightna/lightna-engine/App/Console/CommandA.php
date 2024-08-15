<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console;

use Lightna\Engine\App\ObjectA;

class CommandA extends ObjectA
{
    protected function printStart($text): void
    {
        echo str_pad($text, 30, '.');
    }

    protected function printEnd(): void
    {
        echo ' ok' . "\n";
    }
}
