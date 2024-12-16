<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

use Lightna\Engine\App\Context;
use Lightna\Engine\App\Index\IndexInterface;
use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Scope;
use Lightna\Engine\App\State\Common;

class Gc extends ObjectA
{
    public bool $printKeys = false;

    /** @AppConfig(entity) */
    protected array $entities;
    protected Common $state;
    protected Scope $scope;
    protected Context $context;
    protected Indexer $indexer;

    protected array $scopeList;
    protected string $entityName;
    protected ?EntityA $entity;
    protected ?IndexInterface $index;
    protected int $batchSize = 1000;
    protected array $cleanKeysBatch = [];
    protected array $checkIdsBatch = [];
    protected array $cleanIdsBatch = [];
    protected array $stats = [];

    /** @noinspection PhpUnused */
    protected function defineScopeList(): void
    {
        $this->scopeList = [];
        foreach ($this->scope->getList() as $scope) {
            $this->scopeList[$scope] = $scope;
        }
    }

    public function process(): void
    {
        foreach ($this->entities as $name => $config) {
            if (!isset($config['entity'])) {
                continue;
            }

            $this->entity = getobj($config['entity']);
            if ($this->entity->getStorage()->isReadOnly()) {
                continue;
            }

            $this->startEntityGc($name, $config);
            foreach ($this->entity->keys() as $key) {
                $this->processKey($key);
                $this->processBatches();
            }

            $this->processBatches(true);
            $this->endEntityGc();
        }
    }

    protected function startEntityGc(string $name, array $config): void
    {
        $this->entityName = $name;
        $this->index = $this->indexer->getEntityIndex($name);
        $this->cleanKeysBatch = $this->checkIdsBatch = $this->cleanIdsBatch = [];

        $this->startEntityStats();
    }

    protected function processKey(string $key): void
    {
        $this->statsCountKey();

        [$version, $scope, $id] = $this->parseKey($key);

        if (!$this->versionExists($version) || !$this->scopeExists($scope)) {
            $this->cleanKeysBatch[] = $key;
        } elseif ($this->index) {
            $this->checkIdsBatch[$scope][$id] = $id;
        }
    }

    protected function startEntityStats(): void
    {
        $this->stats[$this->entityName] = [
            'cleaned' => 0,
            'total' => 0,
            'time' => microtime(true),
        ];
    }

    protected function statsCountKey(): void
    {
        $this->stats[$this->entityName]['total']++;
    }

    protected function endEntityGc(): void
    {
        $this->endEntityStats();
    }

    protected function endEntityStats(): void
    {
        $this->stats[$this->entityName]['time'] = microtime(true) - $this->stats[$this->entityName]['time'];
    }

    protected function parseKey(string $key): array
    {
        strtok($key, '_');
        $version = $this->entity->isVersioned() ? strtok('_') : null;
        $scope = $this->entity::SCOPED ? strtok('_') : null;
        $id = strtok('');
        $id = $id === false ? '' : $id;

        return [$version, $scope, $id];
    }

    protected function versionExists(?string $version): bool
    {
        if (!$this->entity->isVersioned()) {
            return $version === null;
        }

        return $version === $this->state->index->version;
    }

    protected function scopeExists(?string $scope): bool
    {
        if (!$this->entity::SCOPED) {
            return $scope === null;
        }

        return isset($this->scopeList[$scope]);
    }

    protected function processBatches(bool $force = false): void
    {
        foreach ($this->scopeList as $scope) {
            $this->context->scope = $scope;

            if ($this->index) {
                $this->processCheckIdsBatch($scope, $force);
            }

            $this->processCleanIdsBatch($scope, $force);

            if (!$this->entity::SCOPED) break;
        }

        $this->processCleanKeysBatch($force);
    }

    protected function processCheckIdsBatch(int $scope, bool $force): void
    {
        if (!$this->isBatchReady($this->checkIdsBatch[$scope] ?? [], $force)) {
            return;
        }

        $this->cleanIdsBatch[$scope] = merge(
            $this->cleanIdsBatch[$scope] ?? [],
            $this->index->gcCheck($this->checkIdsBatch[$scope]),
        );
        $this->checkIdsBatch[$scope] = [];
    }

    protected function processCleanIdsBatch(int $scope, bool $force): void
    {
        if (!$this->isBatchReady($this->cleanIdsBatch[$scope] ?? [], $force)) {
            return;
        }

        $this->cleanIdsBatch($scope);
        $this->cleanIdsBatch[$scope] = [];
    }

    protected function processCleanKeysBatch(bool $force): void
    {
        if (!$this->isBatchReady($this->cleanKeysBatch, $force)) {
            return;
        }

        $this->cleanKeysBatch();
        $this->cleanKeysBatch = [];
    }

    protected function isBatchReady(array $batch, bool $force): bool
    {
        return (count($batch) >= $this->batchSize) || ($force && count($batch));
    }

    protected function cleanKeysBatch(): void
    {
        $this->entity->batch();
        foreach ($this->cleanKeysBatch as $key) {
            if ($this->printKeys) {
                echo "gc: unset key \"$key\"\n";
            }
            $this->entity->getStorage()->unset($key);
        }
        $this->entity->flush();

        $this->stats[$this->entityName]['cleaned'] += count($this->cleanKeysBatch);
    }

    protected function cleanIdsBatch(int $scope): void
    {
        $this->entity->batch();
        foreach ($this->cleanIdsBatch[$scope] as $id) {
            if ($this->printKeys) {
                echo "gc: unset {$this->entityName} id \"$id\" scope \"" . $this->context->scope . "\"\n";
            }
            $this->entity->unset($id);
        }
        $this->entity->flush();

        $this->stats[$this->entityName]['cleaned'] += count($this->cleanIdsBatch[$scope]);
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}
