<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Update\Schema\Index;

use Exception;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Engine\App\Update\Schema\Index\Changelog as ChangelogSchema;

class Triggers extends ObjectA
{
    /** @AppConfig(indexer/changelog/tables) */
    protected array $tablesConfig = [];
    protected Database $db;
    protected array $allTables;
    protected array $tableKeys;
    protected array $triggers = [];
    protected array $watchedTables = [];
    protected array $watchedColumns = [];
    protected array $forcedColumns = [];

    protected function defineAllTables(): void
    {
        $this->allTables = $this->db->structure->getTableNames();
    }

    protected function defineTableKeys(): void
    {
        $this->tableKeys = [];
        foreach ($this->db->structure->getStatistics() as $statistic) {
            foreach ($statistic as $row) {
                if ($row['INDEX_NAME'] === 'PRIMARY') {
                    $this->tableKeys[$row['TABLE_NAME']][] = $row['COLUMN_NAME'];
                }
            }
        }
    }

    protected function defineTriggers(): void
    {
        $this->triggers = [];
        foreach ($this->db->query('show triggers') as $row) {
            $this->triggers[$row['Trigger']] = $row;
        }
    }

    protected function defineWatchedTables(): void
    {
        foreach ($this->tablesConfig as $table => $forcedColumns) {
            if (!isset($this->allTables[$table])) {
                continue;
            }
            $this->watchedTables[$table] = $table;
            $this->forcedColumns[$table] = array_combine($forcedColumns, $forcedColumns);
        }
    }

    protected function defineWatchedColumns(): void
    {
        foreach ($this->db->structure->getColumnsInfo() as $table => $columns) {
            $this->watchedColumns[$table] = [];
            foreach ($columns as $column => $info) {
                if (!$this->isColumnAllowedToWatch($info)) {
                    continue;
                }
                if ($this->isColumnPrimaryKey($info)) {
                    $this->forcedColumns[$table][$column] = $column;
                }
                $this->watchedColumns[$table][$column] = $column;
            }
        }
    }

    public function update(): void
    {
        $this->updateTriggers();
        $this->removeTriggersFromUnwatchedTables();
    }

    protected function updateTriggers(): void
    {
        foreach ($this->watchedTables as $table) {
            foreach (['insert', 'update', 'delete'] as $event) {
                $this->updateTrigger($table, $event);
            }
        }
    }

    protected function removeTriggersFromUnwatchedTables(): void
    {
        $unwatchedTables = array_diff_assoc($this->allTables, $this->watchedTables);
        foreach ($unwatchedTables as $table) {
            $triggerNames = [];
            foreach (['update', 'delete', 'insert'] as $event) {
                $triggerNames[] = $this->getTriggerName($table, $event);
            }
            foreach ($triggerNames as $triggerName) {
                if (isset($this->triggers[$triggerName])) {
                    $this->db->query('DROP TRIGGER ' . $triggerName);
                }
            }
        }
    }

    protected function isColumnAllowedToWatch(array $columnInfo): bool
    {
        return $columnInfo['EXTRA'] !== 'on update current_timestamp()';
    }

    protected function isColumnPrimaryKey(array $info): bool
    {
        return $info['COLUMN_KEY'] === 'PRI' && in_array($info['DATA_TYPE'], ['tinyint', 'smallint', 'mediumint', 'int', 'bigint']);
    }

    protected function updateTrigger(string $table, string $event): void
    {
        $triggerName = $this->getTriggerName($table, $event);
        $statementBody = $this->getTriggerStatementBody($table, $event);
        $currentStatementBody = $this->triggers[$triggerName]['Statement'] ?? null;

        if ($currentStatementBody !== trim($statementBody, ';')) {
            if ($currentStatementBody) {
                $this->db->query('DROP TRIGGER ' . $triggerName);
            }
            $this->db->query($this->getTriggerStatement($table, $event));
        }
    }

    protected function getTriggerStatement(string $table, string $event): string
    {
        return 'CREATE TRIGGER ' . $this->getTriggerName($table, $event) .
            "\nAFTER " . strtoupper($event) . " ON " . $this->db->quoteIdentifier($table) . ' FOR EACH ROW ' .
            $this->getTriggerStatementBody($table, $event);
    }

