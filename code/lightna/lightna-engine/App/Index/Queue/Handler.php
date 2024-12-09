<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Queue;

use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Query\Index\Queue;

class Handler extends ObjectA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Queue $queue;
    protected Indexer $indexer;

    public function process(): void
    {
        $this->admitItems();
        $this->processItems();
    }

    protected function admitItems(): void
    {
        if (!$this->queue->hasProcessingItems()) {
            $this->queue->admitPendingItems();
        }
    }

    protected function processItems(): void
    {
        foreach ($this->queue->getEntities() as $entity) {
            $hasIndexAvailable = $this->indexer->getEntityIndex($entity);

            while ($batch = $this->queue->getEntityBatch($entity)) {
                $this->indexer->validateQueueAllowed();
                if ($hasIndexAvailable) {
                    $this->indexer->processBatch($entity, $batch);
                }
                $this->queue->cleanBatch($entity, $batch);
            }
        }
    }
}
