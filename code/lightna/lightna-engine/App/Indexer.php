<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Entity\State;
use Lightna\Engine\App\Index\Changelog\Handler as ChangelogHandler;
use Lightna\Engine\App\Index\IndexInterface;
use Lightna\Engine\App\Index\Queue\Handler as QueueHandler;
use Lightna\Engine\App\Project\Database;

class Indexer extends ObjectA
{
    protected const LOCK_PARTIAL = 'lightna_indexer_partial';
    protected const BLOCK_PARTIAL = 'indexer/partial/block';
    public array $stats;

    /** @AppConfig(entity) */
    protected array $entities;
    protected Scope $scope;
    protected Context $context;
    protected ChangelogHandler $changelogHandler;
    protected QueueHandler $queueHandler;
    protected Database $db;
    protected State $state;

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
        $this->changelogHandler->process();
        $this->queueHandler->process();
    }

    public function processBatch(string $entity, array $batch): void
    {
        foreach ($this->scope->getList() as $scope) {
            $this->context->scope = $scope;
            $this->getEntityIndex($entity)->refresh($batch);
        }
    }

    protected function getEntityIndex(string $entity): IndexInterface
    {
        return getobj($this->entities[$entity]['index']);
    }

    public function lockPartialReindex(): void
    {
        if (!$this->db->getLock(static::LOCK_PARTIAL)) {
            throw new UserException('Partial reindex is already running');
        }
    }

    public function unlockPartialReindex(): void
    {
        $this->db->releaseLock(static::LOCK_PARTIAL);
    }

    public function blockPartialReindex(): void
    {
        if (!$this->db->getLock(static::LOCK_PARTIAL)) {
            throw new UserException("Can't block running partial reindex, you need to kill it or wait.");
        }

        $this->state->set(static::BLOCK_PARTIAL, [true]);
        $this->unlockPartialReindex();
    }

    public function unblockPartialReindex(): void
    {
        $this->state->set(static::BLOCK_PARTIAL, [false]);
    }

    public function isPartialReindexBlocked(): bool
    {
        return $this->state->get(static::BLOCK_PARTIAL) === [true];
    }

    public function validatePartialReindexBlock(bool $required): void
    {
        if ($this->isPartialReindexBlocked() !== $required) {
            throw new UserException(
                $required ?
                    'Partial reindex must be blocked, run index.queue.block to block.' :
                    'Partial reindex is blocked, run index.queue.unblock to unblock.'
            );
        }
    }
}
