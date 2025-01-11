<?php

declare(strict_types=1);

namespace Lightna\Magento\Staging\App;

use Exception;
use Laminas\Db\Sql\AbstractPreparableSql;
use Laminas\Db\Sql\Combine;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\TableIdentifier;
use Lightna\Engine\App\Index\Changelog\Handler as ChangelogHandler;
use Lightna\Engine\App\ObjectA;

class Staging extends ObjectA
{
    /** @AppConfig(backend:staging/tables) */
    protected array $tables;
    protected array $tablesIdx;
    protected int $versionId;
    protected Query\Staging $query;
    protected ChangelogHandler $changelogHandler;

    /** @noinspection PhpUnused */
    protected function defineTablesIdx(): void
    {
        $this->tablesIdx = [];
        foreach ($this->tables as $parentTable => $parent) {
            $this->tablesIdx[$parentTable] = [
                'parent' => null,
                'entity_id' => $parent['entity_id'],
            ];
            foreach ($parent['relations'] as $relationTable => $relationEntityId) {
                $this->tablesIdx[$relationTable] = [
                    'parent' => $parentTable,
                    'entity_id' => $relationEntityId,
                ];
            }
        }
    }

    /** @noinspection PhpUnused */
    protected function defineVersionId(): void
    {
        $this->versionId = $this->query->getVersionId();
    }

    public function applyToQuery(AbstractPreparableSql $sql): void
    {
        if (instance_of($sql, Select::class)) {
            $this->applyToSelect($sql);
        } elseif (instance_of($sql, Combine::class)) {
            $this->applyToCombine($sql);
        }
    }

    protected function applyToCombine(AbstractPreparableSql $combine): void
    {
        /** @var Combine $combine */
        $state = $combine->getRawState(Combine::COMBINE);
        foreach ($state as $item) {
            if (isset($item['select'])) {
                $this->applyToSelect($item['select']);
            }
        }
    }

    protected function applyToSelect(AbstractPreparableSql $select): void
    {
        $joins = $select->joins->getJoins();
        $state = $select->getRawState();
        $select->reset(Select::JOINS);
        $versionTable = null;
        [$name, $as] = $this->getName($state[Select::TABLE]);
        if (isset($this->tablesIdx[$name])) {
            $versionTable = $state[Select::TABLE];
        }

        foreach ($joins as $join) {
            [$name, $as] = $this->getName($join['name']);
            if (isset($this->tablesIdx[$name])) {
                $versionTable ??= $join['name'];
                $join['on'] = $this->getStagingJoinCondition($name, $join['on']);
            }
            $select->join(...$join);
        }

        if ($versionTable !== null) {
            [$name, $as] = $this->getName($versionTable);
            $this->validateMainStagingTable($name);

            $select->where([
                "$as.created_in <= ?" => $this->versionId,
                "$as.updated_in > ?" => $this->versionId,
            ]);
        }
    }

    protected function getStagingJoinCondition(string $table, mixed $on): mixed
    {
        $search = [];
        if ($parent = $this->getTableParent($table)) {
            $search[] = $this->getEntityIdColumn($parent);
        }
        if ($column = $this->getEntityIdColumn($table)) {
            $search[] = $column;
        }

        return $this->applyRowId($search, $on);
    }

    public function getTableParent(string $table): ?string
    {
        return $this->tablesIdx[$table]['parent'] ?? null;
    }

    public function getEntityIdColumn(string $table): ?string
    {
        return $this->tablesIdx[$table]['entity_id'] ?? null;
    }

    protected function applyRowId(array $search, mixed $condition): mixed
    {
        if (is_object($condition) && instance_of($condition, Expression::class)) {
            return $this->applyRowIdToExpression($search, $condition);
        } else {
            return $this->applyRowIdToString($search, $condition);
        }
    }

    protected function applyRowIdToString(array $search, mixed $condition): string
    {
        return str_replace($search, 'row_id', $condition);
    }

    protected function applyRowIdToExpression(array $search, Expression $condition): Expression
    {
        $condition->setExpression(
            $this->applyRowIdToString($search, $condition->getExpression())
        );

        return $condition;
    }

    protected function getName(mixed $name): array
    {
        if (is_string($name)) {
            return [$name, $name];
        } elseif (is_array($name)) {
            return [current($name), key($name)];
        } elseif (instance_of($name, TableIdentifier::class)) {
            return [$name->getTable(), $name->getTable()];
        } else {
            throw new \Exception('Unknown table name type');
        }
    }

    protected function validateMainStagingTable(string $table): void
    {
        if ($parent = $this->getTableParent($table)) {
            throw new Exception(
                'Table "' . $table . '" is used as the first staging table in SELECT statement but it has parent table.'
                . ' Please use "' . $parent . '" as a main table to make staging functioning.'
            );
        }
    }

    public function applyNewVersion(): void
    {
        $version = $this->query->getVersionId();
        $previousVersion = $this->query->getPreviousVersionId();
        if ($version === $previousVersion) {
            return;
        }

        $this->addEntitiesToChangelog($previousVersion, $version);
        $this->query->setPreviousVersionId($version);
    }

    protected function addEntitiesToChangelog(int $fromVersion, int $toVersion): void
    {
        foreach (array_keys($this->tables) as $table) {
            if ($ids = $this->query->getChangedEntityIds($table, $fromVersion, $toVersion)) {
                $this->changelogHandler->processBatch(
                    $table,
                    $this->getChangelogForTable($table, $ids)
                );
            }
        }
    }

    protected function getChangelogForTable(string $table, array $ids): array
    {
        $entityKey = $this->tablesIdx[$table]['entity_id'];
        $changelog = [];
        foreach ($ids as $id) {
            $changelog[] = [
                $entityKey => ['old_value' => $id, 'new_value' => $id],
                'staging_version' => ['old_value' => 'old', 'new_value' => 'new'],
            ];
        }

        return $changelog;
    }
}
