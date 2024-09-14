<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Opcache;

use Lightna\Engine\App\Opcache;

class Compiled extends Opcache
{
    protected string $dir = COMPILED_DIR;

    public function loadAppConfig(string $scope = null): mixed
    {
        return require $this->dir . 'config/' . ($scope ?? LIGHTNA_AREA) . '.php';
    }
}
