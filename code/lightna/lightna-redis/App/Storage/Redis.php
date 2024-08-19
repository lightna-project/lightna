<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Storage;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Storage\StorageInterface;
use Redis as RedisClient;

class Redis extends ObjectA implements StorageInterface
{
    protected ?RedisClient $client;

    protected array $connection;

    /**
     * @throws \RedisException
     */
    protected function init(array $connection): void
    {
        $this->connection = $connection;
        $this->client = new RedisClient();
        $this->connect();
    }

    /**
     * @throws \RedisException
     */
    protected function connect(): self
    {
        $connType = ($this->connection['persistent'] ?? null) ? 'pconnect' : 'connect';

        $this->client->$connType(
            $this->connection['host'] ?? 'localhost',
            (int)($this->connection['port'] ?? 6379),
        );

        $this->client->select($this->connection['db'] ?? 0);
        $this->client->setOption(
            RedisClient::OPT_SERIALIZER,
            RedisClient::SERIALIZER_IGBINARY
        );

        return $this;
    }

    /**
     * @throws \RedisException
     */
    public function set(string $key, mixed $value, array $tags = []): void
    {
        $this->client->set($key, $value);

        // Add the key to a set that represents the tag
        $this->client->sAdd($this->getTagKey($tags), $key);
    }

    /**
     * @throws \RedisException
     */
    public function unset(string $key): void
    {
        $this->client->del($key);
    }

    /**
     * @throws \RedisException
     */
    public function get(string $key): string|array
    {
        $result = $this->client->get($key);

        return is_array($result) ? $result : (string)$result;
    }

    /**
     * @throws \RedisException
     */
    public function getList(array $keys): array
    {
        $result = $this->client->mGet($keys);

        $return = [];
        foreach (array_values($keys) as $i => $key) {
            $return[$key] = is_array($result[$i]) ? $result[$i] : (string)$result[$i];
        }

        return $return;
    }

    /**
     * @throws \RedisException
     */
    public function clean(array $tags): void
    {
        // Get all keys associated with the tag
        $keys = $this->client->sMembers($this->getTagKey($tags));

        // Start a transaction
        $this->client->multi();

        try {
            // Delete each key
            foreach ($keys as $key) {
                $this->unset($key);
            }

            // Delete the tag set
            $this->unset($this->getTagKey($tags));

            // Execute the transaction
            $this->client->exec();
        } catch (\RedisException $e) {
            // Rollback the transaction
            $this->client->discard();
        }
    }

    private function getTagKey(array $tags): string
    {
        return '|' . implode('|', $tags) . '|';
    }
}
