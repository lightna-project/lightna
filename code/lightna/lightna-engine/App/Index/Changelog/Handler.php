<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Changelog;

use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Query\Index\Changelog;
use Lightna\Engine\App\Query\Index\Queue;
use Lightna\Engine\App\Update\Schema\Index\Changelog as Schema;

class Handler extends ObjectA
{
    protected Changelog $changelog;
    protected Queue $queue;
    protected Indexer $indexer;
    /** @AppConfig(backend:indexer/changelog/batch/collectors) */
    protected array $collectors;

    public function process(): void
    {
        $this->admitItems();
        $this->processItems();
    }

    protected function admitItems(): void
    {
        if (!$this->changelog->hasProcessingItems()) {
            $this->changelog->admitPendingItems();
        }
    }

    protected function processItems(): void
    {
        foreach ($this->changelog->getTables() as $table) {
            while ($batch = $this->changelog->getTableBatch($table)) {
                $this->indexer->validateQueueAllowed();
                if ($filteredBatch = $this->filterUnchanged($batch)) {
                    $this->processBatch($table, $filteredBatch);
                }
                $this->changelog->cleanBatch($table, $batch);
            }
        }
    }

    /**
     * Unchanged rows are filtered on trigger level, however if you delete rows then insert back,
     * these will be registered in changelog. This function makes the clean
     */
    protected function filterUnchanged(array $batch): array
    {
        foreach ($batch as $pk => $columns) {
            if (!$this->areColumnsChanged($columns)) {
                unset($batch[$pk]);
            }
        }

        return $batch;
    }

    protected function areColumnsChanged(array $columns): bool
    {
        foreach ($columns as $values) {
            $old = $values['old_value'];
            $new = $values['new_value'];
            if ($old !== $new) {
                return true;
            } elseif (is_string($old) && mb_strlen($old) >= Schema::VALUE_MAX_LENGTH) {
                // Values are equal and are cut to VALUE_MAX_LENGTH, we can't determine if unchanged
                return true;
            }
        }

        return false;
    }

    public function processBatch(string $table, array $changelogBatch): void
    {
        $indexBatch = $this->getIndexBatch($table, $changelogBatch);
        $this->addIndexBatchDependencies($indexBatch);
        $this->queue->saveBatch($indexBatch);
    }

    protected function getIndexBatch(string $table, array $batch): array
    {
        $toIndex = [];
        foreach ($this->collectors as $class) {
            $collector = $this->getChangelogCollector($class);
            foreach ($collector->collect($table, $batch) as $entity => $ids) {
                // Merge and maintain unique ids, avoiding using array_unique for a whole $toIndex array
                foreach ($ids as $id) {
                    $toIndex[$entity][$id] = $id;
                }
            }
        }

        return $toIndex;
    }

    protected function getChangelogCollector(string $class): CollectorInterface
    {
        return newobj($class);
    }

    protected function addIndexBatchDependencies(array &$indexBatch): void
    {
        // Extension point
    }
}
