<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Index\Changelog\Handler as ChangelogHandler;
use Lightna\Engine\App\Index\IndexInterface;
use Lightna\Engine\App\Index\Queue\Handler as QueueHandler;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Index\ScopeIndexAbstract;

class Indexer extends ObjectA
{
    public const LOCK_NAME = 'lightna_indexer';
    public array $stats;

    /** @AppConfig(entity) */
    protected array $entities;
    protected Scope $scope;
    protected Context $context;
    protected ChangelogHandler $changelogHandler;
    protected QueueHandler $queueHandler;
    protected Database $db;

    public function reindex(string $entity, ?int $onlyScope = null): void
    {
        $this->resetStats();

        $index = $this->getEntityIndex($entity);
        foreach ($this->scope->getList() as $scope) {
            if ($onlyScope && $onlyScope !== $scope) {
                continue;
            }
            $this->context->scope = $scope;
            $this->refreshAll($index);
        }

        $this->completeStats();
    }

    protected function resetStats(): void
    {
        $this->stats = [
            'count' => 0,
            'start_time' => microtime(true),
        ];
    }

    protected function completeStats(): void
    {
        $this->stats['time'] = (int)(microtime(true) - $this->stats['start_time']);
    }

    protected function refreshAll(IndexInterface $index): void
    {
        $lastId = null;
        while ($ids = $index->scan($lastId)) {
            $index->refresh($ids);
            $lastId = end($ids);
            $this->stats['count'] += count($ids);
        }
    }

    public function process(): void
    {
        $this->lock();
        $this->changelogHandler->process();
        $this->queueHandler->process();
        $this->unlock();
    }

    public function processBatch(string $entity, array $batch): void
    {
        $index = $this->getEntityIndex($entity);
        $scopeList = instance_of($index, ScopeIndexAbstract::class)
            ? $batch
            : $this->scope->getList();

        foreach ($scopeList as $scope) {
            $this->context->scope = $scope;
            $index->refresh($batch);
        }
    }

    protected function getEntityIndex(string $entity): IndexInterface
    {
        return getobj($this->entities[$entity]['index']);
    }


    protected function lock(): void
    {
        if (!$this->db->getLock(static::LOCK_NAME)) {
            echo "The indexer is already running; exiting.\n";
            exit;
        }
    }

    protected function unlock(): void
    {
        $this->db->releaseLock(static::LOCK_NAME);
    }
}
