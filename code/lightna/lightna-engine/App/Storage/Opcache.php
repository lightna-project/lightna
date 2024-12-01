<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Storage;

use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class Opcache extends \Lightna\Engine\App\Opcache implements StorageInterface
{
    /** @AppConfig(storage/opcache/dir) */
    protected string $dir;
    /** @AppConfig(storage/opcache/options) */
    protected array $options;
    protected array $permanentOptions;

    protected function init(array $data = []): void
    {
        $this->dir = LIGHTNA_ENTRY . rtrim($this->dir, '/') . '/';

        // Need to be defined before applyOptions, thus here is the place
        $this->definePermanentOptions();
    }

    protected function definePermanentOptions(): void
    {
        $this->permanentOptions = [];
        foreach ($this->options as $option => $value) {
            $this->permanentOptions[$option] = ini_get('opcache.' . $option);
        }
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
            $this->applyOptions();
            return parent::load($this->getFileName($key));
        } catch (Throwable) {
            return [];
        } finally {
            $this->restoreOptions();
        }
    }

    protected function applyOptions(): void
    {
        foreach ($this->options as $option => $value) {
            ini_set('opcache.' . $option, $value);
        }
    }

    protected function restoreOptions(): void
    {
        foreach ($this->permanentOptions as $option => $value) {
            ini_set('opcache.' . $option, $value);
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

    public function keys(string $prefix): Generator
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->dir)
        );

        foreach ($iterator as $item) {
            if ($item->isFile() && str_starts_with($item->getFilename(), $prefix)) {
                yield pathinfo($item->getFilename(), PATHINFO_FILENAME);
            }
        }
    }
}
