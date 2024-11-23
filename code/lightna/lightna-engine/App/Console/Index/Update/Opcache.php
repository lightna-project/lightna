<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Update;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;

class Opcache extends CommandA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;

    public function run(): void
    {
        $this->indexer->validatePartialReindexBlock(true);

        foreach ($this->entities as $code => $entity) {
            if ($entity['storage'] !== 'opcache' || !isset($entity['index'])) {
                continue;
            }

            $this->printStart('index ' . $entity['storage'] . ' ' . $code);

            $this->indexer->reindex($code);

            $stats = $this->indexer->stats;
            $this->printEnd($stats['count'] . ' items have been indexed in ' . $stats['time'] . ' seconds');
        }
    }
}
