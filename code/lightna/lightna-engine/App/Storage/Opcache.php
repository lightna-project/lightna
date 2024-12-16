<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Storage;

use Generator;
use Lightna\Engine\App\State\Common as AppState;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class Opcache extends \Lightna\Engine\App\Opcache implements StorageInterface
{
    protected AppState $appState;

    /** @AppConfig(storage/opcache/options/dir) */
    protected string $dir;
    /** @AppConfig(storage/opcache/options) */
    protected array $options;
    /** @AppConfig(storage/opcache/options/ini) */
    protected array $iniOptions;
    protected array $iniOptionsRestore;
    protected bool $isSlapTime;

    protected function init(array $data = []): void
    {
        $this->dir = LIGHTNA_ENTRY . rtrim($this->dir, '/') . '/';

        // Restoring options need to be defined before applying, thus here is the place
        $this->defineIniOptionsRestore();
    }

    protected function defineIniOptionsRestore(): void
    {
        $this->iniOptionsRestore = [];
        foreach ($this->iniOptions as $option => $value) {
            $this->iniOptionsRestore[$option] = ini_get('opcache.' . $option);
        }
    }

    /** @noinspection PhpUnused */
    protected function defineIsSlapTime(): void
    {
        $slap = $this->appState->opcache->slap;
        $this->isSlapTime = time() - $slap->time < $slap->length;
    }

    public function set(string $key, mixed $value): void
    {
        parent::save($key, $value);
    }

    public function unset(string $key): void
    {
        parent::delete($key);
    }

    public function get(string $key): string|array
    {
        try {
            $this->applyOptions();

            if (!$this->isSlapTime) {
                return parent::load($key);
            } else {
                return parent::loadRevalidated($key);
            }
        } catch (Throwable) {
            return [];
        } finally {
            $this->restoreOptions();
        }
    }

    protected function applyOptions(): void
    {
        if (!IS_PROD_MODE) {
            return;
        }

        $this->setIniOptions($this->iniOptions);
    }

    protected function restoreOptions(): void
    {
        if (!IS_PROD_MODE) {
            return;
        }

        $this->setIniOptions($this->iniOptionsRestore);
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

    protected function getFileName(string $name): string
    {
        $name = implode('/', str_split(substr(sha1($name), 0, 6), 2)) . '/' . $name;

        return parent::getFileName($name);
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

    public function isReadOnly(): bool
    {
        return (bool)($this->options['is_read_only'] ?? false);
    }
}
