<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index;

use Lightna\Engine\App\Entity\Route;
use Lightna\Engine\App\ObjectA;

abstract class IndexAbstract extends ObjectA implements IndexInterface
{
    protected Route $route;
    protected array $dataBatch;
    protected bool $hasRoutes = false;
    protected array $routesBatch;
    protected string $entityName;

    /** @noinspection PhpUnused */
    protected function defineEntityName(): void
    {
        $this->entityName = str_replace('_', '.', trim($this->entity::STORAGE_PREFIX, '_'));
    }

    public function refresh(array $ids): void
    {
        $this->loadDataBatch($ids);
        $this->loadRoutesBatch(array_keys($this->dataBatch));
        $this->batch();

        foreach ($this->dataBatch as $id => $item) {
            $this->updateItem($id, $item);
            $this->updateItemRoutes($id);
        }
        foreach (array_diff($ids, array_keys($this->dataBatch)) as $id) {
            $this->removeItem($id);
            $this->removeItemRoutes($id);
        }

        $this->flush();
    }

    public function getRoutesBatch(array $ids): array
    {
        return [];
    }

    protected function loadDataBatch(array $ids): void
    {
        $this->dataBatch = $this->getDataBatch($ids);
    }

    protected function loadRoutesBatch(array $ids): void
    {
        $this->routesBatch = $this->hasRoutes ? $this->getRoutesBatch($ids) : [];
    }

    protected function updateItem(string|int $id, array $data): void
    {
        $this->entity->set($id, array_camel($data));
    }

    protected function updateItemRoutes(string|int $id): void
    {
        if ($this->hasRoutes) {
            $this->route->setEntityRoutes(
                $this->entityName,
                $id,
                $this->routesBatch[$id] ?? [],
            );
        }
    }

    protected function removeItem(string|int $id): void
    {
        $this->entity->unset($id);
    }

    protected function removeItemRoutes(string|int $id): void
    {
        if ($this->hasRoutes) {
            $this->route->unsetEntityRoutes($this->entityName, $id);
        }
    }

    protected function batch(): void
    {
        $this->entity->batch();
        $this->hasRoutes && $this->route->batch();
    }

    protected function flush(): void
    {
        $this->entity->flush();
        $this->hasRoutes && $this->route->flush();
    }
}
