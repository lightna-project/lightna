<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Update;

use Lightna\Engine\App\Index\ValidEntityCodes;

class Entity extends UpdateA
{
    protected ValidEntityCodes $validEntityCodes;

    public function run(): void
    {
        $this->updateEntities(
            $this->validEntityCodes->get($this->getArgs()),
            (bool)$this->getOpt('multi'),
            (int)$this->getOpt('scope'),
        );
    }
}
