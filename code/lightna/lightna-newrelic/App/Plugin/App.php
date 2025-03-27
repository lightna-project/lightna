<?php

declare(strict_types=1);

namespace Lightna\Newrelic\App\Plugin;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Newrelic\App\Newrelic;

class App extends ObjectA
{
    protected Newrelic $newrelic;

    /**
     * @see          \Lightna\Engine\App::processAction()
     * @noinspection PhpUnused
     */
    public function processActionExtended(Closure $proceed): Closure
    {
        $newrelic = $this->newrelic;

        return function () use ($proceed, $newrelic) {
            $newrelic->registerAction($this->action);
            $proceed();
        };
    }

    /**
     * @see          \Lightna\Engine\App::processNoRoute()
     * @noinspection PhpUnused
     */
    public function processNoRouteExtended(Closure $proceed): void
    {
        $this->newrelic->registerNoRoute();
        $proceed();
    }

    /**
     * @see          \Lightna\Engine\App::processRedirect()
     * @noinspection PhpUnused
     */
    public function processRedirectExtended(Closure $proceed): void
    {
        $this->newrelic->registerRedirect();
        $proceed();
    }
}
