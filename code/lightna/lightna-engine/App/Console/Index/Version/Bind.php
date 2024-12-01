<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Version;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Opcache\Compiled;
use Lightna\Engine\App\State;

class Bind extends CommandA
{
    protected State $state;
    protected Compiled $compiled;

    public function run(): void
    {
        $this->state->index->bindToBuild = $this->compiled->load('version');
        $this->state->save();
    }
}
