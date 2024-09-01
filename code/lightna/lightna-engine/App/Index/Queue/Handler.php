<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Queue;

use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Update;
use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Handler extends ObjectA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Database $db;
    protected Indexer $indexer;

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
            ->from(Schema::TABLE_NAME)
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
            ->table(Schema::TABLE_NAME)
            ->set(['status' => 'processing'])
            ->where(['status' => 'pending']);
    }

    protected function processItems(): void
    {
        foreach ($this->getEntities() as $entity) {
            while ($batch = $this->getEntityBatch($entity)) {
                $this->indexer->processBatch($entity, $batch);
                $this->cleanBatchFromQueue($entity, $batch);
            }
        }
    }

    protected function getEntities(): array
    {
        return $this->db->fetchCol($this->getEntitiesSelect());
    }

    protected function getEntitiesSelect(): Select
    {
        return $this->db->select()
            ->from(Schema::TABLE_NAME)
            ->columns(['entity'])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where(['status' => 'processing']);
    }

    protected function getEntityBatch(string $entity): array
    {
        return $this->db->fetchCol($this->getEntityBatchSelect($entity), 'entity_id');
    }

    protected function getEntityBatchSelect(string $entity): Select
    {
        return $this->db->select()
            ->from(Schema::TABLE_NAME)
            ->where([
                'entity' => $entity,
                'status' => 'processing',
            ])
            ->limit(1000);
    }

    protected function cleanBatchFromQueue(string $entity, array $batch): void
    {
        $this->db->sql($this->getCleanBatchFromQueueDelete($entity, $batch));
    }

    protected function getCleanBatchFromQueueDelete(string $entity, array $batch): Delete
    {
        $delete = $this->db->delete()
            ->from(Schema::TABLE_NAME)
            ->where([
                'entity' => $entity,
                'status' => 'processing',
            ]);
        $delete->where->in('entity_id', $batch);

        return $delete;
    }
}
