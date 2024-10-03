<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Storage;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Storage\StorageInterface;
use Redis as RedisClient;
use RedisException;

class Redis extends ObjectA implements StorageInterface
{

    public const FIELD_VALUE = 'value';
    public const FIELD_TAGS  = 'tags';

    protected ?RedisClient $client;

    protected array $connection;

    protected function init(array $connection): void
    {
        $this->connection = $connection;
        $this->client = new RedisClient();
        $this->connect();
    }

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

    public function set(string $key, mixed $value, array $tags = []): void
    {
        $this->setKeyValue($key, $value);
        $this->createTags($key, $tags);
    }

    protected function setKeyValue(string $key, mixed $value): void
    {
        $result = $this->client->hSet($key, static::FIELD_VALUE, $value);
        if ($result === false) {
            throw new RedisException("Could not set value for key: $key => " . json_encode($value));
        }
    }

    protected function createTags(string $key, array $tags): void
    {
        $result = $this->client->hSet($key, static::FIELD_TAGS, json_encode($tags));
        if ($result === false) {
            throw new RedisException("Could not set tag for key: $key => " . json_encode($tags));
        }

        // Store the tags associated with the key
        foreach ($tags as $tag) {
            // Add the key to a set that represents the tags
            $this->client->sAdd($tag, $key);
        }
    }

    public function unset(string $key): void
    {
        $this->cleanTags($key);
        $this->client->del($key);
    }

    public function get(string $key): string|array
    {
        $result = $this->client->hGet($key, static::FIELD_VALUE);

        return is_array($result) ? $result : (string)$result;
    }

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

    public function clean(array $tags): void
    {
        foreach ($tags as $tag) {
            foreach ($this->getKeysForTag($tag) as $key) {
                $this->unset($key);
            }
        }
    }

    protected function cleanTags(string $key): void
    {
        $tags = $this->getTagsForKey($key);

        // Iterate over each tag and remove the key from the tag set
        foreach ($tags as $tag) {
            $this->client->sRem($tag, $key);

            // Check if the tag set is now empty and remove it if it is
            if ($this->client->sCard($tag) == 0) {
                $this->client->del($tag);
            }
        }
    }

    protected function getTagsForKey(string $key): array
    {
        // Get string from redis
        $tags = $this->client->hGet($key, static::FIELD_TAGS);

        return $tags ? json_decode((string)$tags) : [];
    }

    protected function getKeysForTag(string $tag): array
    {
        // Get all keys associated with the tag
        return $this->client->sMembers($tag);
    }
}
