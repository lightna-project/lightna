<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index;

interface IndexInterface
{
    public function refresh(array $ids): void;

    public function getBatchData(array $ids): array;

    public function scan(string|int $lastId = null): array;
}
