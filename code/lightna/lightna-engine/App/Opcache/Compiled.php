<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Opcache;

use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\Opcache;

class Compiled extends Opcache
{
    protected string $dir = COMPILED_DIR;

    public function getAppConfig(string $area = null): array
    {
        return merge(
            $this->load('config/' . ($area ?? LIGHTNA_AREA)),
            Bootstrap::getConfig(),
        );
    }
}
