<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Update;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\State;

class Opcache extends CommandA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;
    protected State $state;

    public function run(): void
    {
        $this->indexer->validateQueueBlock(true);
        $this->applyVersion();

        foreach ($this->entities as $code => $entity) {
            if ($entity['storage'] !== 'opcache' || !isset($entity['index'])) {
                continue;
            }

            $this->printStart('index ' . $entity['storage'] . ' ' . $code);

            $this->indexer->reindex($code);

            $stats = $this->indexer->stats;
            $this->printEnd($stats['count'] . ' items have been indexed in ' . $stats['time'] . ' seconds');
        }
    }

    protected function applyVersion(): void
    {
        if ($this->getOpt('next-version')) {
            $this->state->index->version = $this->state->index->getNextVersion();
        }
    }
}
