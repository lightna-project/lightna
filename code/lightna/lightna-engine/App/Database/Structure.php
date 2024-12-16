<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Database;

use Laminas\Db\Metadata\MetadataInterface;
use Laminas\Db\Metadata\Source\Factory as MetadataFactory;
use Laminas\Db\Sql\TableIdentifier;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Structure extends ObjectA
{
    protected Database $db;
    protected ?MetadataInterface $metadata;
    protected array $tableNames;
    protected array $columnsInfo;
    protected array $statistics;

    /** @noinspection PhpUnused */
    protected function defineMetadata(): void
    {
        $this->metadata = MetadataFactory::createSourceFromAdapter($this->db->getAdapter());
    }

    /** @noinspection PhpUnused */
    protected function defineTableNames(): void
    {
        $this->tableNames = [];
        foreach ($this->metadata->getTableNames() as $name) {
            $this->tableNames[$name] = $name;
        }
    }

    public function getTableNames(): array
    {
        return $this->tableNames;
    }

    public function tableExists(string $table): bool
    {
        return isset($this->tableNames[$table]);
    }

    /** @noinspection PhpUnused */
    protected function defineColumnsInfo(): void
    {
        $select = $this->db->select()
            ->from(new TableIdentifier('COLUMNS', 'INFORMATION_SCHEMA'))
            ->where('TABLE_SCHEMA = database()');

        $this->columnsInfo = [];
        foreach ($this->db->fetch($select) as $row) {
            $this->columnsInfo[$row['TABLE_NAME']][$row['COLUMN_NAME']] = $row;
        }
    }

    public function getColumnsInfo(): array
    {
        return $this->columnsInfo;
    }

    /** @noinspection PhpUnused */
    protected function defineStatistics(): void
    {
        $select = $this->db->select()
            ->from(new TableIdentifier('STATISTICS', 'INFORMATION_SCHEMA'))
            ->where('TABLE_SCHEMA = database()');

        $this->statistics = [];
        foreach ($this->db->fetch($select) as $row) {
            $this->statistics[$row['TABLE_NAME']][] = $row;
        }
    }

    public function getStatistics(): array
    {
        return $this->statistics;
    }

    public function getCreateTable(string $table, bool $trimExtraInfo = true): string
    {
        $row = $this->db
            ->query('show create table ' . $this->db->quoteIdentifier($table))
            ->next();

        if (($def = $row['Create Table'] ?? '') && $trimExtraInfo) {
            $def = preg_replace('~\n\) ENGINE=.+$~', "\n)", $def);
        }

        return $def;
    }

    public function getApproxRows(string $table): int
    {
        $row = $this->db->query('show table status like ?', [$table])->next();

        return $row['Rows'];
    }
}
