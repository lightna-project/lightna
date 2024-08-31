<?php

declare(strict_types=1);

namespace Lightna\AmastyLabel\App\Index\Changelog;

use Lightna\Engine\App\Database;
use Lightna\Engine\App\Index\Changelog\BatchHandlerAbstract;

class LabelBatchHandler extends BatchHandlerAbstract
{
    protected Database $db;

    public function handle(string $table, array $changelog): array
    {
        if ($this->isTableRelevant($table)) {
            return [
                'product' => $this->collectIds($changelog, 'product_id'),
            ];
        }

        return [];
    }

    protected function isTableRelevant(string $table): bool
    {
        return $table == 'amasty_label_index';
    }
}
