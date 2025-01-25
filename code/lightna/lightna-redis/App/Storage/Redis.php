<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Storage;

use Generator;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Storage\StorageInterface;
use Redis as RedisClient;

class Redis extends ObjectA implements StorageInterface
{
    protected RedisClient $client;
    protected array $options;
    protected bool $batch = false;
    protected array $batchSet = [];
    protected array $batchUnset = [];
    protected array $optionDefaults = [
        'host' => 'localhost',
        'port' => 6379,
        'timeout' => 5,
        'db' => 0,
        'persistent' => true,
        'prefix' => '',
        'serializer' => 'igbinary',
    ];

    protected function init(array $data = []): void
    {
        $this->options = merge($this->optionDefaults, $data);
    }

    /** @noinspection PhpUnused */
    protected function defineClient(): void
    {
        $this->client = new RedisClient();
        $this->connect();
    }

    protected function connect(): self
    {
        if ($this->options['persistent']) {
            $this->connectPersistent();
        } else {
            $this->connectDefault();
        }

        $this->client->select($this->options['db']);
        $this->setSerializerOptions();

        return $this;
    }

    protected function connectPersistent(): void
    {
        $this->client->pconnect(
            $this->options['host'],
            (int)$this->options['port'],
            (float)$this->options['timeout'],
            $this->getPersistentId(),
        );
    }

    protected function getPersistentId(): string
    {
        return (string)$this->options['db'];
    }

    protected function connectDefault(): void
    {
        $this->client->connect(
            $this->options['host'],
            (int)$this->options['port'],
            (float)$this->options['timeout'],
        );
    }

    protected function setSerializerOptions(): void
    {
        $this->client->setOption(
            RedisClient::OPT_SERIALIZER,
            $this->getSerializer(),
        );
    }

    protected function getSerializer(): int
    {
        return match ($this->options['serializer']) {
            'php' => RedisClient::SERIALIZER_PHP,
            'json' => RedisClient::SERIALIZER_JSON,
            'igbinary' => RedisClient::SERIALIZER_IGBINARY,
            default => RedisClient::SERIALIZER_NONE,
        };
    }

    protected function getStorageKey(string $key): string
    {
        return $this->options['prefix'] . $key;
    }

    protected function getEntityKey(string $key): string
    {
        if ($this->options['prefix'] !== '') {
            $key = substr($key, strlen($this->options['prefix']));
        }

        return $key;
    }

    public function set(string $key, mixed $value): void
    {
        $key = $this->getStorageKey($key);
        if ($this->batch) {
            $this->batchSet[$key] = $value;
        } else {
            $this->client->set($key, $value);
        }
    }

    public function unset(string $key): void
    {
        $key = $this->getStorageKey($key);
        if ($this->batch) {
            $this->batchUnset[$key] = 1;
        } else {
            $this->client->del($key);
        }
    }

    public function get(string $key): string|array
    {
        $result = $this->client->get($this->getStorageKey($key));

        return is_array($result) ? $result : (string)$result;
    }

    public function getHashField(string $key, string $field): string
    {
        return (string)$this->client->hGet($this->getStorageKey($key), $field);
    }

    public function getList(array $keys): array
    {
        $storageKeys = array_map(fn(string $key) => $this->getStorageKey($key), $keys);
        $result = $this->client->mGet($storageKeys);

        $return = [];
        foreach (array_values($keys) as $i => $key) {
            $return[$key] = is_array($result[$i]) ? $result[$i] : (string)$result[$i];
        }

        return $return;
    }

    public function batch(): void
    {
        $this->batch = true;
    }

    public function flush(): void
    {
        $this->client->del(array_keys($this->batchUnset));
        $this->client->mSet($this->batchSet);

        $this->batch = false;
        $this->batchSet = [];
        $this->batchUnset = [];
    }

    public function keys(string $prefix): Generator
    {
        $prefix = $this->getStorageKey($prefix);

        do {
            $keys = $this->client->scan($it, $prefix . '*');
            if ($keys !== FALSE) {
                foreach ($keys as $key) {
                    $key = $this->getEntityKey($key);
                    yield $key;
                }
            }
        } while ($it > 0);
    }

    public function expire(int|string $key, int $ttl): void
    {
        $this->client->expire($key, $ttl);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
