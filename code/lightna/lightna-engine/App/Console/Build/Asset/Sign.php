<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Build\Asset;

use Lightna\Engine\App\Compiler\AssetSign as CompilerAssetSign;
use Lightna\Engine\App\Console\CommandA;

class Sign extends CommandA
{
    protected CompilerAssetSign $assetSign;

    public function run(): void
    {
        $this->assetSign->run();
    }
}
