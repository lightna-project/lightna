<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Schema;

use Lightna\Engine\App\Console\CommandA;

class Update extends CommandA
{
    /** @AppConfig(backend:cli/schema/update) */
    protected array $updatePool;

    public function run(): void
    {
        foreach ($this->updatePool as $update) {
            getobj($update)->update();
        }
    }
}
