<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Schema\Storage;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Storage\Database\Client as StorageDatabase;
use Lightna\Engine\App\UserException;

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
            throw new UserException(
                "\nTable schema for \"" . static::TABLE_NAME . "\" requires changes:"
                . "\n\nActual:"
                . "\n\n    " . str_replace("\n", "\n    ", $this->getCurrentSchema())
                . "\n\nExpected:"
                . "\n\n    " . str_replace("\n", "\n    ", $this->getRequiredSchema())
                . "\n\n"
            );
        }
    }

    protected function getRequiredSchema(): string
    {
        return 'CREATE TABLE `lightna_storage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` mediumblob DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
)';
    }

    protected function getCurrentSchema(): string
    {
        return $this->storageDatabase->structure->tableExists(static::TABLE_NAME)
            ? $this->storageDatabase->structure->getCreateTable(static::TABLE_NAME)
            : '';
    }
}
