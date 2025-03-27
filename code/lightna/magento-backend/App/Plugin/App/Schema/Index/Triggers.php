<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Plugin\App\Schema\Index;

use Closure;
use Lightna\Engine\App\ObjectA;

class Triggers extends ObjectA
{
    /**
     * @see          \Lightna\Engine\App\Schema\Index\Triggers::getTableAlias()
     * @noinspection PhpUnused
     */
    public function getTableAliasExtended(Closure $proceed, string $table): string
    {
        return preg_replace('~_replica$~', '', $table);
    }
}