    protected function getTriggerStatementBody(string $table, string $event): string
    {
        $body = 'BEGIN';
        if ($event === 'update') {
            $body .= $this->getIsRowChangedDeclaration($table);
        }

        foreach ($this->watchedColumns[$table] as $field) {
            $isForced = isset($this->forcedColumns[$table][$field]);
            $insertStatement = $this->getInsertIntoChangelogStatement($table, $event, $field);
            if ($event === 'update') {
                $fieldIdent = $this->db->quoteIdentifier($field);
                $condition = $isForced ? 'isRowChanged' : "NOT(NEW.$fieldIdent <=> OLD.$fieldIdent)";

                $body .=
                    "\n    IF($condition)" .
                    "\n        THEN $insertStatement;" .
                    "\n    END IF;";
            } else {
                $body .= "\n    $insertStatement;";
            }
        }
        $body .= "\nEND";

        return $body;
    }

    protected function getIsRowChangedDeclaration(string $table): string
    {
        $declaration = "\n    DECLARE isRowChanged INT;";
        $declaration .= "\n    SET isRowChanged = ";
        $or = '';
        foreach ($this->watchedColumns[$table] as $field) {
            $fieldIdent = $this->db->quoteIdentifier($field);
            $declaration .= "{$or}NOT(NEW.$fieldIdent <=> OLD.$fieldIdent)";
            $or = ' OR ';
        }

        return $declaration . ';';
    }

    protected function getInsertIntoChangelogStatement(string $table, string $event, string $field): string
    {
        $fieldIdent = $this->db->quoteIdentifier($field);
        $maxLength = ChangelogSchema::VALUE_MAX_LENGTH;
        $oldExpr = "OLD.$fieldIdent";
        $newExpr = "NEW.$fieldIdent";
        $oldExpr = "SUBSTRING($oldExpr, 1, $maxLength)";
        $newExpr = "SUBSTRING($newExpr, 1, $maxLength)";
        $fieldExpr = $this->db->quote($field);
        $oldExpr = $event === 'insert' ? 'NULL' : $oldExpr;
        $newExpr = $event === 'delete' ? 'NULL' : $newExpr;

        return 'INSERT INTO ' . ChangelogSchema::TABLE_NAME .
            ' (`table`, `primary_key`, `column`, `old_value`, `new_value`, `status`) VALUES (' .
            $this->db->quote($table) . ", " . $this->getPrimaryKeyExpr($table, $event) .
            ", $fieldExpr, $oldExpr, $newExpr, 'pending')" .
            ' ON DUPLICATE KEY UPDATE new_value = VALUES(new_value)';
    }

    protected function getPrimaryKeyExpr(string $table, string $event): string
    {
        $keys = $this->getTableKeys($table);
        $keysIdents = [];
        foreach ($keys as $key) {
            $ident = $this->db->quoteIdentifier($key);
            $keysIdents[$key] = $event === 'insert' ? 'NEW.' . $ident : 'OLD.' . $ident;
        }

        if (count($keys) === 1) {
            $keyExpr = reset($keysIdents);
        } else {
            $keyExpr = 'CONV(SUBSTRING(SHA1(CONCAT_WS("-", ' . implode(', ', $keysIdents) . ')), 1, 14), 16, 10)';
        }

        return $keyExpr;
    }

    protected function getTableKeys(string $table): array
    {
        $keys = $this->tableKeys[$table] ?? [];
        if (count($keys) === 0) {
            throw new Exception('Primary key for table "' . $table . '" not defined');
        }

        return $keys;
    }

    protected function getTriggerName(string $table, string $event): string
    {
        $suffix = match (strtolower($event)) {
            'insert' => 'ia',
            'update' => 'ua',
            'delete' => 'da',
        };

        // Reduce length by camel, name size limit is 64 characters
        return camel('li_' . $table . '_' . $suffix);
    }

    public function getWatchedTables(): array
    {
        return $this->watchedTables;
    }

    public function getMaxColumnLength(): int
    {
        $max = 1;
        foreach ($this->watchedColumns as $columns) {
            foreach ($columns as $column) {
                $len = strlen($column);
                $max = max($len, $max);
            }
        }

        return $max;
    }
}
