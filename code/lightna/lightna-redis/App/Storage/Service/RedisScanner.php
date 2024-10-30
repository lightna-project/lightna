<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Storage\Service;

use Lightna\Redis\App\Storage\ScanStrategy\ScanStrategyInterface;
use Lightna\Engine\App\ObjectManagerIgnore;

class RedisScanner implements ObjectManagerIgnore
{
    private ScanStrategyInterface $strategy;

    public function __construct(ScanStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function scanByCursor(string $key, int $batchSize = 10000): iterable
    {
        $cursor = null;
        do {
            $items = $this->strategy->scan($key, $cursor, $batchSize);
            if (!empty($items)) {
                yield $items;
            }
        } while ($cursor != 0);
    }
}

