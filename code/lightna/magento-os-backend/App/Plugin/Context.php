<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Plugin;

use Closure;
use Exception;
use Lightna\Engine\App\Opcache\Compiled;
use Lightna\Engine\Data\DataA;

class Context extends DataA
{
    protected Compiled $compiled;

    protected string $runType;
    protected string $runCode;
    protected array $runCodes;

    /** @noinspection PhpUnused */
    protected function defineRunType(): void
    {
        $this->runType = $_SERVER['MAGE_RUN_TYPE'] ?? '';
        if (!in_array($this->runType, ['website', 'store'])) {
            throw new Exception('Unsupported MAGE_RUN_TYPE value');
        }
    }

    /** @noinspection PhpUnused */
    protected function defineRunCode(): void
    {
        $this->runCode = $_SERVER['MAGE_RUN_CODE'] ?? '';
    }

    /** @noinspection PhpUnused */
    protected function defineRunCodes(): void
    {
        $this->runCodes = $this->compiled->load('magento/runCodes');
    }

    /** @noinspection PhpUnused */
    public function resolveExtended(Closure $proceed): Closure
    {
        $resolveScope = $this->resolveScope(...);

        return function () use ($resolveScope, $proceed) {
            if (LIGHTNA_AREA !== 'frontend') {
                $proceed();
            } else {
                $this->scope = $resolveScope();
            }
        };
    }

    protected function resolveScope(): int
    {
        if ($this->runCode === '') {
            return 1;
        }
        if (!$runCode = $this->runCodes[$this->runType][$this->runCode] ?? null) {
            throw new Exception('Undefined run code "' . $this->runCode . '"');
        }

        return $runCode;
    }
}
