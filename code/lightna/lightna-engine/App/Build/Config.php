<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Build;

use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\Build;

class Config extends Build
{
    protected string $dir;
    protected Build $build;

    public function init(array $data = []): void
    {
        $this->dir = Bootstrap::getAppliedConfigDir();
    }

    public function apply(): void
    {
        foreach (LIGHTNA_AREAS as $area) {
            $this->save($area, Bootstrap::getUnappliedAreaConfig($area));
        }
    }
}
