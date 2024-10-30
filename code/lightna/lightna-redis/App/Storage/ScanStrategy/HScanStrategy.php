<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Storage\ScanStrategy;

use Redis as RedisClient;
use Lightna\Engine\App\ObjectManagerIgnore;

class HScanStrategy implements ScanStrategyInterface, ObjectManagerIgnore
{
    private RedisClient $client;

    public function __construct(RedisClient $redisClient)
    {
        $this->client = $redisClient;
    }

    public function scan(string $key, ?int &$cursor, int $batchSize): array
    {
        return $this->client->hScan($key, $cursor, null, $batchSize);
    }
}
