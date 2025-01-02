<?php

declare(strict_types=1);

namespace Lightna\Session\App;

use Exception;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Session\App\Handler\HandlerInterface;
use Lightna\Session\App\Session\Cookie;

class Session extends ObjectA
{
    /** @AppConfig(session) */
    protected array $config;
    /** @AppConfig(fpc_compatible) */
    protected bool $fpcCompatible;
    protected HandlerInterface $handler;
    protected Cookie $cookie;
    protected Context $context;

    /** @noinspection PhpUnused */
    protected function defineHandler(): void
    {
        $this->handler = getobj(
            $this->config['handler'],
            $this->config['options']
        );
    }

    public function read(): array
    {
        if (!$this->canRead()) {
            throw new Exception('Reading the session on public pages is not allowed in FPC-compatible mode.');
        }

        return $this->handler->read();
    }

    public function canRead(): bool
    {
        return !$this->fpcCompatible || $this->context->visibility === 'private';
    }

    public function prolong(): void
    {
        $this->cookie->prolong();
        $this->handler->prolong();
    }
}
