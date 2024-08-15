<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Index\Changelog\Handler as ChangelogHandler;
use Lightna\Engine\App\Index\IndexInterface;
use Lightna\Engine\App\Index\Queue\Handler as QueueHandler;
use Lightna\Engine\Data\Context;

class Indexer extends ObjectA
{
    public const LOCK_NAME = 'lightna_indexer';

    /** @AppConfig(entity) */
    protected array $entities;
    protected Scope $scope;
    protected Context $context;
    protected ChangelogHandler $changelogHandler;
    protected QueueHandler $queueHandler;
    protected Database $db;

    public function reindex(string $entity): void
    {
        $index = $this->getEntityIndex($entity);
        foreach ($this->scope->getList() as $scope) {
            $this->context->scope = $scope;
            $this->refreshAll($index);
        }
    }

    protected function refreshAll(IndexInterface $index): void
    {
        $lastId = null;
        while ($ids = $index->scan($lastId)) {
            $index->refresh($ids);
            $lastId = end($ids);
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
        foreach ($this->scope->getList() as $scope) {
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
