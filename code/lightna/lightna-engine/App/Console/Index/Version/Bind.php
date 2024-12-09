<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Version;

use Lightna\Engine\App\Build;
use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\State;

class Bind extends CommandA
{
    protected State $state;
    protected Build $build;

    public function run(): void
    {
        $this->state->index->bindToBuild = $this->build->load('version');
        $this->state->save();
    }
}
