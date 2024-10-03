<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Storage;

interface StorageInterface
{
    public function set(string $key, mixed $value, array $tags = []): void;

    public function unset(string $key): void;

    public function get(string $key): string|array;

    public function getList(array $keys): array;

    public function clean(array $tags): void;
}
