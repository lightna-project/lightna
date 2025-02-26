<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Schema\Index;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Queue extends ObjectA
{
    public const TABLE_NAME = 'lightna_indexer_queue';

    /** @AppConfig(entity) */
    protected array $entity;
    protected Database $db;

    public function update(): void
    {
        if (!$this->db->isUsed()) {
            return;
        }

        $statement = $this->getQueueTableStatement();
        $currentStatement = $this->getCurrentQueueTableStatement();
        if ($currentStatement !== $statement) {
            if ($currentStatement !== '') {
                echo cli_warning("\nWARNING: Table " . static::TABLE_NAME . " has been recreated.\n");
                $this->db->query('DROP TABLE ' . static::TABLE_NAME);
            }
            $this->db->query($statement);
        }
    }

    protected function getCurrentQueueTableStatement(): string
    {
        if (!($this->db->structure->getTableNames()[static::TABLE_NAME] ?? false)) {
            return '';
        }

        return $this->db->structure->getCreateTable(static::TABLE_NAME);
    }

    protected function getQueueTableStatement(): string
    {
        $tableName = static::TABLE_NAME;
        $enumEntitiesExpr = "'" . implode("','", $this->getEntityColumnValues()) . "'";

        return <<<SQL
CREATE TABLE `$tableName` (
  `entity` enum($enumEntitiesExpr) NOT NULL,
  `entity_id` bigint(20) NOT NULL,
  `status` enum('pending','processing') NOT NULL,
  PRIMARY KEY (`status`,`entity`,`entity_id`)
)
SQL;
    }

    protected function getEntityColumnValues(): array
    {
        return array_keys($this->entity);
    }
}
