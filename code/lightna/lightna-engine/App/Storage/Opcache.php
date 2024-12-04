<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Storage;

use Generator;
use Lightna\Engine\App\State;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class Opcache extends \Lightna\Engine\App\Opcache implements StorageInterface
{
    protected State $state;

    /** @AppConfig(storage/opcache/dir) */
    protected string $dir;
    /** @AppConfig(storage/opcache/options) */
    protected array $options;
    protected array $optionsRestore;
    protected array $slapOptionsBase = [
        'validate_timestamps' => 1,
        'revalidate_freq' => 0,
    ];
    protected array $slapOptions;
    protected array $slapOptionsRestore;
    protected bool $isSlapTime;

    protected function init(array $data = []): void
    {
        $this->dir = LIGHTNA_ENTRY . rtrim($this->dir, '/') . '/';

        // Restoring options need to be defined before applying, thus here is the place
        $this->defineOptionsRestore();
        $this->defineSlapOptionsRestore();
    }

    protected function defineOptionsRestore(): void
    {
        $this->optionsRestore = [];
        foreach ($this->options as $option => $value) {
            $this->optionsRestore[$option] = ini_get('opcache.' . $option);
        }
    }

    /** @noinspection PhpUnused */
    protected function defineSlapOptions(): void
    {
        $this->slapOptions = merge(
            $this->options,
            $this->slapOptionsBase,
        );
    }

    protected function defineSlapOptionsRestore(): void
    {
        $this->slapOptionsRestore = [];
        foreach ($this->slapOptions as $option => $value) {
            $this->slapOptionsRestore[$option] = ini_get('opcache.' . $option);
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
            $this->isSlapTime = $this->isSlapTime();
            $this->applyOptions();
            return parent::load($this->getFileName($key));
        } catch (Throwable) {
            return [];
        } finally {
            $this->restoreOptions();
        }
    }

    protected function isSlapTime(): bool
    {
        $slap = $this->state->opcache->slap;

        return time() - $slap->time < $slap->length;
    }

    protected function applyOptions(): void
    {
        if (!IS_PROD_MODE) {
            return;
        }

        $this->setIniOptions(
            $this->isSlapTime ? $this->slapOptions : $this->options,
        );
    }

    protected function restoreOptions(): void
    {
        if (!IS_PROD_MODE) {
            return;
        }

        $this->setIniOptions(
            $this->isSlapTime ? $this->slapOptionsRestore : $this->optionsRestore,
        );
    }

    protected function setIniOptions(array $options): void
    {
        foreach ($options as $option => $value) {
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
