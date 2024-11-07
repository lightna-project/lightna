<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Storage;

use Exception;

class Opcache extends \Lightna\Engine\App\Opcache implements StorageInterface
{
    /** @AppConfig(storage/opcache/dir) */
    protected string $dir;

    protected function init(): void
    {
        $this->dir = LIGHTNA_ENTRY . rtrim($this->dir, '/') . '/';
    }

    public function set(string $key, mixed $value): void
    {
        parent::save($this->getFileName($key), $value);
    }

    public function unset(string $key): void
    {
        parent::delete($this->getFileName($key));
    }

    public function get(string $key): string|array
    {
        try {
            return parent::load($this->getFileName($key));
        } catch (Exception $e) {
            return [];
        }
    }

    public function getList(array $keys): array
    {
        $return = [];
        foreach ($keys as $key) {
            $return[$key] = $this->get($key);
        }

        return $return;
    }

    protected function getFileName(string $key): string
    {
        return implode('/', str_split(substr(sha1($key), 0, 6), 2)) . '/' . $key;
    }

    public function batch(): void
    {
        // Not needed
    }

    public function flush(): void
    {
        // Not needed
    }
}
