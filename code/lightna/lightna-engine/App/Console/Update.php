<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console;

class Update extends CommandA
{
    /** @AppConfig(backend:cli/update) */
    protected array $updatePool;

    public function run(): void
    {
        foreach ($this->updatePool as $update) {
            getobj($update)->update();
        }
    }
}
