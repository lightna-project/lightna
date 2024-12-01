<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Queue;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;

class Block extends CommandA
{
    protected Indexer $indexer;

    public function run(): void
    {
        $this->indexer->blockQueue(
            (int)($this->getOpt('wait') ?? 10),
        );
    }
}
