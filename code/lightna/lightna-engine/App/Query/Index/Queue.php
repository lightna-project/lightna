<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Query\Index;

use Exception;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Update;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Engine\App\Update\Schema\Index\Queue as Schema;

class Queue extends ObjectA
{
    protected Database $db;

    public function saveBatch(array $items): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($items as $entity => $entityIds) {
                foreach ($entityIds as $entityId) {
                    $this->addEntity($entity, $entityId);
                }
            }
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
        $this->db->commit();
    }

    protected function addEntity(string $entity, int $entityId): void
    {
        $tableIdent = $this->db->quoteIdentifier(Schema::TABLE_NAME);
        $entityValue = $this->db->quote($entity);
        $entityIdValue = $this->db->quote((string)$entityId);

        $this->db->query(
            'INSERT IGNORE INTO ' . $tableIdent . ' (entity, entity_id, status)' .
            "VALUES($entityValue, $entityIdValue, 'pending')"
        );
    }

    public function hasProcessingItems(): bool
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

    public function admitPendingItems(): void
    {
        $this->db->discreteWrite($this->getAdmitPendingItemsUpdate());
    }

    protected function getAdmitPendingItemsUpdate(): Update
    {
        return $this->db->update()
            ->table(Schema::TABLE_NAME)
            ->set(['status' => 'processing'])
            ->where(['status' => 'pending']);
    }

    public function getEntities(): array
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

    public function getEntityBatch(string $entity): array
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

    public function cleanBatch(string $entity, array $batch): void
    {
        $this->db->sql($this->getCleanBatchDelete($entity, $batch));
    }

    protected function getCleanBatchDelete(string $entity, array $batch): Delete
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

    public function reset(): void
    {
        $this->db->query('truncate table ' . $this->db->quoteIdentifier(Schema::TABLE_NAME));
    }

    public function resetEntity(string $code): void
    {
        $this->db->discreteWrite($this->getResetEntityDelete($code));
    }

    protected function getResetEntityDelete(string $code): Delete
    {
        return $this->db->delete()
            ->from(Schema::TABLE_NAME)
            ->where(['entity = ?' => $code]);
    }

    public function isEmpty(): bool
    {
        return empty($this->db->fetchOne($this->db->select(Schema::TABLE_NAME)));
    }

    public function getApproxRows(): int
    {
        return $this->db->structure->getApproxRows(Schema::TABLE_NAME);
    }
}
