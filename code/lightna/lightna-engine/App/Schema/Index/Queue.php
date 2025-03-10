<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Schema\Index;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Engine\App\Project\Database\SchemaUpdater;

class Queue extends ObjectA
{
    public const TABLE_NAME = 'lightna_indexer_queue';

    /** @AppConfig(entity) */
    protected array $entity;
    protected Database $db;
    protected SchemaUpdater $schemaUpdater;

    public function update(): void
    {
        if (!$this->db->isUsed()) {
            return;
        }

        $table = $this->schemaUpdater->createTable(static::TABLE_NAME);

        $table->addColumn('entity', 'enum')->setValues($this->getEntityColumnValues());
        $table->addColumn('entity_id', 'bigint', ['unsigned' => true]);
        $table->addColumn('status', 'enum')->setValues(['pending', 'processing']);
        $table->setPrimaryKey(['status', 'entity', 'entity_id']);

        $this->schemaUpdater->update($table);
    }

    protected function getEntityColumnValues(): array
    {
        $values = merge(
            $this->schemaUpdater->getExistingEnumValues(static::TABLE_NAME, 'entity'),
            array_keys($this->entity),
        );

        $values = array_unique($values);
        sort($values);

        return $values;
    }
}
