<?php

declare(strict_types=1);

namespace Lightna\Magento\Demo\Plugin\App;

use Closure;
use Lightna\Engine\App\ObjectA;

class Layout extends ObjectA
{
    public function renderPageExtended(Closure $proceed): void
    {
        $proceed();
        template('server-time.phtml');
    }
}
