<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Entity;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;

class Info extends CommandA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;

    public function run(): void
    {
        foreach ($this->entities as $code => $entity) {
            if (!$this->indexer->getEntityIndex($code)) {
                continue;
            }

            echo str_pad($code, 30) . $entity['storage'] . "\n";
        }
    }
}
