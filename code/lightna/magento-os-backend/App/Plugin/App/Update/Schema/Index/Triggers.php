<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Plugin\App\Update\Schema\Index;

use Closure;
use Lightna\Engine\App\ObjectA;

class Triggers extends ObjectA
{
    public function getTableAliasExtended(Closure $proceed, string $table): string
    {
        return preg_replace('~_replica$~', '', $table);
    }
}