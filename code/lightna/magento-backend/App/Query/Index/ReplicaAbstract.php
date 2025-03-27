<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query\Index;

use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

abstract class ReplicaAbstract extends ObjectA
{
    protected Database $db;
    protected string $table;
    protected string $offsetField;
    protected int $rowsBatchSize;
    protected int $syncBatchSize;

    public function sync(): void
    {
        $from = 0;
        while ($to = $this->getNextTo($from)) {
            $this->updateReplica($from, $to);
            $this->cleanReplica($from, $to);
            $from = $to + 1;
        }

        $this->cleanReplicaRemainder($from);
    }

    /** @noinspection PhpUnused */
    protected function defineRowsBatchSize(): void
    {
        $this->rowsBatchSize = 40000;
    }

    /** @noinspection PhpUnused */
    protected function defineSyncBatchSize(): void
    {
        // Extension point

        $this->syncBatchSize = $this->rowsBatchSize;
    }

    protected function getNextTo(int $from): ?int
    {
        return $this->db->query(
            $this->getNextToQuery(),
            [$from, $this->syncBatchSize],
        )->next()['to'];
    }

    protected function getNextToQuery(): string
    {
        $offsetFieldExpr = $this->db->quoteIdentifier($this->offsetField);
        $tableExpr = $this->db->quoteIdentifier($this->table);

        return
            "select max($offsetFieldExpr) as \"to\" from (" .
            "    select distinct $offsetFieldExpr from $tableExpr" .
            "    where $offsetFieldExpr >= ?" .
            "    order by $offsetFieldExpr" .
            "    limit ?" .
            ") as t";
    }

    protected function updateReplica(int $from, int $to): void
    {
        $this->db->query($this->getUpdateReplicaQuery($from, $to));
    }

    protected function getUpdateReplicaQuery(int $from, int $to): string
    {
        $insert = $this->db->insert()
            ->into($this->table . '_replica')
            ->values($this->getUpdateReplicaSelect($from, $to));

        return $this->db->buildSqlString($insert) .
            ' ON DUPLICATE KEY UPDATE ' . $this->getInsertOnDuplicateFieldsExpression();
    }

    protected function getInsertOnDuplicateFieldsExpression(): string
    {
        $expr = $sep = '';
        foreach ($this->db->structure->getColumnsInfo()[$this->table] as $column) {
            if ($column['COLUMN_KEY'] === 'PRI') {
                continue;
            }
            $expr .= $sep . $this->db->quoteIdentifier($column['COLUMN_NAME'])
                . ' = VALUES(' . $this->db->quoteIdentifier($column['COLUMN_NAME']) . ')';
            $sep = ', ';
        }

        return $expr;
    }

    protected function getUpdateReplicaSelect(int $from, int $to): Select
    {
        return $this->db->select()
            ->from($this->table)
            ->where([
                $this->offsetField . ' >= ?' => $from,
                $this->offsetField . ' <= ?' => $to,
            ]);
    }

    protected function cleanReplica(int $from, int $to): void
    {
        $this->db->query($this->getCleanReplicaQuery(), [$from, $to]);
    }

    protected function getCleanReplicaQuery(): string
    {
        $offsetFieldExpr = $this->db->quoteIdentifier($this->offsetField);
        $tableExpr = $this->db->quoteIdentifier($this->table);
        $tableReplicaExpr = $this->db->quoteIdentifier($this->table . '_replica');

        return
            "DELETE replica " .
            "FROM $tableReplicaExpr replica " .
            "LEFT JOIN $tableExpr origin USING ($offsetFieldExpr) " .
            "WHERE replica.$offsetFieldExpr >= ? AND replica.$offsetFieldExpr <= ? AND origin.$offsetFieldExpr IS NULL";
    }

    protected function cleanReplicaRemainder(int $from): void
    {
        $this->db->discreteWrite($this->getCleanReplicaRemainderDelete($from));
    }

    protected function getCleanReplicaRemainderDelete(int $from): Delete
    {
        return $this->db
            ->delete($this->table . '_replica')
            ->where([$this->offsetField . ' >= ?' => $from]);
    }
}
