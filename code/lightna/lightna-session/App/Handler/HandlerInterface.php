<?php

declare(strict_types=1);

namespace Lightna\Session\App\Handler;

interface HandlerInterface
{
    public function read(): array;

    public function prolong(): void;
}
