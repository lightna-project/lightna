<?php

declare(strict_types=1);

namespace Lightna\Engine\App\State;

use Lightna\Engine\App\ObjectA;

class Index extends ObjectA
{
    public string $version;

    /** @noinspection PhpUnused */
    protected function defineVersion(): void
    {
        $this->version = 'a';
    }

    public function getNewVersion(): string
    {
        return chr((ord($this->version) - ord('a') + 1) % 26 + ord('a'));
    }
}
