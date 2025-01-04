<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Session;

use Exception;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Redis\App\Storage\Session as Storage;
use Lightna\Session\App\Handler\HandlerInterface;
use Throwable;

class Handler extends ObjectA implements HandlerInterface
{
    protected Storage $storage;
    protected Context $context;
    protected array $options;
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
        if (($srz = $this->storage->get($this->getKey())) === '') {
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
        $this->storage->expire(
            $this->getKey(),
            $this->getTTl(),
        );
    }

    protected function getKey(): string
    {
        return 'sess_' . $this->sessionId;
    }

    protected function getTTl(): int
    {
        $lifetime = session_get_cookie_params()['lifetime'];

        return $lifetime === 0 ? 604800 : $lifetime;
    }
}
