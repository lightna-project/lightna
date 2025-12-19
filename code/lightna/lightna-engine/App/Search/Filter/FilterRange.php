<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Search\Filter;

class FilterRange extends FilterAbstract
{
    public ?float $from=null;
    public ?float $to=null;
}
