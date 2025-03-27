<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Plugin;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\App\Scope as MagentoScope;

class Context extends ObjectA
{
    protected MagentoScope $magentoScope;

    /**
     * @see          \Lightna\Engine\App\Context::defineScope()
     * @noinspection PhpUnused
     */
    public function defineScopeExtended(): Closure
    {
        $magentoScope = $this->magentoScope;

        return function () use ($magentoScope) {
            $this->scope = $magentoScope->resolve();
        };
    }
}
