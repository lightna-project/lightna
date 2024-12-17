<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class Build extends Opcache
{
    protected string $dir = BUILD_DIR;

    public function getAppConfig(string $area = null): array
    {
        if (Bootstrap::getCompilerMode() === 'none') {
            return Bootstrap::getConfig();
        } else {
            return merge(
                $this->load('config/' . ($area ?? LIGHTNA_AREA)),
                Bootstrap::getConfig(),
            );
        }
    }
}
