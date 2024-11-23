<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Storage;

use Generator;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Storage\StorageInterface;
use Redis as RedisClient;

class Redis extends ObjectA implements StorageInterface
{
    protected ?RedisClient $client;
    protected array $options;
    protected bool $batch = false;
    protected array $batchSet = [];
    protected array $batchUnset = [];

    protected function init(array $options): void
    {
        $this->options = $options;
    }

    /** @noinspection PhpUnused */
    protected function defineClient(): void
    {
        $this->client = new RedisClient();
        $this->connect();
    }

    protected function connect(): self
    {
        $connType = ($this->options['persistent'] ?? null) ? 'pconnect' : 'connect';

        $this->client->$connType(
            $this->options['host'] ?? 'localhost',
            (int)($this->options['port'] ?? 6379),
        );

        $this->client->select($this->options['db'] ?? 0);
        $this->client->setOption(
            RedisClient::OPT_SERIALIZER,
            RedisClient::SERIALIZER_IGBINARY
        );

        return $this;
    }

    public function set(string $key, mixed $value): void
    {
        if ($this->batch) {
            $this->batchSet[$key] = $value;
        } else {
            $this->client->set($key, $value);
        }
    }

    public function unset(string $key): void
    {
        if ($this->batch) {
            $this->batchUnset[$key] = 1;
        } else {
            $this->client->del($key);
        }
    }

    public function get(string $key): string|array
    {
        $result = $this->client->get($key);

        return is_array($result) ? $result : (string)$result;
    }

    public function getList(array $keys): array
    {
        $result = $this->client->mGet($keys);

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
        do {
            $keys = $this->client->scan($it, $prefix . '*');
            if ($keys !== FALSE) {
                foreach ($keys as $key) {
                    yield $key;
                }
            }
        } while ($it > 0);
    }
}
