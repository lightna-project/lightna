<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Search\Filter;

use Lightna\Engine\Data\DataA;

abstract class FilterAbstract extends DataA
{
    public string $code;
    public bool $isFacetable = true;
}
