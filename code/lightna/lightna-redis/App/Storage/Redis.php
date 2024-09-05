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
        // Start a transaction
        $this->client->multi();

        try {
            $result = $this->client->hSet($key, self::FIELD_VALUE, $value);
            if (!$result) {
                throw new \RedisException("Could not set value for key: $key");
            }

            $result = $this->client->hSet($key, self::FIELD_TAGS, json_encode($tags));
            if (!$result) {
                throw new \RedisException("Could not set tag for key: $key");
            }

            // Store the tags associated with the key
            foreach ($tags as $tag) {
                // Add the key to a set that represents the tags
                $this->client->sAdd($tag, $key);
            }

            // Execute the transaction
            $this->client->exec();
        } catch (\RedisException $e) {
            // Rollback the transaction
            $this->client->discard();

            throw $e;
        }
    }

    /**
     * @throws \RedisException
     */
    public function unset(string $key): void
    {
        // Retrieve all tags associated with the key
        $tags = $this->getTagsForKey($key);

        // Remove the key from Redis
        $this->client->del($key);

        // Iterate over each tag and remove the key from the tag set
        foreach ($tags as $tag) {
            $this->client->sRem($tag, $key);

            // Check if the tag set is now empty and remove it if it is
            if ($this->client->sCard($tag) == 0) {
                $this->client->del($tag);
            }
        }
    }

    // Method to retrieve tags for a key

    /**
     * @throws \RedisException
     */
    private function getTagsForKey($key): array
    {
        $tags = $this->client->hGet($key, self::FIELD_TAGS);

        return $tags ? json_decode($tags) : [];
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
        foreach ($tags as $tag) {
            // Get all keys associated with the tag
            $keys = $this->client->sMembers($tag);

            // Start a transaction
            $this->client->multi();

            try {
                // Delete each key
                foreach ($keys as $key) {
                    $this->unset($key);
                }

                // Delete the tag set
                $this->client->del($tag);

                // Execute the transaction
                $this->client->exec();
            } catch (\RedisException $e) {
                // Rollback the transaction
                $this->client->discard();

                throw $e;
            }
        }
    }
}
