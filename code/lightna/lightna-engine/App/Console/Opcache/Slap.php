<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Opcache;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\State;

class Slap extends CommandA
{
    protected State $state;

    public function run(): void
    {
        $slap = $this->state->opcache->slap;
        $slap->time = time();
        $slap->length = (int)$this->getArg(1) ?: 60;
        $this->state->save();
    }
}