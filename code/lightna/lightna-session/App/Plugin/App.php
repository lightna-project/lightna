<?php

declare(strict_types=1);

namespace Lightna\Session\App\Plugin;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Session\App\Session;

class App extends ObjectA
{
    protected Session $session;

    /**
     * @see          \Lightna\Engine\App::process()
     * @noinspection PhpUnused
     */
    public function processExtended(Closure $proceed): void
    {
        $this->session->prolong();
        $proceed();
    }
}
