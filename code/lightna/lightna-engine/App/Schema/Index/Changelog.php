<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Schema\Index;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Engine\App\Project\Database\SchemaUpdater;
use Lightna\Engine\App\Schema\Index\Triggers as TriggersSchema;

class Changelog extends ObjectA
{
    public const TABLE_NAME = 'lightna_indexer_changelog';
    public const VALUE_MAX_LENGTH = 16;

    protected Database $db;
    protected TriggersSchema $triggersSchema;
    protected SchemaUpdater $schemaUpdater;

    public function update(): void
    {
        if (!$this->db->isUsed()) {
            return;
        }

        $table = $this->schemaUpdater->createTable(static::TABLE_NAME);

        $table->addColumn('table', 'enum')->setValues($this->getTableColumnValues());
        $table->addColumn('column', 'enum')->setValues($this->getColumnColumnValues());
        $table->addColumn('primary_key', 'bigint', ['unsigned' => true]);
        $table->addColumn('status', 'enum')->setValues(['pending', 'processing']);
        $table->addColumn('old_value', 'string', ['length' => static::VALUE_MAX_LENGTH, 'notnull' => false]);
        $table->addColumn('new_value', 'string', ['length' => static::VALUE_MAX_LENGTH, 'notnull' => false]);
        $table->setPrimaryKey(['status', 'table', 'primary_key', 'column']);

        $this->schemaUpdater->update($table);
    }

    protected function getTableColumnValues(): array
    {
        // Add new values and keep existing values to avoid errors
        $values = merge(
            array_values($this->triggersSchema->getWatchedTables()),
            array_values($this->schemaUpdater->getExistingEnumValues(static::TABLE_NAME, 'table')),
        );

        $values = array_unique($values);
        sort($values);

        return $values;
    }

    protected function getColumnColumnValues(): array
    {
        $watchedTables = $this->triggersSchema->getWatchedTables();
        $columns = [];
        foreach ($this->triggersSchema->getWatchedColumns() as $watchedTable => $watchedColumn) {
            if (!isset($watchedTables[$watchedTable])) {
                continue;
            }
            $columns = merge($columns, $watchedColumn);
        }

        // Add new values and keep existing values to avoid errors
        $values = merge(
            array_values($columns),
            array_values($this->schemaUpdater->getExistingEnumValues(static::TABLE_NAME, 'column')),
        );

        $values = array_unique($values);
        sort($values);

        return $values;
    }
}
