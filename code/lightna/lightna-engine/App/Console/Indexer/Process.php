<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Indexer;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;


class Process extends CommandA
{
    protected Indexer $indexer;

    public function run(): void
    {
        $this->indexer->process();
    }
}
