<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Storage;

use Generator;

interface StorageInterface
{
    public function set(string $key, mixed $value): void;

    public function unset(string $key): void;

    public function get(string $key): string|array;

    public function getList(array $keys): array;

    public function batch(): void;

    public function flush(): void;

    public function keys(string $prefix): Generator;

    public function isReadOnly(): bool;
}
