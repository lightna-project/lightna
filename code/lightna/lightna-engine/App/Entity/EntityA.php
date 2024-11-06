<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Storage;
use Lightna\Engine\App\Storage\StorageInterface;
use Lightna\Engine\App\Context;

class EntityA extends ObjectA
{
    const STORAGE_PREFIX = self::STORAGE_PREFIX;

    protected Storage $storageFactory;
    protected StorageInterface $storage;
    /** @AppConfig(default/storage) */
    protected string $storageName;
    protected Context $context;

    protected function init(): void
    {
        $this->storage = $this->storageFactory->get($this->storageName);
    }

    protected function getKey(string|int $id): string
    {
        return static::STORAGE_PREFIX . $this->context->scope . '_' . $id;
    }

    public function set(string|int $id, array $data): self
    {
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

    public function clean(array $tags): void
    {
        $this->storage->clean($tags);
    }

    public function batch(): void
    {
        $this->storage->batch();
    }

    public function flush(): void
    {
        $this->storage->flush();
    }
}
