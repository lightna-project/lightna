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
    protected bool $isSlapEnabled = true;
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

    public function setIniOptions(array $options): void
    {
        $this->iniOptions = $options;
        $this->defineIniOptionsRestore();
    }

    /** @noinspection PhpUnused */
    protected function defineIsSlapTime(): void
    {
        $slap = $this->appState->opcache->slap;
        $this->isSlapTime = time() - $slap->time < $slap->length;
    }

    public function set(string $key, mixed $value): void
    {
        parent::save($this->encodeKey($key), $value);
    }

    public function unset(string $key): void
    {
        parent::delete($this->encodeKey($key));
    }

    public function get(string $key): string|array
    {
        try {
            $this->applyOptions();

            if ($this->isSlapEnabled && $this->isSlapTime) {
                return parent::loadRevalidated($this->encodeKey($key));
            } else {
                return parent::load($this->encodeKey($key));
            }
        } catch (Throwable) {
            return [];
        } finally {
            $this->restoreOptions();
        }
    }

    protected function applyOptions(): void
    {
        if (!$this->isCustomIniOptionsRelevant()) {
            return;
        }

        $this->applyIniOptions($this->iniOptions);
    }

    protected function restoreOptions(): void
    {
        if (!$this->isCustomIniOptionsRelevant()) {
            return;
        }

        $this->applyIniOptions($this->iniOptionsRestore);
    }

    protected function isCustomIniOptionsRelevant(): bool
    {
        return IS_PROD_MODE;
    }

    public function disableSlap(): void
    {
        $this->isSlapEnabled = false;
    }

    protected function applyIniOptions(array $options): void
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
                yield $this->decodeKey(pathinfo($item->getFilename(), PATHINFO_FILENAME));
            }
        }
    }

    public function isReadOnly(): bool
    {
        return (bool)($this->options['is_read_only'] ?? false);
    }

    protected function encodeKey(int|string $id): int|string
    {
        return urlencode((string)$id);
    }

    protected function decodeKey(int|string $id): int|string
    {
        return urldecode((string)$id);
    }
}
