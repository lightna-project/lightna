<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Storage;

use Generator;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Storage\Database\Client;
use Lightna\Engine\App\Update\Schema\Storage\Database as Schema;

class Database extends ObjectA implements StorageInterface
{
    protected Client $client;
    /** @AppConfig(storage/database/options/serializer) */
    protected string $serializer;
    protected bool $batch = false;
    protected array $batchSet = [];
    protected array $batchUnset = [];
    protected int $keysBatchSize = 1000;

    public function set(string $key, mixed $value): void
    {
        if ($this->batch) {
            $this->batchSet[$key] = $value;
        } else {
            $this->client->query($this->getInsertQuery($key, $value));
        }
    }

    public function unset(string $key): void
    {
        if ($this->batch) {
            $this->batchUnset[$key] = 1;
        } else {
            $this->client->sql(
                $this->client->delete()
                    ->from(Schema::TABLE_NAME)
                    ->where(['key = ?' => $key])
            );
        }
    }

    public function get(string $key): string|array
    {
        $result = $this->client->fetchCol(
            $this->client->select()
                ->from(['s' => Schema::TABLE_NAME])
                ->where(['s.key = ?' => $key]),
            'value',
            'key'
        );

        return isset($result[$key]) ? $this->unserializeValue($result[$key]) : '';
    }

    public function getList(array $keys): array
    {
        $select = $this->client->select(Schema::TABLE_NAME);
        $select->where->in('key', $keys);
        $result = $this->client->fetchCol($select, 'value', 'key');

        $list = [];
        foreach ($keys as $key) {
            $list[$key] = isset($result[$key]) ? $this->unserializeValue($result[$key]) : '';
        }

        return $list;
    }

    public function batch(): void
    {
        $this->batch = true;
    }

    public function flush(): void
    {
        $this->client->beginTransaction();
        if ($this->batchUnset) {
            $delete = $this->client->delete(Schema::TABLE_NAME);
            $delete->where->in('key', array_keys($this->batchUnset));
            $this->client->sql($delete);
        }
        foreach ($this->batchSet as $key => $value) {
            $this->client->query($this->getInsertQuery($key, $value));
        }
        $this->client->commit();

        $this->batch = false;
        $this->batchSet = [];
        $this->batchUnset = [];
    }

    protected function getInsertQuery(string $key, mixed $value): string
    {
        $data = ['key' => $key, 'value' => $this->serializeValue($value),];
        $insert = $this->client->insert()
            ->into(Schema::TABLE_NAME)
            ->values($data);

        return $this->client->buildSqlString($insert)
            . 'ON DUPLICATE KEY UPDATE value = VALUES(value)';
    }

    protected function serializeValue(mixed $value): string
    {
        return match ($this->serializer) {
            'json' => json($value),
            'igbinary' => igbinary_serialize($value),
        };
    }

    protected function unserializeValue(string $value): mixed
    {
        return match ($this->serializer) {
            'json' => json_decode($value, true),
            'igbinary' => igbinary_unserialize($value),
        };
    }

    public function keys(string $prefix): Generator
    {
        $cursor = 0;
        while ($batch = $this->getKeysBatch($prefix, $cursor)) {
            foreach ($batch as $row) {
                yield $row['key'];
            }
            /** @noinspection PhpUndefinedVariableInspection */
            $cursor = $row['id'];

            // Check for last batch, prevent extra query
            if (count($batch) < $this->keysBatchSize) {
                break;
            }
        }
    }

    protected function getKeysBatch(string $prefix, int $cursor): array
    {
        return $this->client->fetch($this->getKeysBatchSelect($prefix, $cursor));
    }

    protected function getKeysBatchSelect(string $prefix, int $cursor): Select
    {
        return $this->client->select()
            ->from(['s' => Schema::TABLE_NAME])
            ->columns(['id', 'key'])
            ->where([
                'id > ?' => $cursor,
                '`key` like ?' => $prefix . '%',
            ])
            ->order(['id'])
            ->limit($this->keysBatchSize);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
