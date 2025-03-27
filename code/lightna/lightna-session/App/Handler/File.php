<?php

declare(strict_types=1);

namespace Lightna\Session\App\Handler;

use Lightna\Engine\App\Context;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\ObjectA;

class File extends ObjectA implements HandlerInterface
{
    protected Context $context;
    protected string $sessionId;

    /** @noinspection PhpUnused */
    protected function defineSessionId(): void
    {
        $this->sessionId = $_COOKIE[session_name()] ?? '';
    }

    public function read(): string
    {
        if (!$this->sessionId) {
            return '';
        }

        if (!$srz = @file_get_contents($this->getFilename())) {
            return '';
        }

        return $srz;
    }

    public function prolong(): void
    {
        if (!$this->sessionId) {
            return;
        }

        @touch($this->getFilename());
    }

    protected function getFilename(): string
    {
        if (preg_match('~[^a-z0-9]~iu', $this->sessionId)) {
            throw new LightnaException('Invalid session ID');
        }

        return session_save_path() . '/sess_' . $this->sessionId;
    }
}
