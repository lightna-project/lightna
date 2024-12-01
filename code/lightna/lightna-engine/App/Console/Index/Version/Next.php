<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Version;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\State;

class Next extends CommandA
{
    protected State $state;

    public function run(): void
    {
        $this->state->index->version = $this->state->index->getNextVersion();
        $this->state->index->bindToBuild = 0;
        $this->state->save();
    }
}
