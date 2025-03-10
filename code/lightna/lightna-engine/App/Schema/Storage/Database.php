<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Schema\Storage;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Storage\Database\SchemaUpdater;

class Database extends ObjectA
{
    public const TABLE_NAME = 'lightna_storage';
    /** @AppConfig(storage/database/options/dbname) */
    protected string $storageDbname;
    protected SchemaUpdater $schemaUpdater;

    public function update(): void
    {
        if ($this->isDbStorageUsed()) {
            $this->updateSchema();
        }
    }

    protected function isDbStorageUsed(): bool
    {
        return (bool)$this->storageDbname;
    }

    protected function updateSchema(): void
    {
        $table = $this->schemaUpdater->createTable(static::TABLE_NAME);

        $table->addColumn('id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('key', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('value', 'blob', ['length' => 16777215, 'notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['key']);

        $this->schemaUpdater->update($table);
    }
}
