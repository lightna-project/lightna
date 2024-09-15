<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Storage;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Storage\StorageInterface;
use Redis as RedisClient;

class Redis extends ObjectA implements StorageInterface
{

    public const FIELD_VALUE = 'value';
    public const FIELD_TAGS  = 'tags';

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
        $this->multi();

        try {
            $this->setKeyValue($key, $value);
            $this->createTags($key, $tags);

            $this->exec();
        } catch (\RedisException $e) {
            $this->discard();

            throw new \RedisException("Could not set key: $key");
        }
    }

    /**
     * Enable multimode to ensure that all operations are atomic
     *
     * @throws \RedisException
     */
    private function multi(): void
    {
        $this->client->multi();
    }

    /**
     * Set key value.
     *
     * @param string $key
     * @param mixed $value
     *
     * @throws \RedisException
     */
    private function setKeyValue(string $key, mixed $value): void
    {
        $result = $this->client->hSet($key, self::FIELD_VALUE, $value);
        if (!$result) {
            throw new \RedisException("Could not set value for key: $key");
        }
    }

    /**
     * Create tags for a key
     *
     * @param string $key
     * @param array $tags
     *
     * @throws \RedisException
     */
    private function createTags(string $key, array $tags): void
    {
        $result = $this->client->hSet($key, self::FIELD_TAGS, json_encode($tags));
        if (!$result) {
            throw new \RedisException("Could not set tag for key: $key");
        }

        // Store the tags associated with the key
        foreach ($tags as $tag) {
            // Add the key to a set that represents the tags
            $this->client->sAdd($tag, $key);
        }
    }

    /**
     * Execute queued commands
     *
     * @return void
     * @throws \RedisException
     */
    private function exec(): void
    {
        $result = $this->client->exec();

        if ($result === false) {
            throw new \RedisException("Exec failed");
        }

        // It's important to note that even when a command fails, all the other commands in the queue are processed
        // Redis will not stop the processing of commands
        /** @TODO: Analyze result and throw exception in case of partial exec */
    }

    /**
     * Flush queued commands
     *
     * @throws \RedisException
     */
    private function discard(): void
    {
        $this->client->discard();
    }

    /**
     * @throws \RedisException
     */
    public function unset(string $key): void
    {
        $this->watch($key);
        $tags = $this->getTagsForKey($key);
        $this->multi();
        try {
            $this->cleanTags($key, $tags);
            $this->client->del($key);

            $this->exec();
        } catch (\RedisException $e) {
            $this->discard();

            throw new \RedisException("Could not unset key: $key");
        }
    }

    /**
     * Watch a key. Ensures that the key has not been modified since the watch
     *
     * @param string $key
     *
     * @throws \RedisException
     */
    public function watch(string $key): void
    {
        $this->client->watch($key);
    }

    /**
     * @throws \RedisException
     */
    public function get(string $key): string|array
    {
        $result = $this->client->hGet($key, self::FIELD_VALUE);

        return is_array($result) ? $result : (string)$result;
    }

    /**
     * @throws \RedisException
     */
    public function getList(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->get($key);
        }

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
        foreach ($tags as $tag) {
            foreach ($this->getKeysForTag($tag) as $key) {
                $this->unset($key);
            }
        }
    }

    /**
     * Clean tags
     *
     * @param string $key
     * @param array $tags
     *
     * @throws \RedisException
     */
    private function cleanTags(string $key, array $tags): void
    {
        // Iterate over each tag and remove the key from the tag set
        foreach ($tags as $tag) {
            $this->client->sRem($tag, $key);

            // Check if the tag set is now empty and remove it if it is
            if ($this->client->sCard($tag) == 0) {
                $this->client->del($tag);
            }
        }
    }

    /**
     * Get tags for a key
     *
     * @param string $key
     *
     * @return array
     * @throws \RedisException
     */
    private function getTagsForKey(string $key): array
    {
        // Get string from redis
        $tags = $this->client->hGet($key, self::FIELD_TAGS);

        return $tags ? json_decode((string)$tags) : [];
    }

    /**
     * Get keys for a tag
     *
     * @param string $tag
     *
     * @return array
     * @throws \RedisException
     */
    private function getKeysForTag(string $tag): array
    {
        // Get all keys associated with the tag
        return $this->client->sMembers($tag);
    }
}
