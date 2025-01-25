<?php

declare(strict_types=1);

namespace Lightna\Engine\App\State\Common;

use Lightna\Engine\App\Build;
use Lightna\Engine\Data\DataA;

class Index extends DataA
{
    public string $version = 'a';
    public string|int $bindToBuild = 0;

    protected Build $build;

    protected function init(array $data = []): void
    {
        parent::init($data);

        $this->bindToBuild();
    }

    protected function bindToBuild(): void
    {
        if (LIGHTNA_AREA !== 'frontend') {
            return;
        }

        if ($this->bindToBuild && $this->bindToBuild !== $this->build->getVersion()) {
            $this->version = $this->getNextVersion();
        }
    }

    public function getNextVersion(): string
    {
        return $this->getIncrementedVersion(1);
    }

    public function getPreviousVersion(): string
    {
        return $this->getIncrementedVersion(-1);
    }

    protected function getIncrementedVersion(int $inc): string
    {
        return chr((ord($this->version) - ord('a') + $inc) % 26 + ord('a'));
    }
}
