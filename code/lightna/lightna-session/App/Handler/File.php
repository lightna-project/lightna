<?php

declare(strict_types=1);

namespace Lightna\Session\App\Handler;

use Exception;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Context;
use Throwable;

class File extends ObjectA implements HandlerInterface
{
    protected array $options;
    protected Context $context;
    protected string $sessionId;

    protected function init(array $data = []): void
    {
        $this->options = $data;
    }

    /** @noinspection PhpUnused */
    protected function defineSessionId(): void
    {
        $this->sessionId = $_COOKIE[session_name()] ?? '';
    }

    public function read(): array
    {
        if (!$this->sessionId) {
            return [];
        }

        if (!$srz = @file_get_contents($this->getFilename())) {
            return [];
        }

        try {
            $data = $this->unserialize($srz);
        } catch (Throwable $e) {
            throw new Exception(
                'Session can\'t be unserialized, make sure session.serialize_handler = php_serialize'
            );
        }

        return $data[$this->options['namespace']]['data'][$this->getScopeKey()] ?? [];
    }

    protected function unserialize(string $srz): array
    {
        return unserialize($srz);
    }

    protected function getScopeKey(): string
    {
        return 'scope_' . $this->context->scope;
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
        return session_save_path() . '/sess_' . $this->sessionId;
    }
}
