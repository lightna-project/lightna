<?php

declare(strict_types=1);

namespace Lightna\Magento\Staging\App\Plugin\Index\Changelog;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Staging\App\Staging;

class Handler extends ObjectA
{
    protected Staging $staging;

    /**
     * @see          \Lightna\Engine\App\Index\Changelog\Handler::process()
     * @noinspection PhpUnused
     */
    public function processExtended(Closure $proceed): void
    {
        $this->staging->applyNewVersion();
        $proceed();
    }
}
