<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Queue;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Schema extends ObjectA
{
    public const TABLE_NAME = 'lightna_indexer_queue';

    /** @AppConfig(entity) */
    protected array $entity;
    protected Database $db;

    public function update(): void
    {
        $statement = $this->getQueueTableStatement();
        $currentStatement = $this->getCurrentQueueTableStatement();
        if ($currentStatement !== $statement) {
            if ($currentStatement !== '') {
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
        $tableRow = $this->db->query('show create table ' . static::TABLE_NAME)->next();

        return preg_replace('~\n\) ENGINE=.+$~', "\n)", $tableRow['Create Table']);
    }

    protected function getQueueTableStatement(): string
    {
        $tableName = static::TABLE_NAME;
        $enumEntitiesExpr = "'" . implode("','", $this->getEntityColumnValues()) . "'";

        return <<<SQL
CREATE TABLE `$tableName` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity` enum($enumEntitiesExpr) NOT NULL,
  `entity_id` bigint(20) NOT NULL,
  `status` enum('pending','processing') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `{$tableName}_uniq_key` (`entity`,`entity_id`,`status`),
  KEY `{$tableName}_status` (`status`),
  KEY `{$tableName}_status_entity` (`status`,`entity`),
  KEY `{$tableName}_status_entity_entity_id` (`status`,`entity`,`entity_id`)
)
SQL;
    }

    protected function getEntityColumnValues(): array
    {
        return array_keys($this->entity);
    }
}
