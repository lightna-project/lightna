<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

use Exception;
use Generator;
use Lightna\Engine\App\Config as AppConfig;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\State\Common;
use Lightna\Engine\App\StoragePool;
use Lightna\Engine\App\Storage\StorageInterface;

class EntityA extends ObjectA
{
    /** Entity NAME should match to the entity code in yaml config */
    public const NAME = self::NAME;
    public const SCOPED = true;
    public const STORAGE_PREFIX = self::STORAGE_PREFIX;

    /** @AppConfig(default/storage) */
    protected string $storageName;
    protected StoragePool $storagePool;
    protected StorageInterface $storage;
    protected Common $state;
    protected Context $context;
    protected AppConfig $appConfig;

    protected bool $isVersioned;

    /** @noinspection PhpUnused */
    protected function defineStorage(): void
    {
        $this->storage = $this->storagePool->get($this->storageName);
    }

    /** @noinspection PhpUnused */
    protected function defineIsVersioned(): void
    {
        $this->isVersioned =
            $this->appConfig->get('entity/' . $this::NAME . '/versioned') ?? true;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function isVersioned(): bool
    {
        return $this->isVersioned;
    }

    public function set(string|int $id, array $data): self
    {
        // Validate here so it won't affect the frontend
        $this->validatePrefix();

        $data = array_filter_recursive($data, function ($k, $v) {
            return $v !== null;
        });

        $this->storage->set($this->getKey($id), $data);

        return $this;
    }

    public function unset(string|int $id): self
    {
        $this->storage->unset($this->getKey($id));

        return $this;
    }

    public function get(string|int $id): array
    {
        return $this->storage->get($this->getKey($id)) ?: [];
    }

    public function getList(array $ids): array
    {
        $storageIds = array_map(fn($id) => $this->getKey($id), $ids);

        $result = [];
        foreach ($this->storage->getList($storageIds) as $key => $data) {
            $result[$this->getId($key)] = $data;
        }

        return $result;
    }

    public function batch(): void
    {
        $this->storage->batch();
    }

    public function flush(): void
    {
        $this->storage->flush();
    }

    public function keys(): Generator
    {
        foreach ($this->storage->keys(static::STORAGE_PREFIX) as $key) {
            yield $key;
        }
    }

    protected function getKey(string|int $id): string
    {
        return $this->getFullPrefix() . $id;
    }

    protected function getId(string $key): string
    {
        return substr($key, strlen($this->getFullPrefix()));
    }

    /**
     * Prevent "_" overuse to avoid incorrect GC functioning
     */
    protected function validatePrefix(): void
    {
        $expectedDepth = 2 + (int)$this->isVersioned + (int)static::SCOPED;
        $actualDepth = substr_count($this->getFullPrefix(), '_') + 1;

        if ($actualDepth !== $expectedDepth) {
            throw new Exception('Invalid depth for prefix "' . $this->getFullPrefix() . '". Make sure "_" isn\'t overused.');
        }
    }

    protected function getFullPrefix(): string
    {
        $versionPrefix = $this->isVersioned ? $this->state->index->version . '_' : '';
        $scopePrefix = static::SCOPED ? $this->context->scope . '_' : '';

        return static::STORAGE_PREFIX . $versionPrefix . $scopePrefix;
    }
}
