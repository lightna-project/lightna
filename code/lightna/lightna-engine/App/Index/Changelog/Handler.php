<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Changelog;

use Exception;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Update;
use Lightna\Engine\App\Database;
use Lightna\Engine\App\Index\Changelog\Schema as ChangelogSchema;
use Lightna\Engine\App\Index\Queue\Schema as QueueSchema;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\App\Query\Product;

class Handler extends ObjectA
{
    /** @AppConfig(indexer/changelog/batch/handlers) */
    protected array $handlers;
    protected Database $db;
    protected Product $productQuery;

    public function process(): void
    {
        $this->admitItems();
        $this->processItems();
    }

    protected function admitItems(): void
    {
        if (!$this->hasProcessingItems()) {
            $this->admitPendingItems();
        }
    }

    protected function hasProcessingItems(): bool
    {
        return (bool)$this->db->fetchOne($this->getHasProcessingItemsSelect());
    }

    protected function getHasProcessingItemsSelect(): Select
    {
        return $this->db->select()
            ->from(ChangelogSchema::TABLE_NAME)
            ->where(['status' => 'processing'])
            ->limit(1);
    }

    protected function admitPendingItems(): void
    {
        $this->db->sql($this->getAdmitPendingItemsUpdate());
    }

    protected function getAdmitPendingItemsUpdate(): Update
    {
        return $this->db->update()
            ->table(ChangelogSchema::TABLE_NAME)
            ->set(['status' => 'processing'])
            ->where(['status' => 'pending']);
    }

    protected function getTables(): array
    {
        return $this->db->fetchCol($this->getTablesSelect());
    }

    protected function getTablesSelect(): Select
    {
        return $this->db->select()
            ->from(ChangelogSchema::TABLE_NAME)
            ->columns(['table'])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where(['status' => 'processing']);
    }

    protected function getTableBatch(string $table): array
    {
        $batch = [];
        foreach ($this->db->fetch($this->getTableBatchSelect($table)) as $row) {
            $batch[$row['primary_key']][$row['column']] = [
                'old_value' => $row['old_value'],
                'new_value' => $row['new_value'],
            ];
        }

        return $batch;
    }

    protected function getTableBatchSelect(string $table): Select
    {
        $select = $this->db->select()
            ->from(ChangelogSchema::TABLE_NAME)
            ->where([
                'table' => $table,
                'status' => 'processing',
            ]);
        $select->where->in('primary_key', $this->getTableBatchPrimaryKeys($table));

        return $select;
    }

    protected function getTableBatchPrimaryKeys(string $table): array
    {
        return $this->db->fetchCol($this->getTableBatchPrimaryKeysSelect($table));
    }

    protected function getTableBatchPrimaryKeysSelect(string $table): Select
    {
        return $this->db->select()
            ->from(ChangelogSchema::TABLE_NAME)
            ->columns(['primary_key'])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where([
                'table' => $table,
                'status' => 'processing',
            ])
            ->limit(1000);
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
            } elseif (is_string($old) && strlen($old) === ChangelogSchema::VALUE_MAX_LENGTH) {
                // Values are equal and are cut to VALUE_MAX_LENGTH, we can't determine if unchanged
                return true;
            }
        }

        return false;
    }

    protected function processItems(): void
    {
        foreach ($this->getTables() as $table) {
            while ($batch = $this->getTableBatch($table)) {
                if ($filteredBatch = $this->filterUnchanged($batch)) {
                    $indexBatch = $this->getIndexBatch($table, $filteredBatch);
                    $this->addIndexBatchDependencies($indexBatch);
                    $this->saveIndexBatch($indexBatch);
                }
                $this->cleanBatchFromChangelog($table, $batch);
            }
        }
    }

    protected function getIndexBatch(string $table, array $batch): array
    {
        $toIndex = [];
        foreach ($this->handlers as $class) {
            $handler = $this->getChangelogBatchHandler($class);
            foreach ($handler->handle($table, $batch) as $entity => $ids) {
                // Merge and maintain unique ids, avoiding using array_unique for a whole $toIndex array
                foreach ($ids as $id) {
                    $toIndex[$entity][$id] = $id;
                }
            }
        }

        return $toIndex;
    }

    protected function getChangelogBatchHandler(string $class): BatchHandlerAbstract
    {
        return getobj($class);
    }

    protected function addIndexBatchDependencies(array &$indexBatch): void
    {
        $productIds = $indexBatch['product'] ?? [];
        $parentIds = $productIds ? $this->productQuery->getParentsBatch($productIds) : [];
        $indexBatch['product'] = merge($productIds, $parentIds);
    }

    protected function saveIndexBatch(array $indexItems): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($indexItems as $entity => $entityIds) {
                foreach ($entityIds as $entityId) {
                    $this->insertIndexIntoQueue($entity, $entityId);
                }
            }
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
        $this->db->commit();
    }

    protected function insertIndexIntoQueue(string $entity, int $entityId): void
    {
        $tableIdent = $this->db->quoteIdentifier(QueueSchema::TABLE_NAME);
        $entityValue = $this->db->quote($entity);
        $entityIdValue = $this->db->quote((string)$entityId);

        $this->db->query(
            'INSERT IGNORE INTO ' . $tableIdent . ' (entity, entity_id, status)' .
            "VALUES($entityValue, $entityIdValue, 'pending')"
        );
    }

    protected function cleanBatchFromChangelog(string $table, array $batch): void
    {
        $this->db->sql($this->getCleanBatchFromChangelogDelete($table, $batch));
    }

    protected function getCleanBatchFromChangelogDelete(string $table, array $batch): Delete
    {
        $delete = $this->db->delete()
            ->from(ChangelogSchema::TABLE_NAME)
            ->where([
                'table' => $table,
                'status' => 'processing',
            ]);
        $delete->where->in('primary_key', array_keys($batch));

        return $delete;
    }
}
