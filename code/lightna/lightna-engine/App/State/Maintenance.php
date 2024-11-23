<?php

declare(strict_types=1);

namespace Lightna\Engine\App\State;

use Lightna\Engine\App\ObjectA;

class Maintenance extends ObjectA
{
    public bool $enabled;

    /** @noinspection PhpUnused */
    protected function defineEnabled(): void
    {
        $this->enabled = false;
    }
}
