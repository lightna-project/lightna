<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Version;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\State;

class Previous extends CommandA
{
    protected State $state;

    public function run(): void
    {
        $this->state->index->version = $this->state->index->getPreviousVersion();
        $this->state->save();
    }
}
