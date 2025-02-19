<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Index\Changelog;

use Lightna\Magento\Backend\App\Index\EntityLink;

class Collect extends \Lightna\Engine\App\Index\Changelog\Collect
{
    protected EntityLink $entityLink;

    public function entityIds(string $table, array $changelog): array
    {
        $column = $this->entityLink->getColumn($table);
        $ids = $this->ids($changelog, $column);

        return $this->entityLink->getIds($table, $ids);
    }
}
