<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Sql\AbstractPreparableSql;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Lightna\Engine\App\Database\Structure;

abstract class DatabaseA extends ObjectA
{
    public Structure $structure;

    protected ?Adapter $adapter;
    protected ?Sql $sql;
    protected array $connection;

    protected function init(): void
    {
        $this->validateConnection();
        $connection = merge($this->connection, [
            'driver' => 'Pdo_Mysql',
            'charset' => 'utf8',
        ]);

        $this->adapter = new Adapter($connection);
        $this->sql = new Sql($this->adapter);
    }

    public function getAdapter(): Adapter
    {
        return $this->adapter;
    }

    public function select(mixed $table = null): Select
    {
        return $this->sql->select($table);
    }

    public function update(mixed $table = null): Update
    {
        return $this->sql->update($table);
    }

    public function delete(mixed $table = null): Delete
    {
        return $this->sql->delete($table);
    }

    public function insert(mixed $table = null): Insert
    {
        return $this->sql->insert($table);
    }

    public function beginTransaction(): void
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
    }

    public function commit(): void
    {
        $this->adapter->getDriver()->getConnection()->commit();
    }

    public function rollback(): void
    {
        $this->adapter->getDriver()->getConnection()->rollback();
    }

    public function sql(AbstractPreparableSql $sql): ResultInterface
    {
        return $this->sql->prepareStatementForSqlObject($sql)->execute();
    }

    public function query(string $sql, array $bind = []): ResultInterface
    {
        return $this->adapter->createStatement($sql, $bind)->execute();
    }

    public function fetch(AbstractPreparableSql $sql, string $key = null): array
    {
        $result = $this->sql($sql);
        $fetch = [];
        while ($row = $result->next()) {
            if ($key) {
                $fetch[$row[$key]] = $row;
            } else {
                $fetch[] = $row;
            }
        }

        return $fetch;
    }

    public function fetchOne(AbstractPreparableSql $sql): ?array
    {
        $result = $this->sql($sql)->next();

        return $result !== false ? $result : null;
    }

    public function fetchCol(AbstractPreparableSql $sql, ?string $colName = null, ?string $keyName = null): array
    {
        $result = $this->sql($sql);
        $fetch = [];
        $i = 0;
        while ($row = $result->next()) {
            $key = $keyName ? $row[$keyName] : $i++;
            $fetch[$key] = $colName ? $row[$colName] : reset($row);
        }

        return $fetch;
    }

    public function fetchOneCol(AbstractPreparableSql $sql): string|int|null
    {
        $result = $this->sql($sql)->next();

        return $result ? reset($result) : null;
    }

    public function buildSqlString(AbstractPreparableSql $sqlObject): string
    {
        return $this->sql->buildSqlString($sqlObject);
    }

    protected function validateConnection(): void
    {
        $required = ['host', 'port', 'username', 'dbname'];
        foreach ($required as $field) {
            if (empty($this->connection[$field])) {
                throw new Exception('Field "connection.' . $field . '" is required');
            }
        }
    }

    public function quote(string $value): string
    {
        return $this->adapter->platform->quoteValue($value);
    }

    public function quoteIdentifier(string $value): string
    {
        return $this->adapter->platform->quoteIdentifier($value);
    }

    public function getLock(string $name, int $timeout = 0): bool
    {
        $result = $this->query(
            'SELECT GET_LOCK(?, ?) as success',
            [$name, $timeout],
        )->next();

        return $result['success'] === 1;
    }

    public function releaseLock(string $name): void
    {
        $this->query('SELECT RELEASE_LOCK(?)', [$name]);
    }
}
