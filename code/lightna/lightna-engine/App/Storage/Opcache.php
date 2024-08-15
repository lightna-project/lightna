<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Storage;

use Exception;
use Lightna\Engine\App\Compiled;
use Lightna\Engine\App\ObjectA;

class Opcache extends ObjectA implements StorageInterface
{
    protected Compiled $compiled;
    protected array $options;
    /** @AppConfig(storage/opcache/options/dir) */
    protected string $dir;

    protected function init(array $options): void
    {
        $this->options = $options;
        $this->dir = $this->dir . '/';
    }

    public function set(string $key, mixed $value): void
    {
        $this->compiled->save($this->dir . $this->getFileName($key), $value);
    }

    public function unset(string $key): void
    {
        $this->compiled->delete($this->dir . $this->getFileName($key));
    }

    public function get(string $key): string|array
    {
        try {
            return require LIGHTNA_CODE . $this->dir . $this->getFileName($key) . '.php';
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

    public function clean(array $tags): void
    {
        // TODO: Implement clean() method.
    }
}
