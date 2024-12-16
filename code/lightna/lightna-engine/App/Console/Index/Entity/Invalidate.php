<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Entity;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Index\ValidEntityCodes;
use Lightna\Engine\App\State\Index as IndexState;

class Invalidate extends CommandA
{
    protected ValidEntityCodes $validEntityCodes;
    protected IndexState $indexState;

    public function run(): void
    {
        $this->indexState->invalidate(
            $this->validEntityCodes->get($this->getArgs()),
        );
    }
}
