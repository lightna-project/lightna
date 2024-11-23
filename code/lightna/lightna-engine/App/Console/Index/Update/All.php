<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Update;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\Query\Index\Changelog;
use Lightna\Engine\App\Query\Index\Queue;

class All extends CommandA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;
    protected Queue $queue;
    protected Changelog $changelog;

    public function run(): void
    {
        $this->indexer->blockPartialReindex();

        try {
            $this->changelog->reset();
            $this->queue->reset();

            foreach ($this->entities as $code => $entity) {
                if (!isset($entity['index'])) {
                    continue;
                }

                $this->printStart('index ' . $entity['storage'] . ' ' . $code);

                $this->indexer->reindex($code);

                $stats = $this->indexer->stats;
                $this->printEnd($stats['count'] . ' items have been indexed in ' . $stats['time'] . ' seconds');
            }
        } finally {
            $this->indexer->unblockPartialReindex();
        }
    }
}
