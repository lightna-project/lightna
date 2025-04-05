<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Router\Action;

interface ActionInterface
{
    public function process(): void;
}
