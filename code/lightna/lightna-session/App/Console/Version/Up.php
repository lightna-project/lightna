<?php

declare(strict_types=1);

namespace Lightna\Session\App\Console\Version;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\State\Common;

class Up extends CommandA
{
    protected Common $state;

    public function run(): void
    {
        $this->state->session->version = time();
        $this->state->save();
    }
}
