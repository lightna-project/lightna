<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Changelog;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\App\Index\EntityLink;

class Collect extends ObjectA
{
    protected EntityLink $entityLink;

    public function ids(array $changelog, string $column, string $type = 'int'): array
    {
        $ids = [];
        foreach ($changelog as $record) {
            foreach ($this->recordIds($record, $column, $type) as $id) {
                $ids[$id] = $id;
            }
        }

        return $ids;
    }

    public function recordIds(array $record, string $column, string $type = 'int'): array
    {
        $ids = [];
        foreach ($this->recordValues($record, $column) as $id) {
            $ids[$id] = $id;
            settype($ids[$id], $type);
        }

        return $ids;
    }

    public function recordValues(array $record, string $column): array
    {
        $ids = [];
        // Don't use "??" operator. It must fail when key is undefined.
        $old = $record[$column]['old_value'];
        $new = $record[$column]['new_value'];
        $old !== null && $ids[$old] = (string)$old;
        $new !== null && $ids[$new] = (string)$new;

        return $ids;
    }

    public function entityIds(string $table, array $changelog): array
    {
        $column = $this->entityLink->getColumn($table);
        $ids = $this->ids($changelog, $column);

        return $this->entityLink->getIds($table, $ids);
    }

    public function idsWithIgnore(array $changelog, string $column, array $ignore, string $type = 'int'): array
    {
        $ids = [];
        foreach ($changelog as $record) {
            if ($this->isOnlyFieldsChanged($record, $ignore)) {
                continue;
            }
            foreach ($this->recordIds($record, $column, $type) as $id) {
                $ids[$id] = $id;
            }
        }

        return $ids;
    }

    public function isOnlyFieldsChanged(array $record, array $fields): bool
    {
        return empty(array_diff($this->getChangedFields($record), $fields));
    }

    public function getChangedFields(array $record): array
    {
        $fields = [];
        foreach ($record as $field => $change) {
            if ($change['old_value'] !== $change['new_value']) {
                $fields[] = $field;
            }
        }

        return $fields;
    }
}
