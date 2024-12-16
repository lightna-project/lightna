<?php

declare(strict_types=1);

namespace Lightna\Engine\App\State\Index;

use Lightna\Engine\Data\DataA;

class Entity extends DataA
{
    public ?float $invalidatedAt = null;
    public ?float $rebuiltAt = null;

    public function isUpToDate(): bool
    {
        return is_null($this->invalidatedAt) || $this->rebuiltAt > $this->invalidatedAt;
    }
}
