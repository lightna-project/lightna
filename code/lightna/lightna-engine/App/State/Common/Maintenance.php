<?php

declare(strict_types=1);

namespace Lightna\Engine\App\State\Common;

use Lightna\Engine\Data\DataA;

class Maintenance extends DataA
{
    public bool $enabled;

    /** @noinspection PhpUnused */
    protected function defineEnabled(): void
    {
        $this->enabled = false;
    }
}
