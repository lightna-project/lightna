<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Database\Doctrine;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Lightna\Engine\App\ObjectA;

abstract class AbstractSchemaUpdater extends ObjectA
{
    /** databaseConnectionParams need to be extended  */
    protected array $databaseConnectionParams;

    protected array $doctrineConnectionParams;
    protected Connection $doctrineConnection;
    protected AbstractSchemaManager $schemaManager;

    /** @noinspection PhpUnused */
    protected function defineDoctrineConnectionParams(): void
    {
        $this->doctrineConnectionParams = [
            'host' => $this->databaseConnectionParams['host'],
            'port' => $this->databaseConnectionParams['port'],
            'user' => $this->databaseConnectionParams['username'],
            'password' => $this->databaseConnectionParams['password'],
            'dbname' => $this->databaseConnectionParams['dbname'],
            'driver' => $this->databaseConnectionParams['driver'] ?? 'pdo_mysql',
        ];
    }

    /** @noinspection PhpUnused */
    protected function defineDoctrineConnection(): void
    {
        $this->doctrineConnection = DriverManager::getConnection(
            $this->doctrineConnectionParams,
            new Configuration(),
        );
    }

    /** @noinspection PhpUnused */
    protected function defineSchemaManager(): void
    {
        $this->schemaManager = $this->doctrineConnection->createSchemaManager();
    }

    public function createTable(string $tableName): Table
    {
        return (new Schema())->createTable($tableName);
    }

    public function update(Table $toTable): void
    {
        foreach ($this->getSqlStatements($toTable) as $sqlStatement) {
            $this->doctrineConnection->executeQuery($sqlStatement);
        }
    }

    protected function getSqlStatements(Table $toTable): array
    {
        if (!$this->schemaManager->tableExists($toTable->getName())) {
            $fromSchema = new Schema();
        } else {
            $fromTable = $this->getExistingTableSchema($toTable->getName());
            $fromSchema = new Schema([$fromTable]);
        }

        $toSchema = new Schema([$toTable]);
        $schemaDiff = $this->schemaManager->createComparator()->compareSchemas($fromSchema, $toSchema);
        $platform = $this->doctrineConnection->getDatabasePlatform();

        return $platform->getAlterSchemaSQL($schemaDiff);
    }

    public function getExistingTableSchema(string $table): Table
    {
        return $this->schemaManager->introspectTable($table);
    }

    public function getExistingEnumValues(string $table, string $column): array
    {
        if (!$this->schemaManager->tableExists($table)) {
            return [];
        }

        $tableSchema = $this->schemaManager->introspectTable($table);
        if (!$tableSchema->hasColumn($column)) {
            return [];
        }

        return $tableSchema->getColumn($column)->getValues();
    }
}
