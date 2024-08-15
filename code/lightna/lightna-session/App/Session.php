<?php

declare(strict_types=1);

namespace Lightna\Session\App;

use Lightna\Engine\App\ObjectA;
use Lightna\Session\App\Handler\HandlerInterface;

class Session extends ObjectA
{
    /** @AppConfig(session) */
    protected array $config;
    protected HandlerInterface $handler;

    protected function init(): void
    {
        $this->handler = getobj(
            $this->config['handler'],
            $this->config['options']
        );
    }

    public function read(): array
    {
        return $this->handler->read();
    }

    public function prolong(): void
    {
        $this->handler->prolong();
    }
}
