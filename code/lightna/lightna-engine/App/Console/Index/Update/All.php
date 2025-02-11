<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Update;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\Query\Index\Changelog;
use Lightna\Engine\App\Query\Index\Queue;
use Lightna\Engine\App\State\Common;

class All extends CommandA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;
    protected Common $state;
    protected Queue $queue;
    protected Changelog $changelog;

    public function run(): void
    {
        $wasBlockedByCommand = false;
        if (!$this->indexer->isQueueBlocked()) {
            $this->indexer->blockQueue();
            $wasBlockedByCommand = true;
        }
        $this->applyVersion();

        try {
            $this->changelog->reset();
            $this->queue->reset();

            foreach ($this->entities as $code => $entity) {
                if (!$this->indexer->getEntityIndex($code)) {
                    continue;
                }

                $this->printStart('index ' . $entity['storage'] . ' ' . $code . ' ');

                $this->indexer->reindex($code);

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
