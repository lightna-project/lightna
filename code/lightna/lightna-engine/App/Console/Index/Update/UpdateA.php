<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Update;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\Query\Index\Queue;
use Lightna\Engine\App\State\Common;
use Lightna\Engine\App\UserException;

class UpdateA extends CommandA
{
    /** @AppConfig(storage) */
    protected array $storages;
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;
    protected Common $state;
    protected Queue $queue;

    public function updateEntities(array $entities, bool $multi = false, ?int $onlyScope = null): void
    {
        if (!$multi && $onlyScope) {
            throw new UserException('Scope can be specified only in multi mode');
        }

        $wasBlockedByCommand = false;
        if ($multi) {
            $this->indexer->validateQueueBlock(true);
        } else {
            if (!$this->indexer->isQueueBlocked()) {
                $this->indexer->blockQueue();
                $wasBlockedByCommand = true;
            }
        }

        $this->applyVersion();

        try {
            foreach ($entities as $code) {
                $this->printStart('index ' . $this->entities[$code]['storage'] . ' ' . $code);

                $this->queue->resetEntity($code);
                $this->indexer->reindex($code, $onlyScope);

                $stats = $this->indexer->stats;
                $this->printEnd($stats['count'] . ' items have been indexed in ' . $stats['time'] . ' seconds');
            }
        } finally {
            if ($wasBlockedByCommand) {
                $this->indexer->unblockQueue();
            }
        }
    }

    protected function applyVersion(): void
    {
        if ($this->getOpt('next-version')) {
            $this->state->index->version = $this->state->index->getNextVersion();
        }
    }
}
