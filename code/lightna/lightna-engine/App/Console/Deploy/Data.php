<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Deploy;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;

class Data extends CommandA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;

    public function run(): void
    {
        foreach ($this->entities as $code => $entity) {
            if ($entity['storage'] === 'opcache' || !isset($entity['index'])) {
                continue;
            }

            $this->printStart('index ' . $entity['storage'] . ' ' . $code);
            $this->indexer->reindex($code);
            $this->printEnd();
        }
    }
}
