<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Queue;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;

class Process extends CommandA
{
    protected Indexer $indexer;

    public function run(): void
    {
        $this->indexer->lockPartialReindex();
        try {
            $this->indexer->validatePartialReindexBlock(false);
            $this->indexer->process();
        } finally {
            $this->indexer->unlockPartialReindex();
        }
    }
}
