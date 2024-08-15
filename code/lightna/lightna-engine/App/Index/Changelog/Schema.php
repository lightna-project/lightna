<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Changelog;

use Lightna\Engine\App\Database;
use Lightna\Engine\App\Index\Triggers\Schema as TriggersSchema;
use Lightna\Engine\App\ObjectA;

class Schema extends ObjectA
{
    public const TABLE_NAME = 'lightna_indexer_changelog';

    protected Database $db;
    protected TriggersSchema $triggersSchema;

    public function update(): void
    {
        $statement = $this->getChangelogTableStatement();
        $currentStatement = $this->getCurrentChangelogTableStatement();
        if ($currentStatement !== $statement) {
            if ($currentStatement !== '') {
                $this->db->query('DROP TABLE ' . static::TABLE_NAME);
            }
            $this->db->query($statement);
        }
    }

    protected function getCurrentChangelogTableStatement(): string
    {
        if (!($this->db->structure->getTableNames()[static::TABLE_NAME] ?? false)) {
            return '';
        }
        $tableRow = $this->db->query('show create table ' . static::TABLE_NAME)->next();

        return preg_replace('~\n\) ENGINE=.+$~', "\n)", $tableRow['Create Table']);
    }

    protected function getChangelogTableStatement(): string
    {
        $tablesEnumExpr = "'" . implode("','", $this->getTableColumnValues()) . "'";
        $maxColumnLength = $this->triggersSchema->getMaxColumnLength();
        $tableName = static::TABLE_NAME;

        return <<<SQL
CREATE TABLE `$tableName` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table` enum($tablesEnumExpr) NOT NULL,
  `column` varchar($maxColumnLength) NOT NULL,
  `primary_key` bigint(20) unsigned NOT NULL,
  `status` enum('pending','processing') NOT NULL,
  `old_value` varchar(16) DEFAULT NULL,
  `new_value` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `{$tableName}_uniq_key` (`table`,`status`,`primary_key`,`column`),
  KEY `{$tableName}_status` (`status`),
  KEY `{$tableName}_table_status` (`table`,`status`),
  KEY `{$tableName}_table_status_primary_key` (`table`,`status`,`primary_key`)
)
SQL;
    }

    protected function getTableColumnValues(): array
    {
        return $this->triggersSchema->getWatchedTables();
    }
}
