<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Update\Schema\Index;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Engine\App\Update\Schema\Index\Triggers as TriggersSchema;

class Changelog extends ObjectA
{
    public const TABLE_NAME = 'lightna_indexer_changelog';
    public const VALUE_MAX_LENGTH = 16;

    protected Database $db;
    protected TriggersSchema $triggersSchema;

    public function update(): void
    {
        $statement = $this->getChangelogTableStatement();
        $currentStatement = $this->getCurrentChangelogTableStatement();
        if ($currentStatement !== $statement) {
            if ($currentStatement !== '') {
                echo cli_warning("\nWARNING: Table " . static::TABLE_NAME . " has been recreated.\n");
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

        return $this->db->structure->getCreateTable(static::TABLE_NAME);
    }

    protected function getChangelogTableStatement(): string
    {
        $tablesEnumExpr = "'" . implode("','", $this->getTableColumnValues()) . "'";
        $maxColumnLength = $this->triggersSchema->getMaxColumnLength();
        $tableName = static::TABLE_NAME;
        $maxLength = static::VALUE_MAX_LENGTH;

        return <<<SQL
CREATE TABLE `$tableName` (
  `table` enum($tablesEnumExpr) NOT NULL,
  `column` varchar($maxColumnLength) NOT NULL,
  `primary_key` bigint(20) unsigned NOT NULL,
  `status` enum('pending','processing') NOT NULL,
  `old_value` varchar($maxLength) DEFAULT NULL,
  `new_value` varchar($maxLength) DEFAULT NULL,
  PRIMARY KEY (`status`,`table`,`primary_key`,`column`)
)
SQL;
    }

    protected function getTableColumnValues(): array
    {
        return $this->triggersSchema->getWatchedTables();
    }
}
