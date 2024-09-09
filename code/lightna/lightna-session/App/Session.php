<?php

declare(strict_types=1);

namespace Lightna\Session\App;

use Lightna\Engine\App\ObjectA;
use Lightna\Session\App\Handler\HandlerInterface;
use Lightna\Session\App\Session\Cookie;

class Session extends ObjectA
{
    /** @AppConfig(session) */
    protected array $config;
    protected HandlerInterface $handler;
    protected Cookie $cookie;

    protected function defineHandler(): void
    {
        $this->handler = getobj(
            $this->config['handler'],
            $this->config['options']
        );
    }

    protected function defineCookie(): void
    {
        $this->cookie = getobj(Cookie::class, $this->config['options']['cookie']);
    }

    public function read(): array
    {
        return $this->handler->read();
    }

    public function prolong(): void
    {
        $this->cookie->prolong();
        $this->handler->prolong();
    }
}
