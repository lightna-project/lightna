<?php

declare(strict_types=1);

namespace Lightna\Magento\Demo\App\Plugin;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\Data\Request;

class Router extends ObjectA
{
    protected Request $request;

    /** @noinspection PhpUnused */
    public function isBypassExtended(Closure $proceed): bool
    {
        $disableLightna = !is_null($this->request->param->disable_lightna);

        return $disableLightna ? true : $proceed();
    }
}
