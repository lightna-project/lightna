<?php

declare(strict_types=1);

namespace Lightna\Redis\App\Storage\ScanStrategy;

interface ScanStrategyInterface
{
    public function scan(string $key, ?int &$cursor, int $batchSize): array;
}