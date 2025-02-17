<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Entity\EntityA;
use Lightna\Engine\App\Entity\State;
use Lightna\Engine\App\Index\Changelog\Handler as ChangelogHandler;
use Lightna\Engine\App\Index\IndexInterface;
use Lightna\Engine\App\Index\Queue\Handler as QueueHandler;
use Lightna\Engine\App\Query\Index\Queue as QueueQuery;
use Lightna\Engine\App\State\Index as IndexState;

class Indexer extends ObjectA
{
    protected const QUEUE_LOCK = 'lightna_indexer_queue';
    protected const QUEUE_BLOCK = 'indexer/queue/block';
    protected const QUEUE_STOP = 'indexer/queue/stop';
    /** @AppConfig(backend:indexer/queue/lock_wait_interval_ms) */
    protected int $lockWaitIntervalMs;
    /** @AppConfig(backend:indexer/queue/lock_wait_print_interval) */
    protected int $lockWaitPrintInterval;
    /** @AppConfig(backend:indexer/queue/allowed_check_interval_ms) */
    protected int $allowedCheckIntervalMs;
    public array $stats;

    /** @AppConfig(entity) */
    protected array $entities;
    protected Scope $scope;
    protected Context $context;
    protected ChangelogHandler $changelogHandler;
    protected QueueHandler $queueHandler;
    protected Lock $lock;
    protected State $state;
    protected QueueQuery $queueQuery;

    public function reindex(string $entityCode, ?int $onlyScope = null): void
    {
        $this->resetStats();
        $index = $this->getEntityIndex($entityCode);

        foreach ($this->scope->getList() as $scope) {
            if ($onlyScope && $onlyScope !== $scope) {
                continue;
            }

            $this->context->scope = $scope;
            $this->refreshAll($index);

            if (!$this->getEntity($entityCode)::SCOPED) break;
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
        $this->lockQueue();
        try {
            $this->validateQueueBlock(false);
            $this->setQueueStopFlag(false);

            $this->reindexOutdated();
            $this->changelogHandler->process();
            $this->queueHandler->process();
        } finally {
            $this->unlockQueue();
        }
    }

    public function processBatch(string $entityCode, array $batch): void
    {
        foreach ($this->scope->getList() as $scope) {
            $this->context->scope = $scope;
            $this->getEntityIndex($entityCode)->refresh($batch);

            if (!$this->getEntity($entityCode)::SCOPED) break;
        }
    }

    public function getEntityIndex(string $entityCode): ?IndexInterface
    {
        if (
            (!$indexClass = $this->entities[$entityCode]['index'] ?? null)
            || $this->getEntity($entityCode)->getStorage()->isReadOnly()
        ) {
            return null;
        }

        return getobj($indexClass);
    }

    protected function getEntity(string $entityCode): EntityA
    {
        return getobj($this->entities[$entityCode]['entity']);
    }

    protected function lockQueue(): void
    {
        if (!$this->lock->get(static::QUEUE_LOCK)) {
            throw new UserException('Queue is already running or locked by another index command');
        }
    }

    protected function unlockQueue(): void
    {
        $this->lock->release(static::QUEUE_LOCK);
    }

    public function blockQueue(int $timeout = 10): void
    {
        $this->stopQueue($timeout);
        $this->state->set(static::QUEUE_BLOCK, [true]);
        $this->unlockQueue();
    }

    protected function stopQueue(int $timeout = 10): void
    {
        $this->waitQueueLock($timeout);
        $this->setQueueStopFlag(false);
        if (!$this->lock->get(static::QUEUE_LOCK)) {
            throw new UserException('Queue stop timeout "' . $timeout . '" exceeded, try again.');
        }
    }

    protected function setQueueStopFlag(bool $value): void
    {
        $this->state->set(static::QUEUE_STOP, [$value]);
    }

    protected function waitQueueLock(int $timeout = 10): void
    {
        $mcsLeft = $timeout * 1000000;
        $mcsInterval = $this->lockWaitIntervalMs * 1000;
        $start = time();
        $printedAt = 0;

        $this->setQueueStopFlag(true);
        while ($mcsLeft > 0 && !$this->lock->get(static::QUEUE_LOCK)) {
            usleep($mcsInterval);
            $mcsLeft -= $mcsInterval;
            $waiting = time() - $start;

            if ($waiting % $this->lockWaitPrintInterval === 0 && !in_array($waiting, [$printedAt, $timeout])) {
                echo "\nWaiting for queue lock $waiting seconds";
                $printedAt = $waiting;
            }
            $this->setQueueStopFlag(true);
        }
    }

    public function unblockQueue(): void
    {
        $this->state->set(static::QUEUE_BLOCK, [false]);
    }

    public function isQueueBlocked(): bool
    {
        return $this->state->get(static::QUEUE_BLOCK) === [true];
    }

    public function validateQueueBlock(bool $required): void
    {
        if ($this->isQueueBlocked() !== $required) {
            throw new UserException(
                $required ?
                    'Queue must be blocked, run index.queue.block to block.' :
                    'Queue is blocked, run index.queue.unblock to unblock.'
            );
        }
    }

    protected function isQueueStopped(): bool
    {
        return $this->state->get(static::QUEUE_STOP) === [true];
    }

    public function validateQueueAllowed(): void
    {
        static $lastCheckMct;
        if (!is_null($lastCheckMct)) {
            if ((microtime(true) - $lastCheckMct) * 1000 < $this->allowedCheckIntervalMs) {
                // Too early to check
                return;
            }
        }

        if ($this->isQueueStopped()) {
            throw new UserException('Queue has been stopped by another index command.');
        }
        $lastCheckMct = microtime(true);
    }

    protected function reindexOutdated(): void
    {
        $this->lockQueue();
        try {
            foreach ($this->getOutdatedEntities() as $code) {
                $this->validateQueueAllowed();

                $this->queueQuery->resetEntity($code);
                $this->reindex($code);

                $indexState = $this->getIndexState();
                $indexState->entities[$code]->rebuiltAt = microtime(true);
                $indexState->save();
            }
        } finally {
            $this->unlockQueue();
        }
    }

    public function getOutdatedEntities(): array
    {
        $outdated = [];
        foreach ($this->getIndexState()->entities as $entityCode => $status) {
            if (!isset($this->entities[$entityCode]) || !$this->getEntityIndex($entityCode)) {
                continue;
            }

            if (!$status->isUpToDate()) {
                $outdated[] = $entityCode;
            }
        }

        return $outdated;
    }

    public function getIndexState(): IndexState
    {
        return newobj(IndexState::class);
    }
}
