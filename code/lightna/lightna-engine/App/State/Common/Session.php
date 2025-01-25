<?php

declare(strict_types=1);

namespace Lightna\Engine\App\State\Common;

use Lightna\Engine\Data\DataA;

class Session extends DataA
{
    public int $version = 0;
}
