<?php

declare(strict_types=1);

namespace Lightna\Magento\AdobeStaging\App;

use Exception;
use Laminas\Db\Sql\AbstractPreparableSql;
use Laminas\Db\Sql\Combine;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;

class Staging extends ObjectA
{
    /** @AppConfig(backend:staging/tables) */
    protected array $tables;
    protected array $tablesIdx;
    protected int $versionId;

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

    protected function defineVersionId(): void
    {
        $this->versionId = 1;
    }

    public function apply(AbstractPreparableSql $sql): void
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
        if (!$joins = $select->joins->getJoins()) {
            return;
        }

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
        if ($parent = $this->tablesIdx[$table]['parent']) {
            $search[] = $this->tablesIdx[$parent]['entity_id'];
        }
        if ($this->tablesIdx[$table]['entity_id']) {
            $search[] = $this->tablesIdx[$table]['entity_id'];
        }

        return $this->applyRowId($search, $on);
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
        } else {
            return [current($name), key($name)];
        }
    }

    protected function validateMainStagingTable(string $table): void
    {
        if ($this->tablesIdx[$table]['parent']) {
            throw new Exception(
                'Table "' . $table . '" is used as the first staging table in SELECT statement but it has parent table.'
                . ' Please use "' . $this->tablesIdx[$table]['parent'] . '" as a main table to make staging functioning.'
            );
        }
    }
}
