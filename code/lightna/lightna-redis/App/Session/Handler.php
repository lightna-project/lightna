<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Session;

use Exception;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\StoragePool;
use Lightna\Engine\App\Storage\StorageInterface;
use Lightna\Session\App\Handler\HandlerInterface;

class Handler extends ObjectA implements HandlerInterface
{
    protected StoragePool $storagePool;
    protected StorageInterface $redis;
    protected string $sessionId;
    /** @AppConfig(storage/session_redis/options) */
    protected array $storageOptions;

    /** @noinspection PhpUnused */
    protected function defineRedis(): void
    {
        $this->redis = $this->storagePool->get('session_redis');
    }

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

        if (!$srz = $this->readRedis()) {
            return '';
        }

        return $srz;
    }

    protected function readRedis(): string
    {
        return match ($this->storageOptions['data_type']) {
            'string' => $this->redis->get($this->getKey()),
            'hash' => $this->redis->getHashField($this->getKey(), $this->storageOptions['data_hash_field']),
            default => throw new Exception('Unknown data_type for session_redis storage'),
        };
    }

    public function prolong(): void
    {
        $this->redis->expire(
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

        return $lifetime === 0 ? /* 1 week */ 604800 : $lifetime;
    }
}
