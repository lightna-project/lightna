<?php

declare(strict_types=1);

namespace Lightna\Magento\AdobeStaging\App\Plugin\Index\Changelog;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\AdobeStaging\App\Staging;

class Handler extends ObjectA
{
    protected Staging $staging;

    public function processExtended(Closure $proceed): void
    {
        $this->staging->applyNewVersion();
        $proceed();
    }
}
