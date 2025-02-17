<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Plugin;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\App\Scope as MagentoScope;

class Context extends ObjectA
{
    protected MagentoScope $magentoScope;

    /** @noinspection PhpUnused */
    public function defineScopeExtended(): Closure
    {
        $resolveScope = $this->magentoScope->resolve(...);

        return function () use ($resolveScope) {
            $this->scope = $resolveScope();
        };
    }
}
