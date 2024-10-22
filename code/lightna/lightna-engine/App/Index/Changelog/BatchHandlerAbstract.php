<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Changelog;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Index\EntityLink;

abstract class BatchHandlerAbstract extends ObjectA
{
    protected Database $db;
    protected EntityLink $entityLink;

    abstract public function handle(string $table, array $changelog): array;

    protected function collectIds(array $changelog, string $column, string $type = 'int'): array
    {
        $ids = [];
        foreach ($changelog as $record) {
            foreach ($this->collectRecordIds($record, $column, $type) as $id) {
                $ids[$id] = $id;
            }
        }

        return $ids;
    }

    protected function collectRecordIds(array $record, string $column, string $type = 'int'): array
    {
        $ids = [];
        foreach ($this->collectRecordValues($record, $column) as $id) {
            $ids[$id] = $id;
            settype($ids[$id], $type);
        }

        return $ids;
    }

    protected function collectRecordValues(array $record, string $column): array
    {
        $ids = [];
        // Don't use "??" operator. It must fail when key is undefined.
        $old = $record[$column]['old_value'];
        $new = $record[$column]['new_value'];
        $old !== null && $ids[$old] = (string)$old;
        $new !== null && $ids[$new] = (string)$new;

        return $ids;
    }

    protected function collectEntityIds(string $table, array $changelog): array
    {
        $column = $this->entityLink->getColumn($table);
        $ids = $this->collectIds($changelog, $column);

        return $this->entityLink->getIds($table, $ids);
    }
}
