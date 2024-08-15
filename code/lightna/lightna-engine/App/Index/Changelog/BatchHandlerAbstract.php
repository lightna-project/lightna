<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Changelog;

use Lightna\Engine\App\ObjectA;

abstract class BatchHandlerAbstract extends ObjectA
{
    abstract public function handle(string $table, array $changelog): array;

    protected function collectIds(array $changelog, string $column): array
    {
        $ids = [];
        foreach ($changelog as $record) {
            foreach ($this->collectRecordIds($record, $column) as $id) {
                $ids[$id] = $id;
            }
        }

        return $ids;
    }

    protected function collectRecordIds(array $record, string $column): array
    {
        $ids = [];
        foreach ($this->collectRecordValues($record, $column) as $id) {
            $ids[$id] = (int)$id;
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
}
