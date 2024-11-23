<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Entity;

use Lightna\Engine\App\Context;
use Lightna\Engine\App\Index\IndexInterface;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Scope;
use Lightna\Engine\App\State;

class Gc extends ObjectA
{
    public bool $printKeys = false;

    /** @AppConfig(entity) */
    protected array $entities;
    protected State $state;
    protected Scope $scope;
    protected Context $context;

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
        $this->entity = getobj($config['entity']);
        $this->entityName = $name;
        $this->index = isset($config['index']) ? getobj($config['index']) : null;
        $this->cleanKeysBatch = $this->checkIdsBatch = $this->cleanIdsBatch = [];

        $this->startEntityStats();
    }

    protected function processKey(string $key): void
    {
        $this->statsCountKey();

        [$version, $scope, $id] = $this->parseKey($key);
        if ($this->entity::IS_GLOBAL) {
            if ($this->index) {
                $this->checkIdsBatch[$scope][$id] = $id;
            }
        } else {
            if ($version === false || $scope === false) {
                $this->cleanKeysBatch[] = $key;
            } else {
                if (!$this->versionExists($version) || !$this->scopeExists($scope)) {
                    $this->cleanKeysBatch[] = $key;
                } elseif ($this->index) {
                    $this->checkIdsBatch[$scope][$id] = $id;
                }
            }
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
        if ($this->entity::IS_GLOBAL) {
            $version = $scope = false;
        } else {
            $version = strtok('_');
            $scope = strtok('_');
        }
        $id = strtok('');
        $id = $id === false ? '' : $id;

        return [$version, $scope, $id];
    }

    protected function versionExists(string $version): bool
    {
        return $version === $this->state->index->version;
    }

    protected function scopeExists(string $scope): bool
    {
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
