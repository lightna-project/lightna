<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Version;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\State\Common;

class Unbind extends CommandA
{
    protected Common $state;

    public function run(): void
    {
        $this->state->index->bindToBuild = 0;
        $this->state->save();
    }
}
