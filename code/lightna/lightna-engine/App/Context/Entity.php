<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Context;

use Lightna\Engine\Data\DataA;

class Entity extends DataA
{
    public string $type = 'page';
    public string|int|null $id;
}
