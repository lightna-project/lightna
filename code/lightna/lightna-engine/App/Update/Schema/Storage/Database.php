<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Update\Schema\Storage;

use Exception;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Storage\Database\Client as StorageDatabase;

class Database extends ObjectA
{
    public const TABLE_NAME = 'lightna_storage';
    protected StorageDatabase $storageDatabase;
    /** @AppConfig(storage/database/options/dbname) */
    protected string $storageDbname;

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
        if ($this->getCurrentSchema() !== $this->getRequiredSchema()) {
            throw new Exception(
                'Table schema for "' . static::TABLE_NAME . '" requires changes. Expected: '
                . "\n\n" . $this->getRequiredSchema() . "\n\n"
            );
        }
    }

    protected function getRequiredSchema(): string
    {
        return 'CREATE TABLE `lightna_storage` (
  `key` varchar(255) NOT NULL,
  `value` mediumblob DEFAULT NULL,
  PRIMARY KEY (`key`)
)';
    }

    protected function getCurrentSchema(): string
    {
        return $this->storageDatabase->structure->getCreateTable(static::TABLE_NAME);
    }
}
