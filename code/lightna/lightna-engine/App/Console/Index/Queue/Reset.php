<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Queue;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\Query\Index\Changelog;
use Lightna\Engine\App\Query\Index\Queue;

class Reset extends CommandA
{
    protected Indexer $indexer;
    protected Queue $queue;
    protected Changelog $changelog;

    public function run(): void
    {
        $this->indexer->validateQueueBlock(true);
        $this->changelog->reset();
        $this->queue->reset();
    }
}
