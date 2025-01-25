<?php

declare(strict_types=1);

namespace Lightna\Session\App\Handler;

interface HandlerInterface
{
    public function read(): string;

    public function prolong(): void;
}
