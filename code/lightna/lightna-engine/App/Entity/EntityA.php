<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

use Exception;
use Generator;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\State;
use Lightna\Engine\App\Storage;
use Lightna\Engine\App\Storage\StorageInterface;

class EntityA extends ObjectA
{
    public const IS_GLOBAL = false;
    public const STORAGE_PREFIX = self::STORAGE_PREFIX;

    /** @AppConfig(default/storage) */
    protected string $storageName;
    protected Storage $storageFactory;
    protected StorageInterface $storage;
    protected State $state;
    protected Context $context;

    protected int $keyDepth = 4;
    protected int $keyDepthGlobal = 2;

    /** @noinspection PhpUnused */
    protected function defineStorage(): void
    {
        $this->storage = $this->storageFactory->get($this->storageName);
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function set(string|int $id, array $data): self
    {
        // Validate here so it won't affect the frontend
        $this->validatePrefix();

        $data = array_filter_recursive($data, function ($k, $v) {
            return $v !== null && $v !== '';
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
        foreach ($ids as &$id) {
            $id = $this->getKey($id);
        }

        return $this->storage->getList($ids);
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

    /**
     * Prevent "_" overuse to avoid incorrect GC functioning
     */
    protected function validatePrefix(): void
    {
        $expectedDepth = static::IS_GLOBAL ? $this->keyDepthGlobal : $this->keyDepth;
        $actualDepth = substr_count($this->getFullPrefix(), '_') + 1;

        if ($actualDepth !== $expectedDepth) {
            throw new Exception('Invalid depth for prefix "' . $this->getFullPrefix() . '". Make sure "_" isn\'t overused.');
        }
    }

    protected function getFullPrefix(): string
    {
        $versionPrefix = static::IS_GLOBAL ? '' : $this->state->index->version . '_';
        $scopePrefix = static::IS_GLOBAL ? '' : $this->context->scope . '_';

        return static::STORAGE_PREFIX . $versionPrefix . $scopePrefix;
    }
}
