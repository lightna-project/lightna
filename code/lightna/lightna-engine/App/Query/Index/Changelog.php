<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Query\Index;

use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Update;
use Lightna\Engine\App\Index\Changelog\Schema as ChangelogSchema;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Changelog extends ObjectA
{
    protected Database $db;

    public function hasProcessingItems(): bool
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

    public function admitPendingItems(): void
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

    public function getTables(): array
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

    public function getTableBatch(string $table): array
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

    public function cleanBatch(string $table, array $batch): void
    {
        $this->db->sql($this->getCleanBatchDelete($table, $batch));
    }

    protected function getCleanBatchDelete(string $table, array $batch): Delete
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
